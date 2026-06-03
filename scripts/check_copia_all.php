<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Verificar equipos copia
echo "=== EQUIPOS CON 'copia' EN NOMBRE ===\n";
$equipos = App\Models\Equipment::withTrashed()
    ->where('name', 'like', '%copia%')
    ->orWhere('name', 'like', '%Copy%')
    ->get();

foreach ($equipos as $eq) {
    echo "\n--- {$eq->name} (id: {$eq->id}) ---\n";
    echo "  Trashed: " . ($eq->trashed() ? 'SI' : 'NO') . "\n";
    echo "  Documentos genéricos: " . $eq->documents()->count() . "\n";
    echo "  Hojas datos: " . $eq->dataSheets()->count() . "\n";
    echo "  Planos: " . $eq->blueprints()->count() . "\n";
    echo "  Catálogos: " . $eq->catalogs()->count() . "\n";
    echo "  Especs técnicas: " . $eq->technicalSpecifications()->count() . "\n";
    echo "  Normas: " . $eq->standards()->count() . "\n";
    echo "  Reportes: " . $eq->reports()->count() . "\n";
    echo "  Consultas campo: " . $eq->fieldQueries()->count() . "\n";
    
    $docs = $eq->documents;
    foreach ($docs as $doc) {
        echo "  DOC: {$doc->name} (cat: {$doc->category?->value})\n";
        foreach ($doc->files as $file) {
            echo "    FILE: {$file->path}\n";
        }
    }
}

// 2. Verificar si hay carpetas en storage con "copia"
echo "\n\n=== CARPETAS EN Equipos/ CON 'copia' ===\n";
$disk = Illuminate\Support\Facades\Storage::disk('local');
$folders = $disk->directories('Equipos');
foreach ($folders as $folder) {
    if (stripos($folder, 'copia') !== false || stripos($folder, 'copy') !== false) {
        echo "\n{$folder}:\n";
        $files = $disk->allFiles($folder);
        foreach ($files as $file) {
            echo "  - {$file}\n";
        }
    }
}

// 3. Verificar si hay otros equipos copia en storage pero no en BD
echo "\n\n=== CARPETAS EN Equipos/ NO REGISTRADAS EN BD ===\n";
$bdNames = App\Models\Equipment::withTrashed()->pluck('name')->map(fn($n) => strtolower(trim($n)))->toArray();
foreach ($folders as $folder) {
    $name = strtolower(trim(basename($folder)));
    if (!in_array($name, $bdNames)) {
        echo "  Carpeta sin equipo en BD: {$folder}\n";
    }
}
