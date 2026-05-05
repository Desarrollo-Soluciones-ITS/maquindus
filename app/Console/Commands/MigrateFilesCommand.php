<?php

namespace App\Console\Commands;

use App\Enums\Category;
use App\Models\Equipment;
use App\Models\Supplier;
use App\Models\Person;
use App\Models\Document;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
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

    protected const ROOT_FOLDERS = ['Equipos', 'Proyectos', 'Proveedores', 'Contactos'];

    protected const SKIPPED_FILENAMES = [
        'thumbs.db',
        'desktop.ini',
        '.ds_store',
        'duplicados.txt',
    ];

    public function handle()
    {
        $source = $this->argument('source');
        $disk = $this->option('disk');
        $storage = Storage::disk($disk);
        $dryRun = $this->option('dry-run');
        $skippedWithError = [];

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
                $relative = str_replace('\\', '/', $file->getRelativePathname());

                try {
                    if ($this->shouldSkipFile($file->getFilename())) {
                        $bar->advance();
                        continue;
                    }

                    $directorySegments = $this->splitPathSegments($file->getRelativePath());

                    // Determinar tipo de entidad raíz
                    $rootFolder = $directorySegments[0] ?? null;
                    if (!in_array($rootFolder, self::ROOT_FOLDERS, true)) {
                        $bar->advance();
                        continue;
                    }

                    // Obtener el modelo padre base (equipment, project, supplier o person)
                    $entityType = null;
                    $entityName = $directorySegments[1] ?? ($rootFolder === 'Equipos' ? 'Equipos_Varios' :
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

                    $contextSegments = array_slice($directorySegments, 2);
                    [$category, $contextSegments] = $this->resolveCategoryAndContext($contextSegments);

                    // Procesar nombre de archivo, versión y nombre del documento
                    $filename = $file->getFilename();
                    $extension = $this->sanitizeExtension($file->getExtension());
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

                    $docName = $this->buildDocumentName($docName, $contextSegments);

                    // Crear o recuperar el documento asociado al modelo padre base.
                    $document = $this->getOrCreateDocument($docName, $baseModel, $category);

                    $destPath = $this->buildDestPath($baseModel, $entityType, $entityName, $category, $docName, $version, $extension);

                    if ($destPath === '') {
                        throw new \RuntimeException('La ruta destino quedó vacía después de normalizarse.');
                    }

                    if ($dryRun) {
                        $this->line("Simulación: Copiar '{$file->getRealPath()}' a '{$destPath}' (doc: {$docName}, v{$version})");
                        $bar->advance();
                        continue;
                    }

                    // Asegurar ruta única en el destino
                    $destPath = $this->makeUniquePath($storage, $destPath);

                    // Copiar archivo
                    $storage->put($destPath, file_get_contents($file->getRealPath()));

                    $sizeBytes = $file->getSize();
                    $mime = check_solidworks(
                        mime: File::mimeType($file->getRealPath()) ?: 'application/octet-stream',
                        path: $file->getRealPath(),
                    );

                    // Registrar el archivo
                    $document->files()->create([
                        'path' => $destPath,
                        'mime' => mime_type($mime),
                        'version' => $version,
                        'file_size' => $sizeBytes,
                    ]);
                } catch (\Throwable $fileException) {
                    $skippedWithError[] = [
                        'file' => $relative,
                        'error' => $fileException->getMessage(),
                    ];
                }

                $bar->advance();
            }

            if (!$dryRun) {
                DB::commit();
                $bar->finish();
                $this->newLine();
                $this->info('¡Migración completada con éxito!');
                $this->reportSkippedFiles($skippedWithError);
            } else {
                DB::rollBack();
                $bar->finish();
                $this->newLine();
                $this->info('Simulación completada. No se realizaron cambios.');
                $this->reportSkippedFiles($skippedWithError);
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
            case 'project':
                return null;
            case 'supplier':
                return Supplier::firstOrCreate(['name' => $name]);
            case 'person':
                return Person::firstOrCreate(['name' => $name]);
            default:
                throw new \Exception("Tipo de entidad no soportado: $type");
        }
    }

    /**
     * Decide la categoria actual del sistema a partir de carpetas legadas.
     */
    private function resolveCategoryAndContext(array $segments): array
    {
        foreach ($segments as $index => $segment) {
            $category = $this->mapFolderToCategory($segment);

            if ($category !== null) {
                return [$category, array_slice($segments, $index + 1)];
            }
        }

        return [null, $segments];
    }

    private function mapFolderToCategory(string $folderName): ?Category
    {
        return match ($this->normalizeFolderName($folderName)) {
            'plano', 'planos' => Category::Blueprint,
            'manual', 'manuales' => Category::Manual,
            'reporte', 'reportes', 'inspecciones' => Category::Report,
            'especificaciontecnica', 'especificacionestecnicas', 'informaciontecnica', 'hojadedatos', 'consultadecampo', 'consultasencampo' => Category::Specs,
            'oferta', 'ofertas' => Category::Offer,
            'foto', 'fotos' => Category::Photo,
            default => null,
        };
    }

    private function buildDocumentName(string $baseName, array $contextSegments): string
    {
        $segments = collect($contextSegments)
            ->map(fn(string $segment) => $this->sanitizePathSegment($segment))
            ->filter();

        $segments->push($this->sanitizePathSegment($baseName));

        return $segments
            ->unique()
            ->implode(' - ') ?: 'Sin título';
    }

    private function splitPathSegments(string $path): array
    {
        if ($path === '' || $path === '.') {
            return [];
        }

        return array_values(array_filter(explode('/', str_replace('\\', '/', $path)), fn(string $segment) => $segment !== ''));
    }

    private function shouldSkipFile(string $filename): bool
    {
        return in_array(mb_strtolower($filename), self::SKIPPED_FILENAMES, true);
    }

    private function sanitizeExtension(string $extension): string
    {
        return trim((string) preg_replace('/[^A-Za-z0-9]+/', '', $extension));
    }

    private function sanitizePathSegment(string $value): string
    {
        $value = str_replace(['\\', '/'], ' ', $value);
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return trim($value, " .\t\n\r\0\x0B");
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
    private function getOrCreateDocument(string $name, ?Model $parentModel, ?Category $category): Document
    {
        $originalName = $name;
        $counter = 0;
        $uniqueName = $originalName;

        $documentQuery = Document::where('name', $uniqueName)
            ->where('category', $category?->value);

        if ($parentModel) {
            $documentQuery
                ->where('documentable_type', get_class($parentModel))
                ->where('documentable_id', $parentModel->id);
        } else {
            $documentQuery
                ->whereNull('documentable_type')
                ->whereNull('documentable_id');
        }

        $document = $documentQuery->first();

        if ($document) {
            return $document;
        }

        // Si no existe, crear con posible sufijo duplicado
        while (
            $this->documentExists($uniqueName, $parentModel, $category)
        ) {
            $counter++;
            $uniqueName = $originalName . ' (Duplicado' . ($counter > 1 ? " $counter" : '') . ')';
        }

        return Document::create(array_filter([
            'name' => $uniqueName,
            'category' => $category,
            'documentable_type' => $parentModel ? get_class($parentModel) : null,
            'documentable_id' => $parentModel?->id,
        ], static fn($value) => $value !== null));
    }

    private function documentExists(string $name, ?Model $parentModel, ?Category $category): bool
    {
        $query = Document::where('name', $name)
            ->where('category', $category?->value);

        if ($parentModel) {
            $query
                ->where('documentable_type', get_class($parentModel))
                ->where('documentable_id', $parentModel->id);
        } else {
            $query
                ->whereNull('documentable_type')
                ->whereNull('documentable_id');
        }

        return $query->exists();
    }

    /**
     * Construye la ruta de destino relativa al disco.
     */
    private function buildDestPath(?Model $parentModel, string $entityType, string $entityName, ?Category $category, string $documentName, int $version, string $extension): string
    {
        $segments = [];

        if ($parentModel) {
            $segments[] = $this->sanitizePathSegment(model_to_spanish($parentModel::class, plural: true) ?? Str::headline($entityType));
            $segments[] = $this->sanitizePathSegment($this->resolveEntityFolderName($parentModel));
        } else {
            $segments[] = $this->sanitizePathSegment(Str::headline($entityType === 'project' ? 'proyectos' : $entityType));
            $segments[] = $this->sanitizePathSegment($entityName);
        }

        if ($category) {
            $segments[] = $this->sanitizePathSegment($category->value);
        }

        $filename = $this->sanitizePathSegment($documentName) . " - V{$version}";
        if ($extension !== '') {
            $filename .= '.' . $extension;
        }

        $segments[] = $this->sanitizePathSegment($filename);

        return implode('/', array_values(array_filter($segments, static fn(string $segment) => $segment !== '')));
    }

    private function resolveEntityFolderName(Model $model): string
    {
        if ($model instanceof Person) {
            $email = trim((string) ($model->email ?? ''));

            return $email !== ''
                ? $model->name . ' - ' . $email
                : $model->name;
        }

        return $model->name;
    }

    /**
     * Asegura que la ruta de destino no exista, añadiendo sufijo numérico.
     */
    private function makeUniquePath(Filesystem $storage, string $path): string
    {
        $path = trim($path, '/');

        if ($path === '') {
            throw new \RuntimeException('La ruta destino está vacía y no puede escribirse en el disco.');
        }

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

    private function reportSkippedFiles(array $skippedWithError): void
    {
        if ($skippedWithError === []) {
            return;
        }

        $this->newLine();
        $this->warn('Se omitieron archivos con error durante la migración: ' . count($skippedWithError));

        foreach (array_slice($skippedWithError, 0, 20) as $item) {
            $this->line('- ' . $item['file'] . ' -> ' . $item['error']);
        }

        if (count($skippedWithError) > 20) {
            $this->line('... y ' . (count($skippedWithError) - 20) . ' archivos más.');
        }
    }
}