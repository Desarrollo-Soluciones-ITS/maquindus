<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'Administrador']);

        DB::table('users')->where('email', 'admin@example.com')->update([
            'role_id' => $admin->id,
        ]);

        $operator = Role::firstOrCreate(['name' => 'Operador']);

        DB::table('users')->where('email', 'operator@example.com')->update([
            'role_id' => $operator->id,
        ]);

        $user = Role::firstOrCreate(['name' => 'Usuario']);

        DB::table('users')->where('email', 'test@example.com')->update([
            'role_id' => $user->id,
        ]);

        $this->grantFilePermissions([$admin, $operator, $user]);
    }

    /**
     * @param array<int, Role> $roles
     */
    private function grantFilePermissions(array $roles): void
    {
        $permissionSlugs = [
            'documents.show_file',
            'documents.open_in_folder',
            'files.show_file',
            'files.open_in_folder',
        ];

        $permissionIds = DB::table('permissions')
            ->whereIn('slug', $permissionSlugs)
            ->pluck('id')
            ->all();

        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
