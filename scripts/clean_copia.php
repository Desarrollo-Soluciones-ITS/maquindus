<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

$disk = Storage::disk('local');

// 1. Eliminar archivos del storage que están bajo "Compresor Atlas - copia"
$files = $disk->allFiles('Equipos/Compresor Atlas - copia');
echo "Archivos encontrados en storage para eliminar: " . count($files) . "\n";
foreach ($files as $file) {
    $disk->delete($file);
    echo "  Eliminado: $file\n";
}

// 2. Eliminar carpetas vacías
$dirs = $disk->allDirectories('Equipos/Compresor Atlas - copia');
foreach (array_reverse($dirs) as $dir) {
    $disk->deleteDirectory($dir);
    echo "  Directorio eliminado: $dir\n";
}
if ($disk->exists('Equipos/Compresor Atlas - copia')) {
    $disk->deleteDirectory('Equipos/Compresor Atlas - copia');
    echo "  Directorio raíz eliminado: Equipos/Compresor Atlas - copia\n";
}

// 3. Buscar documentos asociados al equipo "Compresor Atlas" que tengan "copia" en su nombre
echo "\nBuscando documentos incorrectos...\n";
$equipoOriginal = App\Models\Equipment::where('name', 'Compresor Atlas')->first();
if ($equipoOriginal) {
    $docsToDelete = App\Models\Document::where('documentable_type', App\Models\Equipment::class)
        ->where('documentable_id', $equipoOriginal->id)
        ->where('name', 'like', '%copia%')
        ->get();
    
    foreach ($docsToDelete as $doc) {
        echo "  Eliminando documento: {$doc->name} (ID: {$doc->id})\n";
        $doc->files()->delete();
        $doc->delete();
    }
}

echo "\n=== Listo. Ahora se puede ejecutar la migración correcta ===\n";
