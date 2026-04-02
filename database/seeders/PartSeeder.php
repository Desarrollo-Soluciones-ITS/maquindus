<?php

namespace Database\Seeders;

use App\Enums\Prefix;
use App\Services\Code;
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
            $part = Part::updateOrCreate(
                ['code' => $p['code']],
                $p,
            );

            $equipmentIds = Equipment::query()->limit(2)->pluck('id')->all();
            if ($equipmentIds !== []) {
                $part->equipment()->syncWithoutDetaching($equipmentIds);
            }

            $supplierIds = Supplier::query()->limit(2)->pluck('id')->all();
            if ($supplierIds !== []) {
                $part->suppliers()->syncWithoutDetaching($supplierIds);
            }
        }
    }
}