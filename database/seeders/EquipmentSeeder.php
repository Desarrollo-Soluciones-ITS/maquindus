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
            ['name' => 'Compresor Atlas', 'code' => Code::full('CAT', Prefix::Equipment), 'about' => 'Compresor centrífugo', 'details' => ['Potencia' => '50HP', 'Capacidad' => '100T'], 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Generador Perkins', 'code' => Code::full('GPK', Prefix::Equipment), 'about' => 'Generador diésel', 'details' => ['Potencia' => '200kW', 'Capacidad' => '200T'], 'created_at' => now(), 'updated_at' => now()],
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
