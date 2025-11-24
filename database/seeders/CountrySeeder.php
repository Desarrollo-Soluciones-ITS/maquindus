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
        $date = now()->subDay();
        $countries = [
            ['name' => 'Venezuela', 'created_at' => $date, 'updated_at' => $date],
            ['name' => 'China'],
            ['name' => 'Estados Unidos'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
