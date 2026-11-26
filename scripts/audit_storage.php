<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\File;
use Illuminate\Support\Facades\Storage;

$disk = Storage::disk('local');
$root = $disk->path('');

echo "=== Auditoria de almacenamiento privado ===\n";
echo "Raiz local: {$root}\n";
echo "Existe: " . (is_dir($root) ? 'SI' : 'NO') . "\n\n";

$dbFiles = 0;
$checked = 0;
$missing = [];
$absolute = [];

foreach (File::withTrashed()->select(['id', 'path'])->cursor() as $file) {
    $dbFiles++;
    $path = (string) $file->path;

    if ($path === '') {
        continue;
    }

    $checked++;

    $isAbsolute = str_contains($path, ':\\')
        || str_starts_with($path, '\\\\')
        || str_starts_with($path, '//');

    if ($isAbsolute) {
        $absolute[] = $path;
        continue;
    }

    if (! $disk->exists($path)) {
        $missing[] = $path;
    }
}

$diskFiles = $disk->allFiles();
$diskSize = 0;

foreach ($diskFiles as $path) {
    try {
        $diskSize += $disk->size($path);
    } catch (\Throwable) {
        // Ignorar archivos que no se puedan leer.
    }
}

echo "Archivos en base de datos: {$dbFiles}\n";
echo "Rutas comprobadas: {$checked}\n";
echo "Rutas absolutas en base de datos: " . count($absolute) . "\n";
echo "Rutas relativas faltantes en disco: " . count($missing) . "\n";
echo "Archivos fisicos en disco: " . count($diskFiles) . "\n";
echo "Tamano fisico: " . number_format($diskSize) . " bytes\n";

if ($absolute !== []) {
    echo "\nPrimeras rutas absolutas (deben corregirse a rutas relativas):\n";
    foreach (array_slice($absolute, 0, 20) as $path) {
        echo "  - {$path}\n";
    }
}

if ($missing !== []) {
    echo "\nPrimeras rutas faltantes (pueden ser registros historicos ya inconsistentes):\n";
    foreach (array_slice($missing, 0, 20) as $path) {
        echo "  - {$path}\n";
    }
}
