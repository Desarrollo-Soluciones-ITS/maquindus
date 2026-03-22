<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Part;
use App\Models\Equipment;
use App\Models\Supplier;

class PartSeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
            [
                'name' => 'Filtro principal',
                'about' => 'Filtro de aceite',
                'details' => ['Material' => 'Acero', 'Diámetro' => '50mm'],
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Bomba hidráulica',
                'about' => 'Bomba de transferencia',
                'details' => ['Capacidad' => '120L/min', 'Potencia' => '200kw'],
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        foreach ($parts as $p) {
            $part = Part::create($p);

            // Asociar a equipment si existe
            $equipment = Equipment::first();
            if ($equipment) {
                $part->equipment()->syncWithoutDetaching([$equipment->id]);
            }

            // Asociar a supplier si existe
            $supplier = Supplier::first();
            if ($supplier) {
                $supplier->parts()->syncWithoutDetaching([$part->id]);
            }
        }
    }
}