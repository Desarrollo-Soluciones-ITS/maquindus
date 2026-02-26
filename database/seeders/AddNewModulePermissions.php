<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class AddNewModulePermissions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(([
            'name' => 'Actualizar contraseña',
            'slug' => 'users.update_password'
        ]));
    }
}
