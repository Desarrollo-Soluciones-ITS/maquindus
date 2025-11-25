<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Permission extends Model
{
    use HasUuids, LogsActivity, HasActivityLog;

    public static array $actionLabels = [
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
        'restore' => 'Restaurar',
    ];

    public static array $resourceLabels = [
        'equipments' => 'equipo',
        'parts' => 'repuesto',
        'projects' => 'proyecto',
        'documents' => 'documento',
        'suppliers' => 'proveedor',
        'customers' => 'cliente',
        'people' => 'contacto',
        'users' => 'usuario',
        'activities' => 'actividad',
        'files' => 'versión',
        'activity_logs' => 'bitácora',
    ];

    public static array $permissions = [
        'dashboard',
        'roles',
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
        'projects' => [
            'create',
            'show',
            'view',
            'delete',
            'edit',
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
        'customers' => [
            'create',
            'show',
            'view',
            'delete',
            'edit',
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
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
