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
        'delete' => 'Eliminar',
        'view' => 'Listar',
        'download' => 'Descargar',
        'upload' => 'Subir',
        'open_in_folder' => 'Abrir en carpeta',
        'show_file' => 'Ver archivo',
        'sync' => 'Vincular',
        'unsync' => 'Desvincular',
        'gallery' => 'Galería',
    ];

    protected array $resourceLabels = [
        'equipments' => 'equipo',
        'parts' => 'pieza',
        'projects' => 'proyecto',
        'documents' => 'documento',
        'suppliers' => 'proveedor',
        'customers' => 'cliente',
        'people' => 'persona',
        'users' => 'usuario',
        'activities' => 'actividad',
        'files' => 'archivo',
        'images' => 'imágen',
        'activity_logs' => 'registros',
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
            'equipments' => [
                'create',
                'edit',
                'show',
                'delete',
                'view',
            ],
            'parts' => [
                'create',
                'show',
                'view',
                'delete',
                'edit',
            ],
            'projects' => [
                'create',
                'show',
                'view',
                'delete',
                'edit',
            ],
            'documents' => ['view', 'delete', 'edit', 'show', 'open_in_folder', 'show_file', 'download', 'upload'],
            'suppliers' => [
                'create',
                'show',
                'view',
                'delete',
                'edit',
            ],
            'customers' => [
                'create',
                'show',
                'view',
                'delete',
                'edit',
            ],
            'people' => ['create', 'show', 'view', 'delete', 'edit'],
            'users' => ['create', 'show', 'view', 'delete', 'edit'],
            'activity_logs' => ['create', 'show', 'view', 'delete', 'edit'],
            'relationships' => [
                'parts' => ['create', 'sync', 'unsync', 'edit', 'show'],
                'equipments' => ['create', 'sync', 'unsync', 'edit', 'show'],
                'projects' => ['create', 'edit', 'show'],
                'documents' => ['create', 'delete', 'edit', 'show', 'download', 'open_in_folder', 'show_file'],
                'people' => ['create', 'sync', 'unsync', 'edit', 'show', 'delete'],
                'activities' => ['create', 'edit', 'show', 'delete'],
                'suppliers' => ['create', 'sync', 'unsync', 'edit', 'show', 'delete'],
                'images' => ['download', 'edit', 'show', 'delete', 'create', 'gallery'],
                'files' => ['download', 'show', 'delete', 'create', 'open_in_folder', 'show_file'],
            ]
        ];
    }

    protected function buildPermissionDefinitions(array $tree, string $slugPrefix = '', string $namePrefix = ''): array
    {
        $definitions = [];

        foreach ($tree as $key => $value) {
            if ($key === 'relationships') {
                foreach ($value as $relatedModel => $actions) {
                    $newSlugPrefix = $slugPrefix ? "{$slugPrefix}.relationships.{$relatedModel}" : "relationships.{$relatedModel}";
                    $relatedLabel = $this->resourceLabels[$relatedModel] ?? $relatedModel;
                    $newNamePrefix = $namePrefix ? "{$namePrefix} de {$relatedLabel}" : $relatedLabel;

                    foreach ($actions as $action) {
                        $slug = "{$newSlugPrefix}.{$action}";
                        $actionLabel = $this->actionLabels[$action] ?? ucfirst($action);
                        $name = "{$actionLabel} {$newNamePrefix}";

                        $definitions[] = compact('slug', 'name');
                    }
                }
            } elseif (is_numeric($key) && is_string($value)) {
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
