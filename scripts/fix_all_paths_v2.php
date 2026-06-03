<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Equipment;
use App\Models\File;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

echo "=== MOVIENDO TODOS LOS ARCHIVOS A Equipos/{nombre}/... ===\n\n";

$disk = Storage::disk('local');

function getEquipmentFromDocument(Document $doc): ?Equipment {
    $model = $doc->documentable;
    if (!$model) return null;
    
    // Si es directamente un equipo
    if ($model instanceof Equipment) return $model;
    
    // Si es una entidad hija que tiene relación equipment() (belongsTo - singular)
    if (method_exists($model, 'equipment')) {
        $relation = $model->equipment();
        if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
            return $model->equipment;
        }
        // Si es BelongsToMany (como Part), tomar el primero
        if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
            return $model->equipment()->first();
        }
    }
    
    return null;
}

function getSectionFromPath(string $path): ?string {
    $pathLower = strtolower($path);
    
    if (str_contains($pathLower, 'hojas de datos') || str_contains($pathLower, 'hoja de datos')) {
        return 'Hoja De Datos';
    }
    if (str_contains($pathLower, 'planos')) {
        return 'Planos';
    }
    if (str_contains($pathLower, 'catálogos') || str_contains($pathLower, 'catalogos')) {
        return 'Catálogos';
    }
    if (str_contains($pathLower, 'especificaciones técnicas') || str_contains($pathLower, 'especificaciones tecnicas')) {
        return 'Especificaciones Tecnicas';
    }
    if (str_contains($pathLower, 'normas')) {
        return 'Normas';
    }
    if (str_contains($pathLower, 'consultas de campo')) {
        return 'Consultas de Campo';
    }
    if (str_contains($pathLower, 'reportes')) {
        return 'Reportes';
    }
    if (str_contains($pathLower, 'repuestos')) {
        return 'Repuestos';
    }
    if (str_contains($pathLower, 'manuales') || str_contains($pathLower, 'manual')) {
        return 'Manuales';
    }
    if (str_contains($pathLower, 'ofertas')) {
        return 'Ofertas';
    }
    if (str_contains($pathLower, 'fotos') || str_contains($pathLower, 'foto')) {
        return 'Fotos';
    }
    if (str_contains($pathLower, 'órdenes de compra') || str_contains($pathLower, 'ordenes de compra')) {
        return 'Ordenes de Compra';
    }
    
    return null;
}

function getDescriptorFromPath(string $path): ?string {
    $parts = explode('/', $path);
    foreach ($parts as $i => $part) {
        $lower = strtolower($part);
        if (in_array($lower, ['hojas de datos', 'hoja de datos', 'planos', 'catálogos', 'catalogos', 
            'especificaciones técnicas', 'especificaciones tecnicas', 'normas', 'consultas de campo',
            'reportes', 'repuestos', 'manuales', 'manual', 'ofertas', 'fotos', 'foto',
            'órdenes de compra', 'ordenes de compra'])) {
            if ($i > 0) {
                return $parts[$i - 1];
            }
        }
    }
    return null;
}

function getDescriptorFromModel($model): string {
    if (!$model) return 'General';
    
    foreach (['name', 'document_name', 'sheet_number', 'blueprint_number', 'revision_name', 'part_number', 'catalog_number'] as $key) {
        $value = $model->$key ?? null;
        if (filled($value)) {
            return trim((string) $value);
        }
    }
    
    return 'General';
}

$totalMovidos = 0;

// Procesar archivos de BD que NO están en Equipos/{nombre}/
$files = File::withTrashed()->get();
echo "Total archivos en BD: " . $files->count() . "\n";

foreach ($files as $file) {
    $oldPath = $file->path;
    
    // Si ya está en Equipos/{nombre}/, saltar
    if (preg_match('#^Equipos/[^/]+/#', $oldPath)) {
        continue;
    }
    
    echo "\nProcesando: {$oldPath}\n";
    
    $doc = $file->document;
    if (!$doc) {
        echo "  ⚠ Documento no encontrado, saltando\n";
        continue;
    }
    
    $equipment = getEquipmentFromDocument($doc);
    if (!$equipment) {
        echo "  ⚠ No se pudo determinar equipo asociado, saltando\n";
        continue;
    }
    
    $equipoName = $equipment->name;
    $section = getSectionFromPath($oldPath) ?? 'General';
    $descriptor = getDescriptorFromModel($doc->documentable) ?? getDescriptorFromPath($oldPath) ?? 'General';
    $filename = basename($oldPath);
    
    $newPath = "Equipos/{$equipoName}/{$section}/{$descriptor}/{$filename}";
    
    echo "  Equipo: {$equipoName}\n";
    echo "  Sección: {$section}\n";
    echo "  Descriptor: {$descriptor}\n";
    echo "  Nueva ruta: {$newPath}\n";
    
    if (!$disk->exists($oldPath)) {
        echo "  ⚠ Archivo origen no existe en disco, saltando\n";
        continue;
    }
    
    $counter = 1;
    $uniquePath = $newPath;
    while ($disk->exists($uniquePath)) {
        $info = pathinfo($newPath);
        $uniquePath = $info['dirname'] . '/' . $info['filename'] . "_{$counter}." . $info['extension'];
        $counter++;
    }
    
    try {
        $dir = dirname($uniquePath);
        if (!$disk->exists($dir)) {
            $disk->makeDirectory($dir);
        }
        
        $disk->copy($oldPath, $uniquePath);
        $file->path = $uniquePath;
        $file->save();
        
        echo "  ✓ Movido exitosamente\n";
        $totalMovidos++;
    } catch (\Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n\n=== RESUMEN ===\n";
echo "Total archivos movidos: {$totalMovidos}\n";

echo "\nArchivos que aún están fuera de Equipos/:\n";
$remainingFiles = File::withTrashed()->get()->filter(function($f) {
    return !preg_match('#^Equipos/[^/]+/#', $f->path);
});
if ($remainingFiles->isEmpty()) {
    echo "  (ninguno - todos están en Equipos/{nombre}/)\n";
} else {
    foreach ($remainingFiles as $f) {
        echo "  - {$f->path}\n";
    }
}

echo "\n¡Proceso completado!\n";
