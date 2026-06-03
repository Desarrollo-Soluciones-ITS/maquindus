<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$equipments = App\Models\Equipment::all();
echo "=== EQUIPOS EN BD ===\n";
foreach ($equipments as $e) {
    echo "- {$e->name} (ID: {$e->id})\n";
}

echo "\n=== DOCUMENTOS DEL NUEVO EQUIPO ===\n";
$newEquipment = App\Models\Equipment::where('name', 'Compresor Atlas - copia')->first();
if ($newEquipment) {
    echo "Equipo encontrado!\n";
    foreach ($newEquipment->documents as $doc) {
        echo "- Documento: {$doc->name} (Categoría: " . ($doc->category?->value ?? 'ninguna') . ")\n";
        foreach ($doc->files as $file) {
            echo "  * Archivo: {$file->path} (V{$file->version})\n";
        }
    }
}

echo "\n=== ARCHIVOS EN STORAGE ===\n";
$storage = Illuminate\Support\Facades\Storage::disk('local');
$files = $storage->allFiles('Equipos/Compresor Atlas - copia');
foreach ($files as $f) {
    echo "- $f\n";
}

echo "\n=== TOTALES BD ===\n";
echo "Equipos: " . App\Models\Equipment::count() . "\n";
echo "Documentos: " . App\Models\Document::count() . "\n";
echo "Archivos: " . App\Models\File::count() . "\n";
