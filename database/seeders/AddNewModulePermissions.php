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
        Permission::create(([
            'name' => 'Actualizar contraseña',
            'slug' => 'users.update_password'
        ]));

        Permission::create(([
            'name' => 'Ver órden de compra',
            'slug' => 'purchase_orders.read'
        ]));
        Permission::create(([
            'name' => 'Crear órden de compra',
            'slug' => 'purchase_orders.create'
        ]));
        Permission::create(([
            'name' => 'Actualizar órden de compra',
            'slug' => 'purchase_orders.delete'
        ]));
        Permission::create(([
            'name' => 'Archivar órden de compra',
            'slug' => 'purchase_orders.edit'
        ]));
        Permission::create(([
            'name' => 'Listar ordenes de compra',
            'slug' => 'purchase_orders.view'
        ]));
        Permission::create(([
            'name' => 'Restaurar órdenes de compra',
            'slug' => 'purchase_orders.restore'
        ]));

        $adminRole = Role::where('name', 'Administrador')->first();
        $purchaseOrderPermissions = Permission::where('slug', 'like', 'purchase_orders.%')->pluck('id')->toArray();
        $adminRole->permissions()->syncWithoutDetaching($purchaseOrderPermissions);
    }
}
