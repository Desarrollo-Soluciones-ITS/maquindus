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
            [
                'name' => 'Compresor Atlas',
                'model' => 'CAT-50HP',
                'serial' => 'SN123456',
                'type' => 'Compresor',
                'about' => 'Compresor centrífugo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Generador Perkins',
                'model' => 'GPK-200kW',
                'serial' => 'SN789012',
                'type' => 'Generador',
                'about' => 'Generador diésel',
                'created_at' => now(),
                'updated_at' => now()
            ],
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