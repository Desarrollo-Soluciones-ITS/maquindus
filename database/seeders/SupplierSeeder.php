<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\State;
use App\Models\City;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $aragua = State::where('name', 'Aragua')->first();
        $maracay = City::where('name', 'Maracay')->first();

        $suppliers = [
            [
                'id' => (string) Str::uuid(),
                'rif' => 'J-10101010-2',
                'name' => 'Suministros Técnicos S.A.',
                'email' => 'ventas@suministros.com',
                'phone' => '02441234567',
                'about' => 'Proveedor de repuestos y equipos',
                'address' => 'Parque Industrial',
                'state_id' => $aragua->id,
                'city_id' => $maracay->id,
            ],
        ];

        foreach ($suppliers as $s) {
            Supplier::create($s);
        }
    }
}
