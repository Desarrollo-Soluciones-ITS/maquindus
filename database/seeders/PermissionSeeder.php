<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionDefinitions = Permission::buildDefinitions();

        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $permissions = [];

        foreach ($permissionDefinitions as $def) {
            $permission = Permission::updateOrCreate([
                'slug' => $def['slug'],
            ], [
                'name' => $def['name'],
            ]);

            array_push($permissions, $permission);
        }

        if ($admin) {
            $admin->permissions()->sync(collect($permissions)->pluck('id')->all());
        }
    }
}
