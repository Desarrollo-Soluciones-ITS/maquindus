<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
        $this->truncateDatabase();
        $this->clearPrivateStorage();

        $this->call([
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
            UserSeeder::class,
            PermissionSeeder::class,
            AddNewModulePermissions::class,
            RoleSeeder::class,
            SupplierSeeder::class,
            EquipmentSeeder::class,
            PartSeeder::class,
            PersonSeeder::class,
            ActivitySeeder::class,
            PurchaseOrderSeeder::class,
            EquipmentMetadataSeeder::class,
            DocumentSeeder::class,
            FileSeeder::class,
        ]);
    }

    private function truncateDatabase(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            foreach ($this->getTableNames() as $table) {
                if ($table === 'migrations') {
                    continue;
                }

                DB::table($table)->truncate();
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * @return array<int, string>
     */
    private function getTableNames(): array
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))
                ->pluck('name')
                ->values()
                ->all(),
            'mysql', 'mariadb' => collect(DB::select('SHOW TABLES'))
                ->map(fn(object $row) => array_values((array) $row)[0] ?? null)
                ->filter()
                ->values()
                ->all(),
            default => [],
        };
    }

    private function clearPrivateStorage(): void
    {
        $disk = Storage::disk('local');

        $disk->delete(collect($disk->allFiles())
            ->reject(fn(string $path) => $path === '.gitignore')
            ->all());

        foreach (collect($disk->allDirectories())->sortByDesc(fn(string $directory) => substr_count($directory, '/')) as $directory) {
            $disk->deleteDirectory($directory);
        }
    }
}
