<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "=== EQUIPOS ===\n";
$equipments = App\Models\Equipment::all();
foreach ($equipments as $e) {
    echo "- {$e->name} (ID: {$e->id})\n";
}

echo "\n=== GENERADOR PERKINS - COPIA ===\n";
$e = App\Models\Equipment::where('name', 'Generador Perkins - copia')->first();
if ($e) {
    echo "Equipo: {$e->name}\n";
    foreach ($e->documents as $doc) {
        echo "  Documento: {$doc->name} (Cat: " . ($doc->category?->value ?? 'N/A') . ")\n";
        foreach ($doc->files as $file) {
            echo "    Archivo: {$file->path} V{$file->version}\n";
        }
    }
} else {
    echo "No encontrado\n";
}

echo "\n=== COMPRESOR ATLAS - COPIA ===\n";
$e = App\Models\Equipment::where('name', 'Compresor Atlas - copia')->first();
if ($e) {
    echo "Equipo: {$e->name}\n";
    foreach ($e->documents as $doc) {
        echo "  Documento: {$doc->name} (Cat: " . ($doc->category?->value ?? 'N/A') . ")\n";
        foreach ($doc->files as $file) {
            echo "    Archivo: {$file->path} V{$file->version}\n";
        }
    }
} else {
    echo "No encontrado\n";
}

echo "\n=== ARCHIVOS EN STORAGE ===\n";
$files = Storage::disk('local')->allFiles('Equipos');
foreach ($files as $f) {
    echo "- $f\n";
}

echo "\n=== TOTALES BD ===\n";
echo "Equipos: " . App\Models\Equipment::count() . "\n";
echo "Documentos: " . App\Models\Document::count() . "\n";
echo "Archivos: " . App\Models\File::count() . "\n";
