<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Part;
use App\Models\Supplier;

use function Illuminate\Log\log;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $equipment = [
            ['name' => 'Compresor Atlas', 'code' => 'EQ-AT-001', 'about' => 'Compresor centrífugo', 'details' => json_encode(['power' => '50HP', 'model' => 'AT-500']), 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Generador Perkins', 'code' => 'EQ-GP-002', 'about' => 'Generador diésel', 'details' => json_encode(['power' => '200kW', 'model' => 'GP-200']), 'created_at' => now(), 'updated_at' => now()],
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
