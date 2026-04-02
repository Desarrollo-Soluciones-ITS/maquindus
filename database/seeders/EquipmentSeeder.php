<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Part;
use App\Models\Supplier;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $equipment = [
            ['name' => 'Compresor Atlas GA-75', 'model' => 'ATLASGA7501', 'serial' => 'ATLGA7500001', 'type' => 'COMPRESOR75', 'manufacturing_date' => '2019-06-15', 'about' => 'Compresor centrífugo para línea principal de aire industrial.'],
            ['name' => 'Generador Perkins 400', 'model' => 'PERK400GEN2', 'serial' => 'PRK400GEN002', 'type' => 'GENERADOR40', 'manufacturing_date' => '2020-03-20', 'about' => 'Generador diésel de respaldo para operación de planta.'],
            ['name' => 'Bomba Sulzer APT', 'model' => 'SULZERAPT03', 'serial' => 'SLZAPT000003', 'type' => 'BOMBAAPT300', 'manufacturing_date' => '2021-11-08', 'about' => 'Bomba industrial para recirculación y transferencia de fluidos.'],
        ];

        foreach ($equipment as $e) {
            $equipment = Equipment::updateOrCreate(
                ['name' => $e['name']],
                $e,
            );

            $partIds = Part::query()->limit(2)->pluck('id')->all();
            if ($partIds !== []) {
                $equipment->parts()->syncWithoutDetaching($partIds);
            }

            $supplierIds = Supplier::query()->limit(2)->pluck('id')->all();
            if ($supplierIds !== []) {
                $equipment->suppliers()->syncWithoutDetaching($supplierIds);
            }
        }
    }
}
