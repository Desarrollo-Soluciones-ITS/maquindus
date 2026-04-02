<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AddNewModulePermissions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Permission::buildDefinitions() as $definition) {
            Permission::updateOrCreate(
                ['slug' => $definition['slug']],
                ['name' => $definition['name']],
            );
        }

        $adminRole = Role::where('name', 'Administrador')->first();

        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching(Permission::pluck('id')->all());
        }
    }
}
