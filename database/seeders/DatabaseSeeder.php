<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\StateSeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\SupplierSeeder;
use Database\Seeders\EquipmentSeeder;
use Database\Seeders\PartSeeder;
use Database\Seeders\PersonSeeder;
use Database\Seeders\ActivitySeeder;
use Database\Seeders\DocumentSeeder;
use Database\Seeders\EquipmentMetadataSeeder;
use Database\Seeders\PurchaseOrderSeeder;

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
            RoleSeeder::class,
            FileManagerPermissionsSeeder::class,
            AddNewModulePermissions::class,
            SupplierSeeder::class,
            EquipmentSeeder::class,
            PartSeeder::class,
            PersonSeeder::class,
            ActivitySeeder::class,
            PurchaseOrderSeeder::class,
            EquipmentMetadataSeeder::class,
            DocumentSeeder::class,
            FileSeeder::class,
            FileManagerPermissionsSeeder::class,
        ]);
    }
}
