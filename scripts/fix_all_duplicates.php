<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

$disk = Storage::disk('local');

// Equipos copia a limpiar
$copiaEquipments = ['Compresor Atlas - copia', 'Generador Perkins - copia'];

foreach ($copiaEquipments as $equipName) {
    echo "=== Procesando: $equipName ===\n";
    
    $equipment = App\Models\Equipment::where('name', $equipName)->first();
    if (!$equipment) {
        echo "Equipo no encontrado en BD\n\n";
        continue;
    }
    
    // 1. Identificar archivos duplicados en storage (los que tienen _1, _2 antes de extensión)
    // y archivos originales que están en subcarpetas (deben eliminarse porque ya se copiaron a la raíz)
    $allFiles = $disk->allFiles('Equipos/' . $equipName);
    
    // Archivos que están dentro de subcarpetas (los originales que el Finder encontró)
    $nestedOriginals = [];
    foreach ($allFiles as $f) {
        $parts = explode('/', $f);
        // Si tiene más de 3 partes (Equipos/Nombre/Subcarpeta/archivo), es un original anidado
        if (count($parts) > 3) {
            $nestedOriginals[] = $f;
        }
    }
    
    echo "Archivos originales anidados (se eliminarán del storage): " . count($nestedOriginals) . "\n";
    foreach ($nestedOriginals as $f) {
        $disk->delete($f);
        echo "  Eliminado: $f\n";
    }
    
    // 2. Archivos con _1, _2, etc. que fueron creados como copias de seguridad
    $versionedCopies = [];
    foreach ($allFiles as $f) {
        $basename = basename($f);
        // Patrón: algo - V1_1.ext, algo - V1_2.ext
        if (preg_match('/_(\d+)\.\w+$/', $basename) || preg_match('/ - V1_\d/', $basename)) {
            // Pero algunos son archivos válidos que apuntan a documentos únicos
            // Solo eliminar si existen en BD con esa ruta exacta
            $versionedCopies[] = $f;
        }
    }
    
    // 3. Limpiar duplicados en BD: eliminar documentos que tienen nombres con sufijos _1, _2
    // que se crearon por error (documentos extra que no deberían existir)
    echo "\nDocumentos del equipo:\n";
    foreach ($equipment->documents as $doc) {
        $hasNestedFile = false;
        foreach ($doc->files as $file) {
            $parts = explode('/', $file->path);
            if (count($parts) > 3 && !str_contains($file->path, '_1 - V') && !str_contains($file->path, '_2 - V')) {
                // Este archivo apunta a un original anidado que ya no existe
                echo "  Archivo con ruta inválida (original anidado): {$file->path}\n";
                // Si es el único archivo del doc, marcar doc para eliminar
            }
        }
    }
}

echo "\n=== Limpieza masiva de documentos y archivos extra de copias ===\n";

// Estrategia: para los equipos copia, quedarse solo con los documentos cuyos archivos
// NO tengan _1, _2 en la ruta (documentos limpios sin duplicación)
foreach ($copiaEquipments as $equipName) {
    $equipment = App\Models\Equipment::where('name', $equipName)->first();
    if (!$equipment) continue;
    
    foreach ($equipment->documents as $doc) {
        $filesToKeep = [];
        foreach ($doc->files as $file) {
            // Si la ruta contiene _1_ o _2_ o está en subcarpeta, es duplicado
            $basename = basename($file->path);
            if (preg_match('/_[12]_/', $basename) || preg_match('/_[12]\.\w+$/', $basename)) {
                echo "  Eliminando archivo duplicado BD: {$file->path}\n";
                $disk->delete($file->path);
                $file->delete();
            } elseif (substr_count($file->path, '/') > 2) {
                // Archivo en subcarpeta (original anidado)
                echo "  Eliminando archivo anidado BD: {$file->path}\n";
                $disk->delete($file->path);
                $file->delete();
            } else {
                $filesToKeep[] = $file;
            }
        }
        
        // Si el documento se quedó sin archivos, eliminarlo
        if ($doc->files()->count() == 0) {
            echo "  Eliminando documento vacío: {$doc->name}\n";
            $doc->delete();
        }
    }
}

echo "\n=== RESULTADO FINAL ===\n";
foreach ($copiaEquipments as $equipName) {
    $equipment = App\Models\Equipment::where('name', $equipName)->first();
    if (!$equipment) continue;
    
    echo "\n--- $equipName ---\n";
    foreach ($equipment->documents as $doc) {
        echo "Doc: {$doc->name} (Cat: " . ($doc->category?->value ?? 'N/A') . ")\n";
        foreach ($doc->files as $file) {
            echo "  File: {$file->path} V{$file->version}\n";
        }
    }
}

echo "\nArchivos restantes en storage:\n";
$files = $disk->allFiles('Equipos');
foreach ($files as $f) {
    if (str_contains($f, 'copia')) {
        echo "  $f\n";
    }
}

echo "\nTotales BD:\n";
echo "Equipos: " . App\Models\Equipment::count() . "\n";
echo "Documentos: " . App\Models\Document::count() . "\n";
echo "Archivos: " . App\Models\File::count() . "\n";
