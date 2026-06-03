<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Equipment;
use App\Models\Document;
use App\Models\File;
use Illuminate\Support\Facades\Storage;

echo "=== ESTADO ACTUAL ===\n\n";

$disk = Storage::disk('local');

// Equipos en BD
$equipos = Equipment::withTrashed()->get();
echo "Equipos en BD:\n";
foreach ($equipos as $eq) {
    echo "  - {$eq->name} (id: {$eq->id})\n";
}

// Documentos en BD
echo "\nDocumentos en BD:\n";
$docs = Document::withTrashed()->get();
if ($docs->isEmpty()) {
    echo "  (ninguno)\n";
} else {
    foreach ($docs as $doc) {
        echo "  - {$doc->name} [{$doc->documentable_type}]\n";
    }
}

// Archivos físicos en Equipos/
echo "\nArchivos físicos en Equipos/:\n";
$files = $disk->allFiles('Equipos');
if (empty($files)) {
    echo "  (ninguno)\n";
} else {
    foreach ($files as $f) {
        echo "  - {$f}\n";
    }
}

// Archivos en BD
echo "\nArchivos en BD:\n";
$dbFiles = File::withTrashed()->get();
if ($dbFiles->isEmpty()) {
    echo "  (ninguno)\n";
} else {
    foreach ($dbFiles as $f) {
        echo "  - {$f->path} (doc: {$f->document_id})\n";
    }
}
