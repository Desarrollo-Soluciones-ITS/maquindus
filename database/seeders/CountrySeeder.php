<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'Venezuela'],
            ['name' => 'China'],
            ['name' => 'Estados Unidos'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
