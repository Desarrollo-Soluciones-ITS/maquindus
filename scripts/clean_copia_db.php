<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

$copiaNames = ['Compresor Atlas - copia', 'Generador Perkins - copia'];

foreach ($copiaNames as $name) {
    $equipment = App\Models\Equipment::where('name', $name)->first();
    if (!$equipment) {
        echo "Equipo '$name' no encontrado en BD\n";
        continue;
    }
    
    echo "=== Eliminando: $name ===\n";
    echo "ID: {$equipment->id}\n";
    
    foreach ($equipment->documents as $doc) {
        echo "  Documento: {$doc->name}\n";
        foreach ($doc->files as $file) {
            echo "    Archivo: {$file->path} - ";
            if (Storage::disk('local')->exists($file->path)) {
                Storage::disk('local')->delete($file->path);
                echo "eliminado físicamente\n";
            } else {
                echo "no existe en storage\n";
            }
            $file->delete();
        }
        $doc->delete();
        echo "  Documento eliminado\n";
    }
    
    $equipment->delete();
    echo "Equipo eliminado\n\n";
}

echo "=== Totales actuales BD ===\n";
echo "Equipos: " . App\Models\Equipment::count() . "\n";
echo "Documentos: " . App\Models\Document::count() . "\n";
echo "Archivos: " . App\Models\File::count() . "\n";
