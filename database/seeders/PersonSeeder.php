<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Person;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\State;
use App\Models\City;

class PersonSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::first();
        $supplier = Supplier::first();
        $state = State::first();
        $city = City::first();

        $people = [
            ['id' => (string) Str::uuid(), 'name' => 'Carlos', 'surname' => 'Pérez', 'email' => 'carlos.perez@vega.com', 'phone' => '04141234567', 'address' => 'Oficina 12', 'position' => 'Gerente de Planta', 'personable_type' => 'App\\Models\\Customer', 'personable_id' => $customer->id, 'state_id' => $state->id, 'city_id' => $city->id],
            ['id' => (string) Str::uuid(), 'name' => 'María', 'surname' => 'Gómez', 'email' => 'maria.gomez@suministros.com', 'phone' => '04149876543', 'address' => 'Sucursal', 'position' => 'Vendedora', 'personable_type' => 'App\\Models\\Supplier', 'personable_id' => $supplier->id, 'state_id' => $state->id, 'city_id' => $city->id],
        ];

        foreach ($people as $p) {
            Person::create($p);
        }
    }
}
