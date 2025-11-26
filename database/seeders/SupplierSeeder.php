<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\State;
use App\Models\City;
use App\Models\Country;
use App\Models\Equipment;
use App\Models\Part;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $venezuela = Country::venezuela();

        $aragua = State::where('name', 'Aragua')->first();
        $maracay = City::where('name', 'Maracay')->first();

        $suppliers = [
            [
                'rif' => 'J-10101010-2',
                'name' => 'Suministros Técnicos SA',
                'email' => 'ventas@suministros.com',
                'phone' => '02441234567',
                'about' => 'Proveedor de repuestos y equipos',
                'address' => 'Parque Industrial',
                'country_id' => $venezuela->id,
                'state_id' => $aragua->id,
                'city_id' => $maracay->id,
            ],
        ];

        foreach ($suppliers as $s) {
            $supplier = Supplier::create($s);

            // Asociar equipment si existe
            $equipment = Equipment::first();
            if ($equipment) {
                $supplier->equipment()->syncWithoutDetaching([$equipment->id]);
            }

            // Asociar part si existe
            $part = Part::first();
            if ($part) {
                $supplier->parts()->syncWithoutDetaching([$part->id]);
            }
        }
    }
}
