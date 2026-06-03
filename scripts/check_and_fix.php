<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ver equipos (incluyendo eliminados suave)
$equipments = App\Models\Equipment::withTrashed()->get();
echo "=== EQUIPOS (con eliminados) ===\n";
foreach ($equipments as $e) {
    echo "ID: {$e->id} | {$e->name} | deleted: " . ($e->deleted_at ?? '-') . "\n";
}

// Ver si hay equipment con soft delete con nombre copia
echo "\n=== BUSCANDO COPIAS OCULTAS ===\n";
$copiaEquipments = App\Models\Equipment::withTrashed()
    ->where('name', 'LIKE', '%- copia')
    ->get();
foreach ($copiaEquipments as $e) {
    echo "ID: {$e->id} | {$e->name} | deleted: " . ($e->deleted_at ?? 'NO') . "\n";
    if ($e->deleted_at) {
        // Restaurarlo
        $e->restore();
        echo "  -> Restaurado!\n";
    }
}

echo "\n=== EQUIPOS FINALES ===\n";
$equipments = App\Models\Equipment::all();
foreach ($equipments as $e) {
    echo "- {$e->name} (ID: {$e->id})\n";
}
