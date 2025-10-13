<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Equipment;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $equipment = [
            ['id' => (string) Str::uuid(), 'name' => 'Compresor Atlas', 'code' => 'EQ-AT-001', 'about' => 'Compresor centrífugo', 'details' => json_encode(['power' => '50HP', 'model' => 'AT-500']), 'created_at' => now(), 'updated_at' => now()],
            ['id' => (string) Str::uuid(), 'name' => 'Generador Perkins', 'code' => 'EQ-GP-002', 'about' => 'Generador diésel', 'details' => json_encode(['power' => '200kW', 'model' => 'GP-200']), 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($equipment as $e) {
            Equipment::create($e);
        }
    }
}
