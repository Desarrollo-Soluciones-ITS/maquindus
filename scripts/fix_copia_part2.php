<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Equipment;
use App\Models\Document;
use App\Models\EquipmentCatalog;
use App\Models\EquipmentStandard;

echo "=== SEGUNDA PASADA: DOCUMENTOS SIN CATEGORÍA ===\n\n";

$equipos = Equipment::withTrashed()
    ->where('name', 'like', '%copia%')
    ->get();

foreach ($equipos as $equipo) {
    echo "\n=== {$equipo->name} ===\n";
    
    // Documentos genéricos sin categoría
    $docs = Document::where('documentable_type', Equipment::class)
        ->where('documentable_id', $equipo->id)
        ->whereNull('category')
        ->whereNull('deleted_at')
        ->get();
    
    echo "Documentos sin categoría: " . $docs->count() . "\n";
    
    foreach ($docs as $doc) {
        $name = $doc->name;
        echo "\n  Documento: {$name}\n";
        
        // Detectar categoría desde el nombre del documento
        $childClass = null;
        $childModel = null;
        
        if (stripos($name, 'Catálogo') !== false || stripos($name, 'Catalogo') !== false) {
            $childClass = EquipmentCatalog::class;
            $childModel = EquipmentCatalog::firstOrCreate(
                ['equipment_id' => $equipo->id, 'name' => $name],
                ['document_type' => 'General']
            );
            echo "    -> Detectado como Catálogo\n";
        } elseif (stripos($name, 'Norma') !== false) {
            $childClass = EquipmentStandard::class;
            $childModel = EquipmentStandard::firstOrCreate(
                ['equipment_id' => $equipo->id, 'name' => $name],
                ['revision' => '1']
            );
            echo "    -> Detectado como Norma\n";
        } else {
            echo "    -> No se pudo determinar categoría\n";
            continue;
        }
        
        if ($childModel && $childClass) {
            $doc->documentable_type = $childClass;
            $doc->documentable_id = $childModel->id;
            $doc->category = null; // mantener sin categoría ya que la entidad hija define el tipo
            $doc->save();
            echo "    -> Documento reasignado a {$childClass} ✓\n";
        }
    }
}

echo "\n\n=== RESUMEN FINAL ===\n";
foreach ($equipos as $equipo) {
    echo "\n{$equipo->name}:\n";
    echo "  Genéricos (Equipment): " . Document::where('documentable_type', Equipment::class)->where('documentable_id', $equipo->id)->whereNull('deleted_at')->count() . "\n";
    echo "  dataSheets: " . $equipo->dataSheets()->count() . "\n";
    echo "  blueprints: " . $equipo->blueprints()->count() . " (con docs: " . $equipo->blueprints()->with('documents')->get()->sum(fn($d) => $d->documents->count()) . ")\n";
    echo "  catalogs: " . $equipo->catalogs()->count() . " (con docs: " . $equipo->catalogs()->with('documents')->get()->sum(fn($d) => $d->documents->count()) . ")\n";
    echo "  technicalSpecs: " . $equipo->technicalSpecifications()->count() . " (con docs: " . $equipo->technicalSpecifications()->with('documents')->get()->sum(fn($d) => $d->documents->count()) . ")\n";
    echo "  standards: " . $equipo->standards()->count() . " (con docs: " . $equipo->standards()->with('documents')->get()->sum(fn($d) => $d->documents->count()) . ")\n";
    echo "  reports: " . $equipo->reports()->count() . " (con docs: " . $equipo->reports()->with('documents')->get()->sum(fn($d) => $d->documents->count()) . ")\n";
    echo "  fieldQueries: " . $equipo->fieldQueries()->count() . "\n";
    echo "  Manuales (genéricos): " . Document::where('documentable_type', Equipment::class)->where('documentable_id', $equipo->id)->where('category', 'Manuales')->whereNull('deleted_at')->count() . "\n";
}
