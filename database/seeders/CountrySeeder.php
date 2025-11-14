<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['id' => Str::uuid(), 'name' => 'China'],
            ['id' => Str::uuid(), 'name' => 'Estados Unidos'],
            ['id' => Str::uuid(), 'name' => 'Venezuela'],
        ];

        Country::insert($countries);
    }
}
