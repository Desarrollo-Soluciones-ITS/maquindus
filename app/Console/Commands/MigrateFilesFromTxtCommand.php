<?php

namespace App\Console\Commands;

use App\Enums\Category;
use App\Models\Equipment;
use App\Models\Supplier;
use App\Models\Person;
use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateFilesFromTxtCommand extends Command
{
    protected $signature = 'migrate:files-txt
                            {txtfile : Ruta del archivo directory.txt}
                            {--source-base= : Ruta base de origen (por defecto se extrae del txt)}
                            {--disk=local : Disco de almacenamiento destino}
                            {--dry-run : Simula la migración sin copiar archivos ni guardar en BD}
                            {--output-map= : Archivo de salida con el mapeo origen->destino (opcional)}';
    protected $description = 'Lee directory.txt y migra los archivos listados a la nueva organización.';

    protected const ROOT_FOLDERS = ['Equipos', 'Proyectos', 'Proveedores', 'Contactos'];

    protected const SKIPPED_FILENAMES = [
        'thumbs.db',
        'desktop.ini',
        '.ds_store',
        'duplicados.txt',
    ];

    /**
     * @var string Ruta base de origen (ej: \\SERVER\PROYECTO BASE DE DATOS)
     */
    protected string $sourceBase = '';

    /**
     * @var array<int, array{path: string, files: array<int, array{name: string, size: int}>}>
     */
    protected array $parsedStructure = [];

    /**
     * @var array<int, array{source: string, dest: string, entityType: string, entityName: string, category: ?Category, docName: string, version: int, ext: string, size: int}>
     */
    protected array $fileMap = [];

    protected array $skippedWithError = [];

    public function handle()
    {
        $txtFile = $this->argument('txtfile');
        $disk = $this->option('disk');
        $dryRun = $this->option('dry-run');
        $outputMap = $this->option('output-map');
        $storage = Storage::disk($disk);

        if (!file_exists($txtFile)) {
            $this->error("El archivo directory.txt no existe: $txtFile");
            return 1;
        }

        // 1. Parsear el archivo directory.txt
        $this->info('Paso 1: Parseando estructura del archivo...');
        $lines = file($txtFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            $this->error("No se pudo leer el archivo: $txtFile");
            return 1;
        }

        $this->parseDirectoryTxt($lines);

        $this->info("  - Ruta base de origen: {$this->sourceBase}");
        $this->info('  - Archivos encontrados: ' . count($this->fileMap));

        // 2. Construir el mapa de archivos con rutas de origen y destino
        $this->info('Paso 2: Construyendo mapa de archivos origen->destino...');
        $this->buildFileMap();

        // 3. Opcional: guardar el mapeo en un archivo
        if ($outputMap) {
            $this->saveMapToFile($outputMap);
            $this->info("  - Mapeo guardado en: $outputMap");
        }

        // 4. Ejecutar la migración (copia de archivos + registro en BD)
        $this->info('Paso 3: Ejecutando migración...');

        $total = count($this->fileMap);
        $this->info("  - Total archivos a procesar: $total");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        DB::beginTransaction();

        try {
            foreach ($this->fileMap as $item) {
                try {
                    if ($dryRun) {
                        $this->line("  [SIM] {$item['source']} -> {$item['dest']}");
                        $bar->advance();
                        continue;
                    }

                    // Verificar si existe el archivo de origen
                    if (!file_exists($item['source'])) {
                        $this->skippedWithError[] = [
                            'file' => $item['source'],
                            'error' => 'El archivo de origen no existe en la ruta especificada',
                        ];
                        $bar->advance();
                        continue;
                    }

                    // Obtener o crear la entidad base
                    $baseModel = $this->getOrCreateEntity($item['entityType'], $item['entityName']);

                    // Obtener o crear el documento
                    $document = $this->getOrCreateDocument($item['docName'], $baseModel, $item['category']);

                    // Construir la ruta de destino
                    $destPath = $this->buildDestPath(
                        $baseModel,
                        $item['entityType'],
                        $item['entityName'],
                        $item['category'],
                        $item['docName'],
                        $item['version'],
                        $item['ext']
                    );

                    if ($destPath === '') {
                        throw new \RuntimeException('La ruta destino quedó vacía después de normalizarse.');
                    }

                    // Asegurar ruta única en el destino
                    $destPath = $this->makeUniquePath($storage, $destPath);

                    // Determinar el MIME type
                    $sourcePath = $item['source'];
                    $mime = File::mimeType($sourcePath) ?: 'application/octet-stream';

                    // Copiar archivo al storage
                    $storage->put($destPath, file_get_contents($sourcePath));

                    // Registrar el archivo en la base de datos
                    $document->files()->create([
                        'path' => $destPath,
                        'mime' => mime_type($mime),
                        'version' => $item['version'],
                        'file_size' => $item['size'],
                    ]);

                } catch (\Throwable $e) {
                    $this->skippedWithError[] = [
                        'file' => $item['source'] ?? 'desconocido',
                        'error' => $e->getMessage(),
                    ];
                }

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

            $this->reportSkippedFiles();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->newLine();
            $this->error('Error durante la transacción: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Parsea el archivo directory.txt y extrae la estructura jerárquica.
     *
     * Formato esperado:
     *   [CARPETA] NombreCarpeta
     *     - [ARCHIVO] nombre_archivo.ext (tamaño)
     *   - [ARCHIVO] nombre_archivo.ext (tamaño)
     */
    protected function parseDirectoryTxt(array $lines): void
    {
        $stack = []; // Pila de [nombre, nivel_indentacion]
        $baseFound = false;

        foreach ($lines as $line) {
            // Detectar la línea de encabezado con la ruta base
            if (str_starts_with($line, 'Carpeta, Ruta escaneada:')) {
                $this->sourceBase = trim(substr($line, strpos($line, ':') + 1));
                continue;
            }

            // Si la línea está vacía o solo tiene espacios, saltar
            if (trim($line) === '') {
                continue;
            }

            // Calcular nivel de indentación (2 espacios por nivel)
            $indentLevel = 0;
            $content = $line;
            while (str_starts_with($content, '  ')) {
                $indentLevel++;
                $content = substr($content, 2);
            }

            $content = trim($content);

            // Si está vacío después de trim, saltar
            if ($content === '') {
                continue;
            }

            // Procesar las líneas según su tipo
            if (preg_match('/^\[CARPETA\]\s+(.+)$/', $content, $m)) {
                // Es una carpeta
                $folderName = trim($m[1]);

                // Pop de la pila hasta el nivel correcto
                while (!empty($stack) && $stack[count($stack) - 1]['level'] >= $indentLevel) {
                    array_pop($stack);
                }

                // Detectar la carpeta raíz: primer [CARPETA] después del header
                if (empty($stack) && !$baseFound) {
                    $baseFound = true;
                    $stack[] = ['name' => $folderName, 'level' => $indentLevel];
                    continue;
                }

                $stack[] = ['name' => $folderName, 'level' => $indentLevel];

            } elseif (preg_match('/^-\s+\[ARCHIVO\]\s+(.+?)(?:\s+\(([\d.,]+\s*(?:B|KB|MB|GB)\))?$/', $content, $m)) {
                // Es un archivo - formato: - [ARCHIVO] nombre.ext (tamaño)
                $fileName = trim($m[1]);

                // Verificar si es un archivo a omitir
                if ($this->shouldSkipFile($fileName)) {
                    continue;
                }

                // Construir la ruta desde la pila de carpetas
                $folderPath = [];
                foreach ($stack as $folder) {
                    $folderPath[] = $folder['name'];
                }

                // Extraer extensión y nombre base
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $baseName = pathinfo($fileName, PATHINFO_FILENAME);
                $version = 1;

                if (preg_match('/- V(\d+)$/', $baseName, $vm)) {
                    $version = (int) $vm[1];
                    $baseName = preg_replace('/- V\d+$/', '', $baseName);
                }

                $baseName = trim($baseName) ?: 'Sin título';

                // Determinar el tamaño
                $size = 0;
                if (isset($m[2])) {
                    $size = $this->parseSize($m[2]);
                }

                // Construir la ruta de origen completa
                $sourcePath = $this->sourceBase;
                foreach ($folderPath as $seg) {
                    $sourcePath .= '\\' . $seg;
                }
                $sourcePath .= '\\' . $fileName;

                // Determinar el tipo de entidad y nombre
                $entityType = 'equipment';
                $entityName = 'Equipos_Varios';
                $folderPathLower = array_map(fn($s) => $this->normalizeFolderName($s), $folderPath);

                foreach ($folderPath as $index => $folderName) {
                    $normalized = $this->normalizeFolderName($folderName);
                    if ($normalized === 'equipos' || $normalized === 'equipo') {
                        $entityType = 'equipment';
                        $entityName = $folderPath[$index + 1] ?? 'Equipos_Varios';
                        break;
                    } elseif ($normalized === 'proveedores' || $normalized === 'proveedor') {
                        $entityType = 'supplier';
                        $entityName = $folderPath[$index + 1] ?? 'Proveedores_Varios';
                        break;
                    } elseif ($normalized === 'contactos' || $normalized === 'contacto') {
                        $entityType = 'person';
                        $entityName = $folderPath[$index + 1] ?? 'Contactos_Varios';
                        break;
                    }
                }

                // Determinar categoría y el resto del contexto
                $contextSegments = [];
                $category = null;
                $captureContext = false;
                foreach ($folderPath as $fp) {
                    if ($captureContext) {
                        $cat = $this->mapFolderToCategory($fp);
                        if ($cat !== null) {
                            $category = $cat;
                            $captureContext = false;
                        } else {
                            $contextSegments[] = $fp;
                        }
                    } else {
                        $normalized = $this->normalizeFolderName($fp);
                        if (in_array($normalized, array_map(fn($r) => $this->normalizeFolderName($r), self::ROOT_FOLDERS), true)) {
                            $captureContext = true;
                            continue;
                        }
                        // Si no es una carpeta raíz, revisar categoría
                        $cat = $this->mapFolderToCategory($fp);
                        if ($cat !== null) {
                            $category = $cat;
                        }
                    }
                }

                // Construir nombre del documento
                $docName = $this->buildDocumentName($baseName, $contextSegments);

                $this->fileMap[] = [
                    'source' => $sourcePath,
                    'dest' => '', // Se calcula después
                    'entityType' => $entityType,
                    'entityName' => $entityName,
                    'category' => $category,
                    'docName' => $docName,
                    'version' => $version,
                    'ext' => $this->sanitizeExtension($extension),
                    'size' => $size,
                ];
            }
        }
    }

    /**
     * Construye el mapa de archivos con las rutas de destino.
     */
    protected function buildFileMap(): void
    {
        foreach ($this->fileMap as &$item) {
            $baseModel = $this->getOrCreateEntity($item['entityType'], $item['entityName']);
            $item['dest'] = $this->buildDestPath(
                $baseModel,
                $item['entityType'],
                $item['entityName'],
                $item['category'],
                $item['docName'],
                $item['version'],
                $item['ext']
            );
        }
        unset($item);
    }

    /**
     * Guarda el mapeo origen->destino en un archivo de texto.
     */
    protected function saveMapToFile(string $outputPath): void
    {
        $content = "MAPA ORIGEN -> DESTINO\n";
        $content .= "Ruta base origen: {$this->sourceBase}\n";
        $content .= str_repeat('=', 80) . "\n\n";

        foreach ($this->fileMap as $item) {
            $content .= "ORIGEN: {$item['source']}\n";
            $content .= "DESTINO: {$item['dest']}\n";
            $content .= "ENTIDAD: {$item['entityType']} / {$item['entityName']}\n";
            $content .= "CATEGORÍA: {$item['category']?->value ?? 'N/A'}\n";
            $content .= "DOCUMENTO: {$item['docName']} (v{$item['version']})\n";
            $content .= str_repeat('-', 80) . "\n";
        }

        File::put($outputPath, $content);
    }

    /**
     * Parsea un string de tamaño a bytes.
     */
    protected function parseSize(string $sizeStr): int
    {
        $sizeStr = trim($sizeStr);
        if (preg_match('/^([\d.,]+)\s*(B|KB|MB|GB)$/i', $sizeStr, $m)) {
            $value = (float) str_replace(',', '', $m[1]);
            $unit = strtoupper($m[2]);
            return match ($unit) {
                'B' => (int) $value,
                'KB' => (int) ($value * 1024),
                'MB' => (int) ($value * 1024 * 1024),
                'GB' => (int) ($value * 1024 * 1024 * 1024),
                default => (int) $value,
            };
        }
        return (int) $sizeStr;
    }

    // ===== MÉTODOS REUTILIZADOS DE MigrateFilesCommand =====

    protected function getOrCreateEntity(string $type, string $name)
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

    protected function resolveCategoryAndContext(array $segments): array
    {
        foreach ($segments as $index => $segment) {
            $category = $this->mapFolderToCategory($segment);
            if ($category !== null) {
                return [$category, array_slice($segments, $index + 1)];
            }
        }
        return [null, $segments];
    }

    protected function mapFolderToCategory(string $folderName): ?Category
    {
        return match ($this->normalizeFolderName($folderName)) {
            'plano', 'planos' => Category::Blueprint,
            'manual', 'manuales' => Category::Manual,
            'reporte', 'reportes', 'inspecciones' => Category::Report,
            'especificaciontecnica', 'especificacionestecnicas', 'informaciontecnica',
            'hojadedatos', 'consultadecampo', 'consultasencampo', 'catalogo', 'catalogos' => Category::Specs,
            'oferta', 'ofertas' => Category::Offer,
            'foto', 'fotos' => Category::Photo,
            default => null,
        };
    }

    protected function buildDocumentName(string $baseName, array $contextSegments): string
    {
        $segments = collect($contextSegments)
            ->map(fn(string $segment) => $this->sanitizePathSegment($segment))
            ->filter();

        $segments->push($this->sanitizePathSegment($baseName));

        return $segments
            ->unique()
            ->implode(' - ') ?: 'Sin título';
    }

    protected function splitPathSegments(string $path): array
    {
        if ($path === '' || $path === '.') {
            return [];
        }
        return array_values(array_filter(explode('/', str_replace('\\', '/', $path)), fn(string $segment) => $segment !== ''));
    }

    protected function shouldSkipFile(string $filename): bool
    {
        return in_array(mb_strtolower($filename), self::SKIPPED_FILENAMES, true);
    }

    protected function sanitizeExtension(string $extension): string
    {
        return trim((string) preg_replace('/[^A-Za-z0-9]+/', '', $extension));
    }

    protected function sanitizePathSegment(string $value): string
    {
        $value = str_replace(['\\', '/'], ' ', $value);
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';
        return trim($value, " .\t\n\r\0\x0B");
    }

    protected function normalizeFolderName(string $name): string
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

    protected function getOrCreateDocument(string $name, ?Model $parentModel, ?Category $category): Document
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

        while ($this->documentExists($uniqueName, $parentModel, $category)) {
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

    protected function documentExists(string $name, ?Model $parentModel, ?Category $category): bool
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

    protected function buildDestPath(?Model $parentModel, string $entityType, string $entityName, ?Category $category, string $documentName, int $version, string $extension): string
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

    protected function resolveEntityFolderName(Model $model): string
    {
        if ($model instanceof Person) {
            $email = trim((string) ($model->email ?? ''));
            return $email !== ''
                ? $model->name . ' - ' . $email
                : $model->name;
        }
        return $model->name;
    }

    protected function makeUniquePath($storage, string $path): string
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

    protected function reportSkippedFiles(): void
    {
        if ($this->skippedWithError === []) {
            return;
        }

        $this->newLine();
        $this->warn('Se omitieron archivos con error durante la migración: ' . count($this->skippedWithError));

        foreach (array_slice($this->skippedWithError, 0, 30) as $item) {
            $this->line('- ' . $item['file'] . ' -> ' . $item['error']);
        }

        if (count($this->skippedWithError) > 30) {
            $this->line('... y ' . (count($this->skippedWithError) - 30) . ' archivos más.');
        }
    }
}
