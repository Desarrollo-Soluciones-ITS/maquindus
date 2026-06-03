<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Equipment;
use App\Models\File;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

echo "=== MOVIENDO ARCHIVOS A LA ESTRUCTURA CORRECTA ===\n\n";

$disk = Storage::disk('local');
$equipos = Equipment::withTrashed()->get();

$totalMovidos = 0;

foreach ($equipos as $equipo) {
    echo "\n--- Procesando equipo: {$equipo->name} ---\n";
    
    // Obtener todas las entidades hijas con sus documentos
    $relations = [
        'dataSheets' => 'Hojas de datos',
        'blueprints' => 'Planos',
        'catalogs' => 'Catálogos',
        'technicalSpecifications' => 'Especificaciones técnicas',
        'standards' => 'Normas',
        'fieldQueries' => 'Consultas de campo',
        'reports' => 'Reportes',
        'equipmentSpareParts' => 'Repuestos',
    ];
    
    foreach ($relations as $relation => $oldFolder) {
        $items = $equipo->$relation()->with('documents.files')->get();
        
        foreach ($items as $item) {
            foreach ($item->documents as $doc) {
                foreach ($doc->files as $file) {
                    $oldPath = $file->path;
                    
                    // Verificar si la ruta actual ya está dentro de Equipos/{nombre}/
                    $expectedPrefix = "Equipos/{$equipo->name}/";
                    if (str_starts_with($oldPath, $expectedPrefix)) {
                        continue; // Ya está en la ubicación correcta
                    }
                    
                    // Construir nueva ruta
                    $section = match ($relation) {
                        'dataSheets' => 'Hoja De Datos',
                        'blueprints' => 'Planos',
                        'catalogs' => 'Catálogos',
                        'technicalSpecifications' => 'Especificaciones Tecnicas',
                        'standards' => 'Normas',
                        'fieldQueries' => 'Consultas de Campo',
                        'reports' => 'Reportes',
                        'equipmentSpareParts' => 'Repuestos',
                        default => 'General',
                    };
                    
                    // Obtener descriptor (nombre del item)
                    $descriptor = $item->name ?? 'General';
                    
                    // Obtener nombre del archivo
                    $filename = basename($oldPath);
                    
                    $newPath = "Equipos/{$equipo->name}/{$section}/{$descriptor}/{$filename}";
                    
                    echo "  Moviendo: {$oldPath}\n";
                    echo "       ->: {$newPath}\n";
                    
                    // Verificar que el archivo origen existe
                    if (!$disk->exists($oldPath)) {
                        echo "       ⚠ Archivo origen no existe, saltando\n";
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
                    
                    // Copiar archivo
                    try {
                        $disk->copy($oldPath, $uniquePath);
                        
                        // Actualizar ruta en BD
                        $file->path = $uniquePath;
                        $file->save();
                        
                        echo "       ✓ Movido exitosamente\n";
                        $totalMovidos++;
                    } catch (\Exception $e) {
                        echo "       ✗ Error: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
    }
    
    // También procesar documentos genéricos (Manuales)
    $genericDocs = Document::where('documentable_type', Equipment::class)
        ->where('documentable_id', $equipo->id)
        ->whereNull('deleted_at')
        ->get();
    
    foreach ($genericDocs as $doc) {
        foreach ($doc->files as $file) {
            $oldPath = $file->path;
            
            $expectedPrefix = "Equipos/{$equipo->name}/";
            if (str_starts_with($oldPath, $expectedPrefix)) {
                continue;
            }
            
            $section = match ($doc->category?->value) {
                'Manuales' => 'Manuales',
                'Especificaciones Tecnicas' => 'Especificaciones Tecnicas',
                'Planos' => 'Planos',
                'Reportes' => 'Reportes',
                'Ofertas' => 'Ofertas',
                'Fotos' => 'Fotos',
                default => 'General',
            };
            
            $filename = basename($oldPath);
            $newPath = "Equipos/{$equipo->name}/{$section}/{$filename}";
            
            echo "  Moviendo (doc genérico): {$oldPath}\n";
            echo "       ->: {$newPath}\n";
            
            if (!$disk->exists($oldPath)) {
                echo "       ⚠ Archivo origen no existe, saltando\n";
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
                $disk->copy($oldPath, $uniquePath);
                $file->path = $uniquePath;
                $file->save();
                echo "       ✓ Movido exitosamente\n";
                $totalMovidos++;
            } catch (\Exception $e) {
                echo "       ✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }
}

echo "\n\n=== RESUMEN ===\n";
echo "Total archivos movidos: {$totalMovidos}\n";
echo "¡Proceso completado!\n";
