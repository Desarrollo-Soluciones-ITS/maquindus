<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

$disk = Storage::disk('local');

// 1. Eliminar archivos duplicados (los que tienen _1 antes de la extensión)
$filesToDelete = [
    'Equipos/Compresor Atlas - copia/Manuales/Manual de operación Compresor Atlas - V1_1.txt',
    'Equipos/Compresor Atlas - copia/Manuales/Manual de operación Compresor Atlas - V2_1.txt',
];

foreach ($filesToDelete as $file) {
    if ($disk->exists($file)) {
        $disk->delete($file);
        echo "Eliminado duplicado: $file\n";
    }
}

// 2. Corregir las rutas en la BD para que apunten a los archivos originales (sin _1)
$newEquipment = App\Models\Equipment::where('name', 'Compresor Atlas - copia')->first();
if ($newEquipment) {
    foreach ($newEquipment->documents as $doc) {
        foreach ($doc->files as $file) {
            $correctPath = str_replace('_1.txt', '.txt', $file->path);
            if ($file->path !== $correctPath) {
                $file->update(['path' => $correctPath]);
                echo "Corregida ruta: {$file->path} -> {$correctPath}\n";
            }
        }
    }
}

echo "\n=== Verificación final ===\n";
$files = $disk->allFiles('Equipos/Compresor Atlas - copia');
echo "Archivos en storage:\n";
foreach ($files as $f) {
    echo "- $f\n";
}

echo "\nDocumentos del equipo:\n";
$newEquipment = App\Models\Equipment::where('name', 'Compresor Atlas - copia')->first();
if ($newEquipment) {
    foreach ($newEquipment->documents as $doc) {
        echo "- Documento: {$doc->name}\n";
        foreach ($doc->files as $file) {
            echo "  * Archivo: {$file->path} (V{$file->version}) [ID: {$file->id}]\n";
        }
    }
}
