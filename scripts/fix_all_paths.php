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
$equipos = Equipment::withTrashed()->get();

// Mapa de rutas viejas a nuevas secciones
// Formato: [old_path_prefix => [equipo_nombre, nueva_seccion]]
function findEquipmentForOldPath(string $path, $equipos): ?array {
    // Buscar por nombre de equipo en la ruta
    foreach ($equipos as $eq) {
        $eqName = $eq->name;
        if (str_contains($path, $eqName)) {
            return [$eq->name, null];
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
    // Extraer el nombre del descriptor (ej: "DS-001" de "Hojas de datos/DS-001/...")
    $parts = explode('/', $path);
    // Buscar un segmento que sea un código/nombre significativo
    foreach ($parts as $i => $part) {
        $lower = strtolower($part);
        // Si encontramos una sección conocida, el segmento anterior podría ser el descriptor
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

$totalMovidos = 0;

// 1. Primero, procesar archivos de BD que NO están en Equipos/{nombre}/
$files = File::withTrashed()->get();
echo "Total archivos en BD: " . $files->count() . "\n";

foreach ($files as $file) {
    $oldPath = $file->path;
    
    // Si ya está en Equipos/{nombre}/, saltar
    if (preg_match('#^Equipos/[^/]+/#', $oldPath)) {
        continue;
    }
    
    echo "\nProcesando: {$oldPath}\n";
    
    // Buscar el documento para obtener el equipo asociado
    $doc = $file->document;
    if (!$doc) {
        echo "  ⚠ Documento no encontrado, saltando\n";
        continue;
    }
    
    // Obtener el modelo padre (equipment, supplier, person, etc.)
    $parentModel = $doc->documentable;
    if (!$parentModel) {
        echo "  ⚠ Modelo padre no encontrado, saltando\n";
        continue;
    }
    
    // Solo procesar si es un equipo
    if (!($parentModel instanceof Equipment)) {
        echo "  ⚠ No es equipo (es: " . get_class($parentModel) . "), saltando\n";
        continue;
    }
    
    $equipoName = $parentModel->name;
    $section = getSectionFromPath($oldPath) ?? 'General';
    $descriptor = getDescriptorFromPath($oldPath) ?? 'General';
    $filename = basename($oldPath);
    
    $newPath = "Equipos/{$equipoName}/{$section}/{$descriptor}/{$filename}";
    
    echo "  Equipo: {$equipoName}\n";
    echo "  Sección: {$section}\n";
    echo "  Descriptor: {$descriptor}\n";
    echo "  Nueva ruta: {$newPath}\n";
    
    // Verificar si el archivo origen existe
    if (!$disk->exists($oldPath)) {
        echo "  ⚠ Archivo origen no existe en disco, saltando\n";
        continue;
    }
    
    // Si el destino ya existe, agregar sufijo
    $counter = 1;
    $uniquePath = $newPath;
    while ($disk->exists($uniquePath)) {
        $info = pathinfo($newPath);
        $uniquePath = $info['dirname'] . '/' . $info['filename'] . "_{$counter}." . $info['extension'];
        $counter++;
    }
    
    try {
        // Crear directorio si no existe
        $dir = dirname($uniquePath);
        if (!$disk->exists($dir)) {
            $disk->makeDirectory($dir);
        }
        
        // Copiar archivo
        $disk->copy($oldPath, $uniquePath);
        
        // Actualizar ruta en BD
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

// 2. Mostrar archivos que quedaron fuera de Equipos/
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
