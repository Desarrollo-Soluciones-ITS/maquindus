<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\State;
use App\Models\City;
use App\Models\Country;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $venezuela = Country::venezuela();

        $caracas = State::where('name', 'Distrito Capital')->first();
        $miranda = State::where('name', 'Miranda')->first();

        $libertador = City::where('name', 'Caracas (Centro)')->first();
        $petare = City::where('name', 'Sucre (Petare)')->first();

        $customers = [
            [
                'rif' => 'J-12345678-1',
                'name' => 'Industrias Vega SA',
                'email' => 'contacto@vega.com',
                'phone' => '02121234567',
                'about' => 'Cliente industrial dedicado a manufactura',
                'address' => 'Av. Principal, Centro',
                'country_id' => $venezuela->id,
                'state_id' => $caracas->id,
                'city_id' => $libertador->id,
            ],
            [
                'rif' => 'J-87654321-0',
                'name' => 'Construcciones López CA',
                'email' => 'info@clopez.com',
                'phone' => '02129876543',
                'about' => 'Empresa constructora',
                'address' => 'Urbanización El Paraíso',
                'country_id' => $venezuela->id,
                'state_id' => $miranda->id,
                'city_id' => $petare->id,
            ],
        ];

        foreach ($customers as $c) {
            Customer::create($c);
        }
    }
}
