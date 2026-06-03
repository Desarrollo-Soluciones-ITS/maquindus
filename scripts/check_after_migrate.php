<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Equipment;
use App\Models\Document;
use App\Models\File;

echo "=== ESTADO ACTUAL DEL SISTEMA ===\n\n";

// Equipos
$equipos = Equipment::withTrashed()->get();
echo "Equipos en BD:\n";
foreach ($equipos as $eq) {
    echo "  - {$eq->name} (id: {$eq->id}, trashed: " . ($eq->trashed() ? 'SI' : 'NO') . ")\n";
}

echo "\n--- Documentos ---\n";
$docs = Document::withTrashed()->get();
foreach ($docs as $doc) {
    $type = class_basename($doc->documentable_type);
    $cat = $doc->category?->value ?? 'SIN CAT';
    echo "  [{$type}] {$doc->name} (cat: {$cat})\n";
    $files = $doc->files;
    foreach ($files as $f) {
        echo "    -> FILE: {$f->path}\n";
    }
}

echo "\n--- Archivos migrados al storage ---\n";
$storageFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(storage_path('app/private/Equipos'), RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if ($file->isFile()) {
        $storageFiles[] = str_replace('\\', '/', $file->getPathname());
    }
}
echo "Total archivos en storage/Equipos: " . count($storageFiles) . "\n";
foreach ($storageFiles as $sf) {
    echo "  {$sf}\n";
}
