<?php

namespace App\Console\Commands;

use App\Enums\Category;
use App\Models\Equipment;
use App\Models\Document;
use App\Models\File;
use App\Models\EquipmentDataSheet;
use App\Models\EquipmentBlueprint;
use App\Models\EquipmentCatalog;
use App\Models\EquipmentTechnicalSpecification;
use App\Models\EquipmentStandard;
use App\Models\EquipmentManual;
use App\Models\EquipmentReport;
use App\Models\EquipmentFieldQuery;
use App\Models\EquipmentSparePart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;

class SyncEquipmentFolder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'equipment:sync-folder 
                            {folder_name? : El nombre exacto de la carpeta del equipo (opcional. Si se omite, procesa todas las carpetas)}
                            {--rebuild : Elimina los registros anteriores de los equipos procesados en base de datos antes de volver a escanear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza y registra en base de datos los archivos de uno o todos los equipos manteniendo su estructura física original intacta.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $folderName = trim((string) $this->argument('folder_name'));
        $rebuild = $this->option('rebuild');

        $equiposPath = storage_path('app/private/Equipos');
        if (!is_dir($equiposPath)) {
            $this->error("El directorio raíz de Equipos no existe en la ruta: {$equiposPath}");
            return 1;
        }

        $foldersToProcess = [];

        if ($folderName !== '') {
            $path = "{$equiposPath}/{$folderName}";
            if (!is_dir($path)) {
                $this->error("El directorio físico del equipo no existe en la ruta: {$path}");
                return 1;
            }
            $foldersToProcess[] = $folderName;
        } else {
            $this->info("Buscando carpetas de equipos en: Equipos/");
            $dirs = Finder::create()->directories()->in($equiposPath)->depth(0);
            foreach ($dirs as $dir) {
                $foldersToProcess[] = $dir->getFilename();
            }

            if (empty($foldersToProcess)) {
                $this->warn("No se encontraron carpetas de equipos en: {$equiposPath}");
                return 0;
            }

            $this->info("Se encontraron " . count($foldersToProcess) . " carpetas de equipos para procesar: " . implode(', ', $foldersToProcess));
        }

        $totalProcessed = 0;
        $totalErrors = 0;

        foreach ($foldersToProcess as $index => $targetFolder) {
            $this->newLine();
            $this->info("[" . ($index + 1) . "/" . count($foldersToProcess) . "] Procesando equipo: {$targetFolder}");

            // 1. Buscar o crear el equipo
            $equipment = Equipment::withTrashed()->where('name', $targetFolder)->first();

            if ($equipment && $rebuild) {
                $this->warn("Opción --rebuild activa. Eliminando registros anteriores de la base de datos para '{$targetFolder}'...");
                DB::transaction(function () use ($equipment) {
                    $relations = [
                        'dataSheets',
                        'blueprints',
                        'catalogs',
                        'technicalSpecifications',
                        'standards',
                        'manuals',
                        'reports',
                        'fieldQueries',
                        'equipmentSpareParts',
                    ];

                    foreach ($relations as $rel) {
                        $items = $equipment->$rel()->withTrashed()->get();
                        foreach ($items as $item) {
                            $docs = Document::withTrashed()
                                ->where('documentable_type', get_class($item))
                                ->where('documentable_id', $item->id)
                                ->get();
                            foreach ($docs as $doc) {
                                $doc->files()->withTrashed()->forceDelete();
                                $doc->forceDelete();
                            }
                            $item->forceDelete();
                        }
                    }

                    // Eliminar documentos genéricos directamente asociados al equipo
                    $genericDocs = Document::withTrashed()
                        ->where('documentable_type', Equipment::class)
                        ->where('documentable_id', $equipment->id)
                        ->get();
                    foreach ($genericDocs as $doc) {
                        $doc->files()->withTrashed()->forceDelete();
                        $doc->forceDelete();
                    }

                    $equipment->forceDelete();
                });
                $equipment = null;
                $this->info("Limpieza completada.");
            }

            if (!$equipment) {
                $equipment = Equipment::create(['name' => $targetFolder]);
                $this->info("Creado registro de equipo: {$targetFolder} (ID: {$equipment->id})");
            } else {
                if ($equipment->trashed()) {
                    $equipment->restore();
                    $this->info("Restaurado registro de equipo existente: {$targetFolder}");
                } else {
                    $this->info("Usando equipo existente: {$targetFolder} (ID: {$equipment->id})");
                }
            }

            // 2. Escanear archivos recursivamente para este equipo
            $path = "{$equiposPath}/{$targetFolder}";
            $finder = new Finder();
            $finder->files()->in($path)->ignoreDotFiles(true);

            $fileCount = iterator_count($finder);
            $this->info("Se encontraron {$fileCount} archivos en '{$targetFolder}'.");

            if ($fileCount === 0) {
                continue;
            }

            $bar = $this->output->createProgressBar($fileCount);
            $bar->start();

            foreach ($finder as $file) {
                $fileRelativePathname = str_replace('\\', '/', $file->getRelativePathname());
                $fullRelativePath = "Equipos/{$targetFolder}/" . $fileRelativePathname;

                try {
                    DB::transaction(function () use ($file, $fileRelativePathname, $fullRelativePath, $equipment) {
                        // Ignorar archivos no válidos o del sistema
                        $filename = $file->getFilename();
                        if ($this->shouldSkipFile($filename)) {
                            return;
                        }

                        // Determinar versión y nombre base del archivo
                        $version = 1;
                        $baseName = pathinfo($filename, PATHINFO_FILENAME);
                        if (preg_match('/- V(\d+)$/i', $baseName, $matches)) {
                            $version = (int) $matches[1];
                            $baseName = preg_replace('/- V\d+$/i', '', $baseName);
                        }
                        $baseName = trim($baseName);

                        // Resolver subcarpeta/identificador de entidad (ej: "DS-001")
                        $entityIdentifier = $this->resolveEntityIdentifier($fileRelativePathname, $baseName);

                        // Mapear la categoría y el modelo hijo de base de datos
                        $mapping = $this->resolveCategoryAndClass($fileRelativePathname);
                        $targetClass = $mapping['class'];
                        $identifierField = $mapping['field'];
                        $category = $mapping['category'];
                        $defaults = $mapping['defaults'];
                        $extraFields = $mapping['extra'];

                        // Manejar campos adicionales específicos
                        if ($targetClass === EquipmentBlueprint::class) {
                            $extraFields['name'] = $entityIdentifier;
                        }

                        $documentableType = $targetClass;
                        $documentableId = null;

                        if ($targetClass === Equipment::class) {
                            // Documento genérico directamente asociado al equipo
                            $documentableId = $equipment->id;
                        } else {
                            // Crear o buscar la entidad hija
                            $childModel = $targetClass::firstOrCreate(
                                ['equipment_id' => $equipment->id, $identifierField => $entityIdentifier],
                                array_merge($defaults, $extraFields)
                            );
                            $documentableId = $childModel->id;
                        }

                        // Crear o buscar el registro del documento
                        $document = Document::firstOrCreate([
                            'documentable_type' => $documentableType,
                            'documentable_id' => $documentableId,
                            'name' => $baseName,
                        ], [
                            'category' => $category,
                        ]);

                        // Determinar mime y peso del archivo
                        $mime = check_solidworks(
                            mime: Storage::disk('local')->mimeType($fullRelativePath) ?: 'application/octet-stream',
                            path: $file->getRealPath()
                        );
                        $fileSize = $file->getSize();

                        // Registrar archivo físico en base de datos
                        File::firstOrCreate([
                            'path' => $fullRelativePath,
                        ], [
                            'document_id' => $document->id,
                            'version' => $version,
                            'mime' => mime_type($mime),
                            'file_size' => $fileSize,
                        ]);
                    });

                    $totalProcessed++;
                } catch (\Throwable $th) {
                    $totalErrors++;
                    $this->newLine();
                    $this->error("Error procesando '{$fileRelativePathname}': " . $th->getMessage());
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        // 3. Reconstruir el índice de búsqueda al final
        $this->newLine();
        $this->info("Reconstruyendo índice de búsqueda para todos los equipos...");
        $this->call('search:index', ['--rebuild' => true]);

        $this->info("Proceso completado.");
        $this->info("Archivos registrados: {$totalProcessed} | Errores: {$totalErrors}");

        return 0;
    }

    private function shouldSkipFile(string $filename): bool
    {
        $skipped = ['thumbs.db', 'desktop.ini', '.ds_store', 'duplicados.txt'];
        return in_array(mb_strtolower($filename), $skipped, true);
    }

    private function resolveEntityIdentifier(string $relativePath, string $baseName): string
    {
        $dirPath = dirname($relativePath);
        if ($dirPath === '.' || $dirPath === '') {
            return $baseName;
        }

        $folderName = basename($dirPath);

        // Si es una carpeta de sección raíz, usamos el nombre del archivo
        $normalizedFolder = $this->normalizeName($folderName);
        $sectionNames = [
            'reportes', 'reporte', 'manuales', 'manual', 'planos', 'plano', 
            'hoja de datos', 'hojas de datos', 'catalogos', 'catalogo', 
            'normas', 'norma', 'revisiones', 'revision', 'consultas de campo', 
            'consulta de campo', 'repuestos', 'repuesto', 'general', 
            'especificaciones tecnicas'
        ];

        if (in_array($normalizedFolder, $sectionNames)) {
            return $baseName;
        }

        return $folderName;
    }

    private function normalizeName(string $name): string
    {
        $normalized = mb_strtolower($name);
        return str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $normalized
        );
    }

    private function resolveCategoryAndClass(string $relativePath): array
    {
        $normalized = $this->normalizeName($relativePath);

        if (str_contains($normalized, '/hoja de datos/') || str_contains($normalized, '/hojas de datos/')) {
            return [
                'class' => EquipmentDataSheet::class,
                'field' => 'sheet_number',
                'category' => Category::Specs,
                'defaults' => ['revision' => '1', 'document_date' => now()],
                'extra' => []
            ];
        }

        if (str_contains($normalized, '/planos/') || str_contains($normalized, '/plano/')) {
            return [
                'class' => EquipmentBlueprint::class,
                'field' => 'blueprint_number',
                'category' => Category::Blueprint,
                'defaults' => ['revision' => '1', 'document_date' => now()],
                'extra' => []
            ];
        }

        if (str_contains($normalized, '/catalogos/') || str_contains($normalized, '/catalogo/')) {
            return [
                'class' => EquipmentCatalog::class,
                'field' => 'name',
                'category' => Category::Photo,
                'defaults' => ['document_type' => 'General'],
                'extra' => []
            ];
        }

        if (str_contains($normalized, '/revisiones/') || str_contains($normalized, '/revision/')) {
            return [
                'class' => EquipmentTechnicalSpecification::class,
                'field' => 'revision_name',
                'category' => Category::Specs,
                'defaults' => ['revision' => '1', 'document_date' => now()],
                'extra' => []
            ];
        }

        if (str_contains($normalized, '/normas/') || str_contains($normalized, '/norma/')) {
            return [
                'class' => EquipmentStandard::class,
                'field' => 'name',
                'category' => Category::Specs,
                'defaults' => ['revision' => '1'],
                'extra' => []
            ];
        }

        if (str_contains($normalized, '/manuales/') || str_contains($normalized, '/manual/')) {
            return [
                'class' => EquipmentManual::class,
                'field' => 'name',
                'category' => Category::Manual,
                'defaults' => [],
                'extra' => []
            ];
        }

        if (str_contains($normalized, '/reportes/') || str_contains($normalized, '/reporte/')) {
            return [
                'class' => EquipmentReport::class,
                'field' => 'document_name',
                'category' => Category::Report,
                'defaults' => ['document_type' => 'General', 'document_date' => now()],
                'extra' => []
            ];
        }

        if (str_contains($normalized, '/consultas de campo/') || str_contains($normalized, '/consulta de campo/')) {
            return [
                'class' => EquipmentFieldQuery::class,
                'field' => 'document_name',
                'category' => Category::Report,
                'defaults' => ['document_type' => 'General', 'document_date' => now()],
                'extra' => []
            ];
        }

        if (str_contains($normalized, '/repuestos/') || str_contains($normalized, '/repuesto/')) {
            return [
                'class' => EquipmentSparePart::class,
                'field' => 'part_number',
                'category' => null,
                'defaults' => [],
                'extra' => []
            ];
        }

        return [
            'class' => Equipment::class,
            'field' => 'name',
            'category' => null,
            'defaults' => [],
            'extra' => []
        ];
    }
}
