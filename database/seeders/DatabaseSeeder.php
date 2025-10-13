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
            StateSeeder::class,
            CitySeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            SupplierSeeder::class,
            EquipmentSeeder::class,
            PartSeeder::class,
            PersonSeeder::class,
            ProjectSeeder::class,
            ActivitySeeder::class,
            DocumentSeeder::class,
        ]);
    }
}
