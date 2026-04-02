<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'Administrador']);

        User::where('email', 'admin@example.com')->update([
            'role_id' => $admin->id,
        ]);

        $operator = Role::firstOrCreate(['name' => 'Operador']);

        User::where('email', 'operator@example.com')->update([
            'role_id' => $operator->id,
        ]);

        $user = Role::firstOrCreate(['name' => 'Usuario']);

        User::where('email', 'test@example.com')->update([
            'role_id' => $user->id,
        ]);
    }
}
