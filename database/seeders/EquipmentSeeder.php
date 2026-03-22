<?php

namespace Database\Seeders;

use App\Enums\Prefix;
use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Part;
use App\Models\Supplier;
use App\Services\Code;

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
            $equipment = Equipment::create($e);

            // Asociar una parte si existe
            $part = Part::first();
            if ($part) {
                $equipment->parts()->syncWithoutDetaching([$part->id]);
            }

            // Asociar un supplier si existe
            $supplier = Supplier::first();
            if ($supplier) {
                $equipment->suppliers()->syncWithoutDetaching([$supplier->id]);
            }
        }
    }
}