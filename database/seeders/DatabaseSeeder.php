<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\StateSeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\SupplierSeeder;
use Database\Seeders\EquipmentSeeder;
use Database\Seeders\PartSeeder;
use Database\Seeders\PersonSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\ActivitySeeder;
use Database\Seeders\DocumentSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            PartSeeder::class,
            EquipmentSeeder::class,
            SupplierSeeder::class,
            ProjectSeeder::class,
            ActivitySeeder::class,
            PersonSeeder::class,
            DocumentSeeder::class,
            FileSeeder::class,
            RoleSeeder::class
        ]);
    }
}
