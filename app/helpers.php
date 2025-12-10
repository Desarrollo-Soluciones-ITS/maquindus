<?php

use App\Enums\Prefix;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Activity;
use App\Models\City;
use App\Models\Country;
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
use App\Services\Code;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @param string $mime El tipo MIME completo del archivo (ej: 'image/png').
 * @return string Una etiqueta de texto amigable (ej: 'PNG', 'Word', 'Archivo').
 */
if (!function_exists('mime_type')) {
    function mime_type(string $mime): string
    {
        return match ($mime) {
            'application/pdf' => 'PDF',
            'image/jpeg' => 'Imagen',
            'image/jpg' => 'Imagen',
            'image/png' => 'Imagen',
            'image/webp' => 'Imagen',
            'image/svg+xml' => 'Imagen',
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
            'image/vnd.dxf' => 'AutoCAD',
            'image/x-dwg' => 'AutoCAD',
            'application/dwg' => 'AutoCAD',
            'application/x-autocad' => 'AutoCAD',
            'application/x-dwg' => 'AutoCAD',
            'application/x-solidworks' => 'SolidWorks',
            default => 'Archivo',
        };
    }
}

if (!function_exists('check_solidworks')) {
    function check_solidworks(string $mime, string $path)
    {
        if ($mime !== 'application/vnd.ms-office')
            return $mime;

        $extension = str($path)
            ->lower()->explode('.')->last();

        $contains = collect(['sldprt', 'sldasm', 'slddrw', 'slddrt'])
            ->contains($extension);

        if (!$contains)
            return $mime;
        return 'application/x-solidworks';
    }
}

if (!function_exists('model_to_spanish')) {
    function model_to_spanish(string $model, $plural = false)
    {
        $spanish = match ($model) {
            Activity::class => 'Actividad',
            City::class => 'Ciudad',
            Country::class => 'País',
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

        if (!$spanish)
            return null;
        if (!$plural)
            return $spanish;

        $str = str($spanish);
        $last = $str->charAt($str->length() - 1);
        $suffix = $last === 'd' || $last === 'r' || $last === 'l' ? 'es' : 's';
        return $str->append($suffix);
    }
}

if (!function_exists('is_local')) {
    function is_not_localhost()
    {
        return collect(['127.0.0.1', '::1'])
            ->doesntContain(request()->ip());
    }
}

if (!function_exists('path')) {
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

if (!function_exists('translate_activity_verb')) {
    function translate_activity_verb(string $eventName): string
    {
        return match ($eventName) {
            // Eventos CRUD
            'created' => 'creado',
            'updated' => 'actualizado',
            'deleted' => 'archivado',
            'restored' => 'desarchivado',
            // Eventos de Autenticación
            'authenticated' => 'inició sesión',
            'logged_out' => 'cerró sesión',
            'login_failed' => 'falló el inicio de sesión',
            default => $eventName,
        };
    }
}

if (!function_exists('translate_activity_event')) {
    function translate_activity_event(string $eventName): string
    {
        return match ($eventName) {
            // Eventos CRUD
            'created' => 'Creación',
            'updated' => 'Actualización',
            'deleted' => 'Archivado',
            'restored' => 'Desarchivado',
            // Eventos de Autenticación
            'authenticated' => 'Inicio de Sesión',
            'logged_out' => 'Cierre de Sesión',
            'login_failed' => 'Fallo de Inicio de Sesión',
            default => $eventName,
        };
    }
}

if (!function_exists('get_activity_color')) {
    function get_activity_color(string $eventName): string
    {
        return match ($eventName) {
            // Eventos CRUD
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'success',
            // Eventos de Autenticación
            'authenticated' => 'success',
            'logged_out' => 'info',
            'login_failed' => 'danger',
            default => 'secondary',
        };
    }
}

if (!function_exists('hasPermission')) {
    function currentUserHasPermission(string $permission)
    {
        return auth()->user()->hasPermission($permission);
    }
}

if (!function_exists('is_relation_manager')) {
    function is_not_relation_manager()
    {
        return fn($livewire) => !($livewire instanceof RelationManager);
    }
}

if (!function_exists('code_to_full')) {
    function code_to_full(Prefix $prefix)
    {
        return function ($data) use ($prefix) {
            $data['code'] = Code::full($data['code'], $prefix);
            return $data;
        };
    }
}

if (!function_exists('is_view_customer')) {
    function is_view_customer()
    {
        return function (RelationManager|ListProjects $livewire) {
            if ($livewire instanceof ListProjects)
                return false;
            return $livewire->getPageClass() === ViewCustomer::class;
        };
    }
}

if (!function_exists('documentables')) {
    function documentables()
    {
        return collect([
            Project::class,
            Equipment::class,
            Person::class,
            Customer::class,
            Part::class,
            Supplier::class,
        ]);
    }
}

if (!function_exists('cleanup_empty_folders')) {
    function cleanup_empty_folders(string $folderPath): void
    {
        $disk = Storage::disk('local');

        $deleteIfEmpty = function ($path) use ($disk, &$deleteIfEmpty) {
            if (!$disk->exists($path)) {
                return;
            }

            $contents = $disk->files($path);
            $directories = $disk->directories($path);

            if (count($contents) === 0 && count($directories) === 0) {
                $disk->deleteDirectory($path);

                $parentPath = dirname($path);
                if ($parentPath !== '.' && $parentPath !== '') {
                    $deleteIfEmpty($parentPath);
                }
            }
        };

        $deleteIfEmpty($folderPath);
    }
}

if (!function_exists('handle_documentable_name_change')) {
    function handle_documentable_name_change(Model $documentable, string $oldName, string $newName): void
    {
        $disk = Storage::disk('local');
        $parent = model_to_spanish($documentable::class, plural: true);

        $oldBaseFolder = $parent . '/' . $oldName;
        $newBaseFolder = $parent . '/' . $newName;

        if ($oldBaseFolder !== $newBaseFolder && $disk->exists($oldBaseFolder)) {
            if (!$disk->exists($newBaseFolder)) {
                $disk->makeDirectory($newBaseFolder);
            }

            $allFiles = $disk->allFiles($oldBaseFolder);
            foreach ($allFiles as $oldPath) {
                $newPath = str_replace($oldBaseFolder, $newBaseFolder, $oldPath);

                $newDirectory = dirname($newPath);
                if (!$disk->exists($newDirectory)) {
                    $disk->makeDirectory($newDirectory);
                }

                $disk->move($oldPath, $newPath);
            }

            $documentable->documents->each(function ($document) use ($oldBaseFolder, $newBaseFolder) {
                $document->files->each(function ($file) use ($oldBaseFolder, $newBaseFolder) {
                    $oldPath = $file->path;
                    $newPath = str_replace($oldBaseFolder, $newBaseFolder, $oldPath);

                    if ($oldPath !== $newPath) {
                        $file->update(['path' => $newPath]);
                    }
                });
            });

            $allDirectories = $disk->allDirectories($oldBaseFolder);

            foreach (array_reverse($allDirectories) as $directory) {
                cleanup_empty_folders($directory);
            }

            cleanup_empty_folders($oldBaseFolder);
        }
    }
}


if (!function_exists('key_value_trimmer')) {
    function key_value_trimmer()
    {
        return function ($state) {
            $trimmed = [];

            foreach ($state as $key => $value) {
                $trim = fn($str) => is_string($str) ? trim($str) : $str;
                $trimmed[$trim($key)] = $trim($value);
            }

            return $trimmed;
        };
    }
}
