<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    protected array $actionLabels = [
        'create' => 'Crear',
        'edit' => 'Editar',
        'show' => 'Ver',
        'delete' => 'Archivar',
        'view' => 'Listar',
        'download' => 'Descargar',
        'upload' => 'Subir',
        'open_in_folder' => 'Abrir carpeta de',
        'show_file' => 'Ver archivo',
        'sync' => 'Vincular',
        'unsync' => 'Desvincular',
        'gallery' => 'Galería',
    ];

    protected array $resourceLabels = [
        'equipments' => 'equipo',
        'parts' => 'repuesto',
        'projects' => 'proyecto',
        'documents' => 'documento',
        'suppliers' => 'proveedor',
        'customers' => 'cliente',
        'people' => 'contacto',
        'users' => 'usuario',
        'activities' => 'actividad',
        'files' => 'archivo',
        'images' => 'imagen',
        'activity_logs' => 'bitácora',
    ];

    public function run(): void
    {
        $permissionsTree = $this->getPermissionsTree();
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

    protected function getPermissionsTree(): array
    {
        return [
            'dashboard',
            'roles',
            'equipments' => [
                'create',
                'edit',
                'show',
                'delete',
                'view',
                'sync',
                'unsync'
            ],
            'parts' => [
                'create',
                'show',
                'view',
                'delete',
                'edit',
                'sync',
                'unsync'
            ],
            'projects' => [
                'create',
                'show',
                'view',
                'delete',
                'edit',
            ],
            'documents' => [
                'view',
                'delete',
                'edit',
                'show',
                'open_in_folder',
                'show_file',
                'download',
                'upload',
                'create',
            ],
            'suppliers' => [
                'create',
                'show',
                'view',
                'delete',
                'edit',
                'sync',
                'unsync'
            ],
            'customers' => [
                'create',
                'show',
                'view',
                'delete',
                'edit',
            ],
            'people' => [
                'create',
                'show',
                'view',
                'delete',
                'edit',
                'sync',
                'unsync'
            ],
            'users' => [
                'create',
                'show',
                'view',
                'delete',
                'edit',
            ],
            'activity_logs' => [
                'create',
                'show',
                'view',
                'delete',
                'edit',
            ],
            'activities' => ['create', 'edit', 'show', 'delete', 'sync', 'unsync'],
            'images' => ['download', 'edit', 'show', 'delete', 'create', 'gallery'],
            'files' => ['download', 'show', 'delete', 'create', 'open_in_folder', 'show_file'],
        ];
    }

    protected function buildPermissionDefinitions(array $tree, string $slugPrefix = '', string $namePrefix = ''): array
    {
        $definitions = [];

        foreach ($tree as $key => $value) {
            if (is_numeric($key) && is_string($value)) {
                $action = $value;
                $slug = $slugPrefix ? "{$slugPrefix}.{$action}" : $action;
                $actionLabel = $this->actionLabels[$action] ?? ucfirst($action);
                $resourceLabel = $this->resourceLabels[$slugPrefix] ?? $slugPrefix;
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
