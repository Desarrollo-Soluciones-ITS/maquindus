<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\Person;
use App\Models\Document;
use App\Models\EquipmentStandard;
use App\Models\EquipmentTechnicalSpecification;
use App\Models\EquipmentCatalog;
use App\Models\EquipmentBlueprint;
use App\Models\EquipmentDataSheet;
use App\Models\EquipmentReport;
use App\Models\EquipmentFieldQuery;
use App\Models\EquipmentSparePart;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Str;

class MigrateFilesCommand extends Command
{
    protected $signature = 'migrate:files {source : Ruta completa del directorio origen}
                                          {--disk=local : Disco de almacenamiento destino (local, public, etc.)}
                                          {--dry-run : Simula la migración sin copiar archivos ni guardar en BD}';
    protected $description = 'Copia archivos desde una estructura de directorio a la nueva organización de la base de datos.';

    // Mapeo de subcarpetas dentro de Equipos hacia modelos específicos (metadata)
    protected const EQUIPMENT_METADATA_MAP = [
        'Plano' => EquipmentBlueprint::class,
        'Planos' => EquipmentBlueprint::class,
        'Hoja De Datos' => EquipmentDataSheet::class,
        'Hoja de Datos' => EquipmentDataSheet::class,
        'Catálogo' => EquipmentCatalog::class,
        'Catalogo' => EquipmentCatalog::class,
        'Especificaciones Tecnicas' => EquipmentTechnicalSpecification::class,
        'Especificación Técnica' => EquipmentTechnicalSpecification::class,
        'Norma' => EquipmentStandard::class,
        'Consulta De Campo' => EquipmentFieldQuery::class,
        'Consulta de Campo' => EquipmentFieldQuery::class,
        'Reportes' => EquipmentReport::class,
        'Reporte' => EquipmentReport::class,
        'Repuestos' => EquipmentSparePart::class,
    ];

    public function handle()
    {
        $source = $this->argument('source');
        $disk = $this->option('disk');
        $storage = Storage::disk($disk);
        $dryRun = $this->option('dry-run');

        if (!is_dir($source)) {
            $this->error("El directorio origen no existe: $source");
            return 1;
        }

        $finder = new Finder();
        $finder->files()->in($source)->ignoreDotFiles(true);

        $total = iterator_count($finder);
        $this->info("Se encontraron $total archivos para procesar.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        DB::beginTransaction();

        try {
            foreach ($finder as $file) {
                $relative = $file->getRelativePathname();
                $segments = explode(DIRECTORY_SEPARATOR, $relative);

                // Determinar tipo de entidad raíz
                $rootFolder = $segments[0] ?? null;
                if (!in_array($rootFolder, ['Equipos', 'Proyectos', 'Proveedores', 'Contactos'])) {
                    $this->warn("Ignorando archivo sin entidad padre válida: $relative");
                    $bar->advance();
                    continue;
                }

                // Obtener el modelo padre base (equipment, project, supplier o person)
                $entityType = null;
                $entityName = $segments[1] ?? ($rootFolder === 'Equipos' ? 'Equipos_Varios' :
                    ($rootFolder === 'Proyectos' ? 'Proyectos_Varios' :
                        ($rootFolder === 'Proveedores' ? 'Proveedores_Varios' : 'Contactos_Varios')));

                $baseModel = null;
                if ($rootFolder === 'Equipos') {
                    $entityType = 'equipment';
                    $baseModel = $this->getOrCreateEntity($entityType, $entityName);
                } elseif ($rootFolder === 'Proyectos') {
                    $entityType = 'project';
                    $baseModel = $this->getOrCreateEntity($entityType, $entityName);
                } elseif ($rootFolder === 'Proveedores') {
                    $entityType = 'supplier';
                    $baseModel = $this->getOrCreateEntity($entityType, $entityName);
                } elseif ($rootFolder === 'Contactos') {
                    $entityType = 'person';
                    $baseModel = $this->getOrCreateEntity($entityType, $entityName);
                }

                // Determinar el modelo al que se asociará el documento (puede ser el base o un metadata)
                $targetModel = $baseModel;
                $metadataType = null;

                if ($rootFolder === 'Equipos' && isset($segments[2])) {
                    $possibleMetadata = $segments[2];
                    // Normalizar eliminando acentos y mayúsculas para comparación
                    $normalized = $this->normalizeFolderName($possibleMetadata);
                    foreach (self::EQUIPMENT_METADATA_MAP as $key => $class) {
                        if ($this->normalizeFolderName($key) === $normalized) {
                            $metadataType = $class;
                            break;
                        }
                    }

                    if ($metadataType) {
                        // Crear o encontrar el modelo de metadatos asociado al equipo
                        // Usamos un nombre por defecto basado en la subcarpeta (puede personalizarse)
                        $metadataName = $possibleMetadata;
                        $metadata = $metadataType::firstOrCreate([
                            'equipment_id' => $baseModel->id,
                            $this->getNameFieldForMetadata($metadataType) => $metadataName
                        ]);
                        $targetModel = $metadata;
                    }
                }

                // Procesar nombre de archivo, versión y nombre del documento
                $filename = $file->getFilename();
                $extension = $file->getExtension();
                $baseName = pathinfo($filename, PATHINFO_FILENAME);
                $version = 1;

                if (preg_match('/- V(\d+)$/', $baseName, $matches)) {
                    $version = (int) $matches[1];
                    $baseName = preg_replace('/- V\d+$/', '', $baseName);
                }

                $docName = trim($baseName);
                if (empty($docName)) {
                    $docName = 'Sin título';
                }

                // Crear o recuperar el documento asociado al modelo padre (targetModel)
                $document = $this->getOrCreateDocument($docName, $targetModel);

                // Generar nombre limpio para la ruta del archivo (slug)
                $cleanName = Str::slug($docName, '_');
                $destPath = $this->buildDestPath($entityType, $targetModel->id, $document->id, $version, $cleanName, $extension);

                if ($dryRun) {
                    $this->line("Simulación: Copiar '{$file->getRealPath()}' a '{$destPath}' (doc: {$docName}, v{$version})");
                    $bar->advance();
                    continue;
                }

                // Asegurar ruta única en el destino
                $destPath = $this->makeUniquePath($storage, $destPath);

                // Copiar archivo
                $storage->put($destPath, file_get_contents($file->getRealPath()));

                // Calcular tamaño en MB
                $sizeBytes = $file->getSize();
                $sizeMB = round($sizeBytes / 1000000, 2);

                // Registrar el archivo
                $document->files()->create([
                    'path' => $destPath,
                    'mime' => File::mimeType($file->getRealPath()) ?: 'application/octet-stream',
                    'version' => $version,
                    'file_size' => $sizeMB,
                    'user_id' => null,
                ]);

                $bar->advance();
            }

            if (!$dryRun) {
                DB::commit();
                $bar->finish();
                $this->newLine();
                $this->info('¡Migración completada con éxito!');
            } else {
                DB::rollBack();
                $bar->finish();
                $this->newLine();
                $this->info('Simulación completada. No se realizaron cambios.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error durante la transacción: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Obtiene o crea una entidad base (Equipment, Project, Supplier, Person).
     */
    private function getOrCreateEntity(string $type, string $name)
    {
        $name = trim($name);
        if (empty($name)) {
            throw new \Exception("Nombre de entidad vacío para tipo $type");
        }

        switch ($type) {
            case 'equipment':
                return Equipment::firstOrCreate(['name' => $name]);
            case 'supplier':
                return Supplier::firstOrCreate(['name' => $name]);
            case 'person':
                return Person::firstOrCreate(['name' => $name]);
            default:
                throw new \Exception("Tipo de entidad no soportado: $type");
        }
    }

    /**
     * Obtiene el campo "name" que debe usarse para cada modelo de metadata.
     */
    private function getNameFieldForMetadata(string $modelClass): string
    {
        return match ($modelClass) {
            EquipmentBlueprint::class => 'name',
            EquipmentDataSheet::class => 'sheet_number',
            EquipmentCatalog::class => 'name',
            EquipmentTechnicalSpecification::class => 'revision_name',
            EquipmentStandard::class => 'name',
            EquipmentFieldQuery::class => 'document_name',
            EquipmentReport::class => 'document_name',
            EquipmentSparePart::class => 'part_number',
            default => 'name',
        };
    }

    /**
     * Normaliza el nombre de carpeta para comparación (minúsculas, sin acentos, sin espacios).
     */
    private function normalizeFolderName(string $name): string
    {
        $normalized = mb_strtolower($name);
        $normalized = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $normalized
        );
        $normalized = preg_replace('/[^a-z0-9]/', '', $normalized);
        return $normalized;
    }

    /**
     * Obtiene o crea un documento asociado a un modelo padre.
     * Maneja duplicados por nombre con sufijo "(Duplicado)".
     */
    private function getOrCreateDocument(string $name, $parentModel): Document
    {
        $originalName = $name;
        $counter = 0;
        $uniqueName = $originalName;

        // Buscar documento existente con el mismo nombre y asociado al mismo padre
        $document = Document::where('name', $uniqueName)
            ->where('documentable_type', get_class($parentModel))
            ->where('documentable_id', $parentModel->id)
            ->first();

        if ($document) {
            return $document;
        }

        // Si no existe, crear con posible sufijo duplicado
        while (
            Document::where('name', $uniqueName)
                ->where('documentable_type', get_class($parentModel))
                ->where('documentable_id', $parentModel->id)
                ->exists()
        ) {
            $counter++;
            $uniqueName = $originalName . ' (Duplicado' . ($counter > 1 ? " $counter" : '') . ')';
        }

        return Document::create([
            'name' => $uniqueName,
            'category' => null, // Sin categoría según requerimiento
            'documentable_type' => get_class($parentModel),
            'documentable_id' => $parentModel->id,
        ]);
    }

    /**
     * Construye la ruta de destino relativa al disco.
     */
    private function buildDestPath(string $entityType, string $parentId, string $documentId, int $version, string $cleanName, string $extension): string
    {
        $folder = match ($entityType) {
            'equipment' => 'equipos',
            'project' => 'proyectos',
            'supplier' => 'proveedores',
            'person' => 'contactos',
            default => 'otros',
        };

        return sprintf(
            '%s/%s/%s/v%d_%s.%s',
            $folder,
            $parentId,
            $documentId,
            $version,
            $cleanName,
            $extension
        );
    }

    /**
     * Asegura que la ruta de destino no exista, añadiendo sufijo numérico.
     */
    private function makeUniquePath(Filesystem $storage, string $path): string
    {
        $directory = dirname($path);
        $filename = basename($path);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $counter = 1;

        while ($storage->exists($path)) {
            $suffix = '_' . $counter;
            $path = $directory . '/' . $name . $suffix . ($ext !== '' ? '.' . $ext : '');
            $counter++;
        }

        return $path;
    }
}