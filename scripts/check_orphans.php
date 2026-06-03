<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Equipment;
use App\Models\EquipmentDataSheet;
use App\Models\EquipmentBlueprint;
use App\Models\EquipmentCatalog;
use App\Models\EquipmentTechnicalSpecification;
use App\Models\EquipmentStandard;
use App\Models\EquipmentFieldQuery;
use App\Models\EquipmentReport;
use App\Models\EquipmentSparePart;

echo "=== ENTIDADES HUÉRFANAS (sin equipo asociado) ===\n\n";

// Verificar cada tipo de entidad hija
$checks = [
    'EquipmentDataSheet' => EquipmentDataSheet::class,
    'EquipmentBlueprint' => EquipmentBlueprint::class,
    'EquipmentCatalog' => EquipmentCatalog::class,
    'EquipmentTechnicalSpecification' => EquipmentTechnicalSpecification::class,
    'EquipmentStandard' => EquipmentStandard::class,
    'EquipmentFieldQuery' => EquipmentFieldQuery::class,
    'EquipmentReport' => EquipmentReport::class,
    'EquipmentSparePart' => EquipmentSparePart::class,
];

foreach ($checks as $name => $class) {
    $orphans = $class::whereDoesntHave('equipment')->get();
    echo "{$name}: " . $class::count() . " total, {$orphans->count()} huérfanos\n";
    foreach ($orphans as $o) {
        echo "  - {$o->name} (id: {$o->id})\n";
    }
}

echo "\n\n=== ENTIDADES VÁLIDAS (con equipo asociado) ===\n";
$equipos = Equipment::withTrashed()->get();
foreach ($equipos as $eq) {
    echo "\nEquipo: {$eq->name}\n";
    echo "  dataSheets: " . $eq->dataSheets()->count() . "\n";
    echo "  blueprints: " . $eq->blueprints()->count() . "\n";
    echo "  catalogs: " . $eq->catalogs()->count() . "\n";
    echo "  technicalSpecifications: " . $eq->technicalSpecifications()->count() . "\n";
    echo "  standards: " . $eq->standards()->count() . "\n";
    echo "  fieldQueries: " . $eq->fieldQueries()->count() . "\n";
    echo "  reports: " . $eq->reports()->count() . "\n";
    echo "  spareParts: " . $eq->equipmentSpareParts()->count() . "\n";
}
