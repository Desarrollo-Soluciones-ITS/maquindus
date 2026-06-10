<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    use HasFactory, HasUuids, LogsActivity, HasActivityLog;

    public static array $standaloneLabels = [
        'dashboard' => 'Acceso al panel principal',
        'roles' => 'Administrar roles y permisos',
        'search' => 'Usar buscador global',
    ];

    public static array $actionLabels = [
        'create' => 'Crear',
        'read' => 'Ver',
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
        'restore' => 'Restaurar',
        'update_password' => 'Actualizar contraseña',
    ];

    public static array $resourceLabels = [
        'equipments' => 'equipo',
        'parts' => 'repuesto',
        'documents' => 'documento',
        'suppliers' => 'proveedor',
        'people' => 'contacto',
        'users' => 'usuario',
        'activities' => 'actividad',
        'files' => 'versión',
        'activity_logs' => 'bitácora',
        'search' => 'buscador',
        'projects' => 'proyecto',
        'customers' => 'cliente',
        'purchase_orders' => 'órden de compra proveedor',
    ];

    public static array $permissions = [
        'dashboard',
        'roles',
        'search',
        'equipments' => [
            'create',
            'edit',
            'show',
            'delete',
            'view',
            'sync',
            'unsync',
            'restore',
        ],
        'parts' => [
            'create',
            'show',
            'view',
            'delete',
            'edit',
            'sync',
            'unsync',
            'restore',
        ],
        'documents' => [
            'view',
            'delete',
            'edit',
            'show',
            'open_in_folder',
            'show_file',
            'download',
            'create',
            'restore',
        ],
        'suppliers' => [
            'create',
            'show',
            'view',
            'delete',
            'edit',
            'sync',
            'unsync',
            'restore',
        ],
        'people' => [
            'create',
            'show',
            'view',
            'delete',
            'edit',
            'sync',
            'unsync',
            'restore',
        ],
        'users' => [
            'create',
            'show',
            'view',
            'delete',
            'edit',
            'update_password'
        ],
        'activity_logs' => [
            'show',
            'view',
        ],
        'activities' => [
            'create',
            'edit',
            'show',
        ],
        'files' => [
            'download',
            'show',
            'create',
            'open_in_folder',
            'show_file',
        ],
        'projects' => [
            'create',
            'edit',
            'show',
            'delete',
            'view',
            'restore',
        ],
        'customers' => [
            'create',
            'show',
            'view',
            'delete',
            'edit',
            'restore',
        ],
        'purchase_orders' => [
            'read',
            'create',
            'edit',
            'delete',
            'view',
            'restore',
        ],
    ];

    public static function buildDefinitions(): array
    {
        $definitions = [];

        foreach (static::$permissions as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $definitions[] = [
                    'slug' => $value,
                    'name' => static::$standaloneLabels[$value] ?? ucfirst(str_replace('_', ' ', $value)),
                ];

                continue;
            }

            if (!is_string($key) || !is_array($value)) {
                continue;
            }

            $resourceLabel = static::$resourceLabels[$key] ?? $key;

            foreach ($value as $action) {
                if (!is_string($action)) {
                    continue;
                }

                $definitions[] = [
                    'slug' => "{$key}.{$action}",
                    'name' => trim((static::$actionLabels[$action] ?? ucfirst($action)) . ' ' . $resourceLabel),
                ];
            }
        }

        return $definitions;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
