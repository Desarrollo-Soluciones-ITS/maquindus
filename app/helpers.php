<?php

use App\Models\Activity;
use App\Models\City;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Equipment;
use App\Models\File;
use App\Models\Part;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Project;
use App\Models\Role;
use App\Models\State;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * @param string $mime El tipo MIME completo del archivo (ej: 'image/png').
 * @return string Una etiqueta de texto amigable (ej: 'PNG', 'Word', 'Archivo').
 */
if (! function_exists('mime_type')) {
    function mime_type(string $mime): string
    {
        return match ($mime) {
            'application/pdf' => 'PDF',
            'image/jpeg' => 'Imagen',
            'image/jpg' => 'Imagen',
            'image/png' => 'Imagen',
            'image/webp' => 'Imagen',
            'image/gif' => 'GIF',
            'application/msword' => 'Word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word',
            'application/vnd.ms-excel' => 'Excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel',
            'application/vnd.ms-powerpoint' => 'PowerPoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PowerPoint',
            'application/zip' => 'ZIP',
            'text/plain' => 'Texto',
            'text/csv' => 'CSV',
            'application/json' => 'JSON',
            'video/mp4' => 'MP4',
            'audio/mpeg' => 'MP3',
            'application/acad' => 'AutoCAD',
            'image/vnd.dwg' => 'AutoCAD',
            'image/x-dwg' => 'AutoCAD',
            'application/dwg' => 'AutoCAD',
            'application/x-autocad' => 'AutoCAD',
            'application/x-dwg' => 'AutoCAD',
            default => 'Archivo',
        };
    }
}

if (! function_exists('model_to_spanish')) {
    function model_to_spanish(string $model, $plural = false)
    {
        $spanish = match ($model) {
            Activity::class => 'Actividad',
            City::class => 'Ciudad',
            Customer::class => 'Cliente',
            Document::class => 'Documento',
            Equipment::class => 'Equipo',
            File::class => 'Archivo',
            Part::class => 'Repuesto',
            Permission::class => 'Permiso',
            Person::class => 'Contacto',
            Project::class => 'Proyecto',
            Role::class => 'Rol',
            State::class => 'Estado',
            Supplier::class => 'Proveedor',
            User::class => 'Usuario',
        };

        if (!$spanish) return null;
        if (!$plural) return $spanish;

        $str = str($spanish);
        $last = $str->charAt($str->length() - 1);
        $suffix = $last === 'd' || $last === 'r' || $last === 'l' ? 'es' : 's';
        return $str->append($suffix);
    }
}

if (! function_exists('is_local')) {
    function is_not_localhost()
    {
        return collect(['127.0.0.1', '::1'])
            ->doesntContain(request()->ip());
    }
}

if (! function_exists('path')) {
    function path(string $path, $asFolder = false)
    {
        $segments = str($path)
            ->explode('/');

        if ($asFolder) {
            $segments->pop();
        }

        $folder = $segments->join('\\');

        if ($asFolder && Storage::directoryMissing($folder)) {
            throw new Error('path() helper error: directory is missing');
        }

        if (!$asFolder && Storage::fileMissing($folder)) {
            throw new Error('path() helper error: file is missing');
        }

        return str(Storage::path($folder))
            ->replace('/', DIRECTORY_SEPARATOR)
            ->replace('\\', DIRECTORY_SEPARATOR);
    }
}

if (! function_exists('translate_activity_verb')) {
    function translate_activity_verb(string $eventName): string
    {
        return match ($eventName) {
            // Eventos CRUD
            'created' => 'creado',
            'updated' => 'actualizado',
            'deleted' => 'eliminado',
            // Eventos de Autenticación
            'authenticated' => 'inició sesión',
            'logged_out' => 'cerró sesión',
            'login_failed' => 'falló el inicio de sesión',
            default => $eventName,
        };
    }
}

if (! function_exists('translate_activity_event')) {
    function translate_activity_event(string $eventName): string
    {
        return match ($eventName) {
            // Eventos CRUD
            'created' => 'Creación',
            'updated' => 'Actualización',
            'deleted' => 'Eliminación',
            // Eventos de Autenticación
            'authenticated' => 'Inicio de Sesión',
            'logged_out' => 'Cierre de Sesión',
            'login_failed' => 'Fallo de Inicio de Sesión',
            default => $eventName,
        };
    }
}

if (! function_exists('get_activity_color')) {
    function get_activity_color(string $eventName): string
    {
        return match ($eventName) {
            // Eventos CRUD
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            // Eventos de Autenticación
            'authenticated' => 'success',
            'logged_out' => 'info',
            'login_failed' => 'danger',
            default => 'secondary',
        };
    }
}
