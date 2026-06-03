<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Equipment;
use App\Models\Document;
use App\Models\EquipmentDataSheet;
use App\Models\EquipmentBlueprint;
use App\Models\EquipmentCatalog;
use App\Models\EquipmentTechnicalSpecification;
use App\Models\EquipmentStandard;
use App\Models\EquipmentReport;
use App\Models\EquipmentFieldQuery;

echo "=== REPARANDO DOCUMENTOS DE EQUIPOS COPIA ===\n\n";

// Obtener equipos copia
$equipos = Equipment::withTrashed()
    ->where('name', 'like', '%copia%')
    ->get();

foreach ($equipos as $equipo) {
    echo "\n\n=== Procesando: {$equipo->name} ===\n";
    
    // Obtener documentos genéricos de este equipo
    $docs = Document::where('documentable_type', Equipment::class)
        ->where('documentable_id', $equipo->id)
        ->whereNull('deleted_at')
        ->get();
    
    echo "Documentos genéricos encontrados: " . $docs->count() . "\n";
    
    foreach ($docs as $doc) {
        $category = $doc->category;
        echo "\n  Documento: {$doc->name} (cat: {$category?->value})\n";
        
        if (!$category) {
            echo "    -> SIN CATEGORÍA, saltando\n";
            continue;
        }
        
        // Determinar qué entidad hija crear según la categoría
        $childModel = null;
        $childClass = null;
        
        switch ($category->value) {
            case 'Hojas De Datos':
                $childClass = EquipmentDataSheet::class;
                $childModel = EquipmentDataSheet::firstOrCreate(
                    ['equipment_id' => $equipo->id, 'sheet_number' => $doc->name],
                    ['revision' => '1', 'document_date' => now()]
                );
                break;
                
            case 'Planos':
                $childClass = EquipmentBlueprint::class;
                $childModel = EquipmentBlueprint::firstOrCreate(
                    ['equipment_id' => $equipo->id, 'blueprint_number' => $doc->name, 'name' => $doc->name],
                    ['revision' => '1', 'document_date' => now()]
                );
                break;
                
            case 'Catalogos':
            case 'Catálogos':
                $childClass = EquipmentCatalog::class;
                $childModel = EquipmentCatalog::firstOrCreate(
                    ['equipment_id' => $equipo->id, 'name' => $doc->name],
                    ['document_type' => 'General']
                );
                break;
                
            case 'Especificaciones Tecnicas':
            case 'Especificación Técnica':
                $childClass = EquipmentTechnicalSpecification::class;
                $childModel = EquipmentTechnicalSpecification::firstOrCreate(
                    ['equipment_id' => $equipo->id, 'revision_name' => $doc->name],
                    ['revision' => '1', 'document_date' => now()]
                );
                break;
                
            case 'Normas':
                $childClass = EquipmentStandard::class;
                $childModel = EquipmentStandard::firstOrCreate(
                    ['equipment_id' => $equipo->id, 'name' => $doc->name],
                    ['revision' => '1']
                );
                break;
                
            case 'Reportes':
            case 'Reporte':
                $childClass = EquipmentReport::class;
                $childModel = EquipmentReport::firstOrCreate(
                    ['equipment_id' => $equipo->id, 'document_name' => $doc->name],
                    ['document_type' => 'General', 'document_date' => now()]
                );
                break;
                
            case 'Consultas De Campo':
            case 'Consulta De Campo':
                $childClass = EquipmentFieldQuery::class;
                $childModel = EquipmentFieldQuery::firstOrCreate(
                    ['equipment_id' => $equipo->id, 'document_name' => $doc->name],
                    ['document_type' => 'General', 'document_date' => now()]
                );
                break;
                
            case 'Manuales':
            case 'Manual':
                echo "    -> Manuales: se quedan como documentos genéricos (visibles en pestaña Documentos)\n";
                continue 2;
                
            default:
                echo "    -> Categoría no mapeada: {$category->value}\n";
                continue 2;
        }
        
        if ($childModel && $childClass) {
            echo "    -> Entidad creada: {$childClass} (id: {$childModel->id})\n";
            
            // Reasignar el documento a la entidad hija
            $doc->documentable_type = $childClass;
            $doc->documentable_id = $childModel->id;
            $doc->save();
            echo "    -> Documento reasignado a entidad hija ✓\n";
        }
    }
}

echo "\n\n=== RESUMEN ===\n";
foreach ($equipos as $equipo) {
    echo "\n{$equipo->name}:\n";
    echo "  Documentos genéricos (Equipment): " . $equipo->documents()->count() . "\n";
    echo "  dataSheets: " . $equipo->dataSheets()->count() . " (con " . EquipmentDataSheet::where('equipment_id', $equipo->id)->with('documents')->get()->sum(fn($d) => $d->documents->count()) . " docs)\n";
    echo "  blueprints: " . $equipo->blueprints()->count() . "\n";
    echo "  catalogs: " . $equipo->catalogs()->count() . "\n";
    echo "  technicalSpecifications: " . $equipo->technicalSpecifications()->count() . "\n";
    echo "  standards: " . $equipo->standards()->count() . "\n";
    echo "  reports: " . $equipo->reports()->count() . "\n";
    echo "  fieldQueries: " . $equipo->fieldQueries()->count() . "\n";
}
