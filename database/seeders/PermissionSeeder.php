<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionsTree = Permission::$permissions;
        $permissionDefinitions = $this->buildPermissionDefinitions($permissionsTree);

        $admin = Role::first();
        $permissions = [];

        foreach ($permissionDefinitions as $def) {
            $permission = Permission::firstOrCreate([
                'slug' => $def['slug'],
            ], [
                'name' => $def['name'],
            ]);

            array_push($permissions, $permission);
        }

        $admin->permissions()->sync($permissions);
    }

    protected function buildPermissionDefinitions(array $tree, string $slugPrefix = '', string $namePrefix = ''): array
    {
        $definitions = [];

        foreach ($tree as $key => $value) {
            if (is_numeric($key) && is_string($value)) {
                $action = $value;
                $slug = $slugPrefix ? "{$slugPrefix}.{$action}" : $action;
                $actionLabel = Permission::$actionLabels[$action] ?? ucfirst($action);
                $resourceLabel = Permission::$resourceLabels[$slugPrefix] ?? $slugPrefix;
                $name = "{$actionLabel} {$resourceLabel}";

                $definitions[] = compact('slug', 'name');
            } elseif (is_string($key) && is_array($value)) {
                $definitions = array_merge(
                    $definitions,
                    $this->buildPermissionDefinitions($value, $key, $this->resourceLabels[$key] ?? $key)
                );
            }
        }

        return $definitions;
    }
}
