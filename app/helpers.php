<?php

use App\Enums\Prefix;
use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Parts\Pages\ViewPart;
use App\Filament\Resources\People\Pages\ViewPerson;
use App\Filament\Resources\Suppliers\Pages\ViewSupplier;
use App\Models\Activity;
use App\Models\City;
use App\Models\Country;
use App\Models\Document;
use App\Models\Equipment;
use App\Models\EquipmentBlueprint;
use App\Models\EquipmentCatalog;
use App\Models\EquipmentDataSheet;
use App\Models\EquipmentFieldQuery;
use App\Models\EquipmentReport;
use App\Models\EquipmentSparePart;
use App\Models\EquipmentStandard;
use App\Models\EquipmentTechnicalSpecification;
use App\Models\File;
use App\Models\Part;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\State;
use App\Models\Supplier;
use App\Models\SupplierPurchaseOrder;
use App\Models\User;
use App\Services\Code;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $extension = str($path)->lower()->explode('.')->last();
        $contains = collect(['sldprt', 'sldasm', 'slddrw', 'slddrt'])->contains($extension);
        if (!$contains)
            return $mime;
        return 'application/x-solidworks';
    }
}

if (!function_exists('model_to_spanish')) {
    function model_to_spanish(string $model, $plural = false)
    {
        $singularMap = [
            Activity::class => 'Actividad',
            City::class => 'Ciudad',
            Country::class => 'País',
            Document::class => 'Documento',
            Equipment::class => 'Equipo',
            EquipmentBlueprint::class => 'Plano',
            EquipmentCatalog::class => 'Catálogo',
            EquipmentDataSheet::class => 'Hoja de datos',
            EquipmentFieldQuery::class => 'Consulta de campo',
            EquipmentReport::class => 'Reporte',
            EquipmentSparePart::class => 'Repuesto',
            EquipmentStandard::class => 'Norma',
            EquipmentTechnicalSpecification::class => 'Especificación técnica',
            File::class => 'Archivo',
            Part::class => 'Repuesto',
            Permission::class => 'Permiso',
            Person::class => 'Contacto',
            SupplierPurchaseOrder::class => 'Orden de compra proveedor',
            Role::class => 'Rol',
            State::class => 'Estado',
            Supplier::class => 'Proveedor',
            User::class => 'Usuario',
        ];
        $pluralMap = [
            Activity::class => 'Actividades',
            City::class => 'Ciudades',
            Country::class => 'Países',
            Document::class => 'Documentos',
            Equipment::class => 'Equipos',
            EquipmentBlueprint::class => 'Planos',
            EquipmentCatalog::class => 'Catálogos',
            EquipmentDataSheet::class => 'Hojas de datos',
            EquipmentFieldQuery::class => 'Consultas de campo',
            EquipmentReport::class => 'Reportes',
            EquipmentSparePart::class => 'Repuestos',
            EquipmentStandard::class => 'Normas',
            EquipmentTechnicalSpecification::class => 'Especificaciones técnicas',
            File::class => 'Archivos',
            Part::class => 'Repuestos',
            Permission::class => 'Permisos',
            Person::class => 'Contactos',
            SupplierPurchaseOrder::class => 'Órdenes de compra proveedor',
            Role::class => 'Roles',
            State::class => 'Estados',
            Supplier::class => 'Proveedores',
            User::class => 'Usuarios',
        ];
        $spanish = $plural ? ($pluralMap[$model] ?? null) : ($singularMap[$model] ?? null);
        if (!$spanish) return null;
        return $spanish;
    }
}

if (!function_exists('documentable_name_column')) {
    function documentable_name_column(string $model): string
    {
        return match ($model) {
            EquipmentDataSheet::class => 'sheet_number',
            EquipmentBlueprint::class => 'name',
            EquipmentCatalog::class => 'name',
            EquipmentTechnicalSpecification::class => 'revision_name',
            EquipmentStandard::class => 'name',
            EquipmentFieldQuery::class => 'document_name',
            EquipmentSparePart::class => 'part_number',
            EquipmentReport::class => 'document_name',
            default => 'name',
        };
    }
}

if (!function_exists('path')) {
    function path(string $path, $asFolder = false, $base = true)
    {
        $segments = str($path)->explode('/');
        if ($asFolder) $segments->pop();
        $folder = $segments->join('\\');
        if ($asFolder && Storage::directoryMissing($folder)) throw new Error('path() helper error: directory is missing');
        if (!$asFolder && Storage::fileMissing($folder)) throw new Error('path() helper error: file is missing');
        return str($base ? Storage::path($folder) : $folder)->replace('/', DIRECTORY_SEPARATOR)->replace('\\', DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('translate_activity_verb')) {
    function translate_activity_verb(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'creado', 'updated' => 'actualizado', 'deleted' => 'archivado', 'restored' => 'desarchivado',
            'authenticated' => 'inició sesión', 'logged_out' => 'cerró sesión', 'login_failed' => 'falló el inicio de sesión',
            'code_updated' => 'código actualizado', default => $eventName,
        };
    }
}

if (!function_exists('translate_activity_event')) {
    function translate_activity_event(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'Creación', 'updated' => 'Actualización', 'deleted' => 'Archivado', 'restored' => 'Desarchivado',
            'authenticated' => 'Inicio de Sesión', 'logged_out' => 'Cierre de Sesión', 'login_failed' => 'Fallo de Inicio de Sesión',
            'code_updated' => 'Código Actualizado', default => $eventName,
        };
    }
}

if (!function_exists('get_activity_color')) {
    function get_activity_color(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', 'restored' => 'success',
            'authenticated' => 'success', 'logged_out' => 'info', 'login_failed' => 'danger', 'code_updated' => 'warning',
            default => 'secondary',
        };
    }
}

if (!function_exists('hasPermission')) {
    function currentUserHasPermission(string $permission) { return Auth::user()?->hasPermission($permission) ?? false; }
}

if (!function_exists('currentUserHasAnyPermission')) {
    function currentUserHasAnyPermission(array|string $permissions): bool
    {
        foreach ((array) $permissions as $permission) { if (currentUserHasPermission($permission)) return true; }
        return false;
    }
}

if (!function_exists('is_relation_manager')) {
    function is_not_relation_manager() { return fn($livewire) => !($livewire instanceof RelationManager); }
}

if (!function_exists('code_to_full')) {
    function code_to_full(Prefix $prefix)
    {
        return function ($data) use ($prefix) {
            $value = $data['code'] ?? null;
            if (!filled($value)) { $data['code'] = null; return $data; }
            $data['code'] = Code::full($value, $prefix);
            return $data;
        };
    }
}

if (!function_exists('relation_manager_owner_is_equipment')) {
    function relation_manager_owner_is_equipment(RelationManager $livewire): bool
    {
        return method_exists($livewire, 'getOwnerRecord') && $livewire->getOwnerRecord() instanceof Equipment;
    }
}

if (!function_exists('managed_from_equipment')) {
    function managed_from_equipment(mixed $livewire): bool
    {
        return $livewire instanceof RelationManager && relation_manager_owner_is_equipment($livewire);
    }
}

if (!function_exists('documentables')) {
    function documentables()
    {
        return collect([
            Equipment::class, Person::class, Part::class, Supplier::class,
            EquipmentDataSheet::class, EquipmentBlueprint::class, EquipmentCatalog::class,
            EquipmentTechnicalSpecification::class, EquipmentStandard::class,
            EquipmentFieldQuery::class, EquipmentSparePart::class, EquipmentReport::class,
        ]);
    }
}

if (!function_exists('documentable_view_url')) {
    function documentable_view_url(?Model $documentable): ?string
    {
        if (!$documentable) return null;
        return match ($documentable::class) {
            Part::class => ViewPart::getUrl(['record' => $documentable->id]),
            Person::class => ViewPerson::getUrl(['record' => $documentable->id]),
            Supplier::class => ViewSupplier::getUrl(['record' => $documentable->id]),
            Equipment::class => ViewEquipment::getUrl(['record' => $documentable->id]),
            EquipmentDataSheet::class, EquipmentBlueprint::class, EquipmentCatalog::class,
            EquipmentTechnicalSpecification::class, EquipmentStandard::class,
            EquipmentFieldQuery::class, EquipmentSparePart::class, EquipmentReport::class
                => ViewEquipment::getUrl(['record' => $documentable->equipment_id]),
            default => null,
        };
    }
}

if (!function_exists('cleanup_empty_folders')) {
    function cleanup_empty_folders(string $folderPath): void
    {
        $disk = Storage::disk('local');
        $deleteIfEmpty = function ($path) use ($disk, &$deleteIfEmpty) {
            if (!$disk->exists($path)) return;
            $contents = $disk->files($path);
            $directories = $disk->directories($path);
            if (count($contents) === 0 && count($directories) === 0) {
                $disk->deleteDirectory($path);
                $parentPath = dirname($path);
                if ($parentPath !== '.' && $parentPath !== '') $deleteIfEmpty($parentPath);
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
            if (!$disk->exists($newBaseFolder)) $disk->makeDirectory($newBaseFolder);
            $allFiles = $disk->allFiles($oldBaseFolder);
            foreach ($allFiles as $oldPath) {
                $newPath = str_replace($oldBaseFolder, $newBaseFolder, $oldPath);
                $newDirectory = dirname($newPath);
                if (!$disk->exists($newDirectory)) $disk->makeDirectory($newDirectory);
                $disk->move($oldPath, $newPath);
            }
            $documentable->documents->each(function ($document) use ($oldBaseFolder, $newBaseFolder) {
                $document->files->each(function ($file) use ($oldBaseFolder, $newBaseFolder) {
                    $oldPath = $file->path;
                    $newPath = str_replace($oldBaseFolder, $newBaseFolder, $oldPath);
                    if ($oldPath !== $newPath) $file->update(['path' => $newPath]);
                });
            });
            $allDirectories = $disk->allDirectories($oldBaseFolder);
            foreach (array_reverse($allDirectories) as $directory) cleanup_empty_folders($directory);
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

if (!function_exists('exec_url')) {
    function exec_url(string $filepath, string $endpoint)
    {
        $base = env('SHELL_API_URL', 'http://127.0.0.1:8970');
        try {
            // Enviar la ruta absoluta completa del sistema de archivos
            $replaced = path($filepath, base: true);
        } catch (\Throwable) {
            return null;
        }
        $path = urlencode($replaced);
        return "$base/$endpoint.php?path=$path";
    }
}

if (!function_exists('gestor_net_url')) {
    function gestor_net_url(string $filepath, string $action = 'select'): ?string
    {
        $base = env('STORAGE_NETWORK_PATH');
        if (!$base) return null;

        try {
            $relativePath = path($filepath, base: false);
        } catch (\Throwable) {
            return null;
        }
        $relativePath = ltrim($relativePath, '\\/');

        $fullPath = rtrim($base, '\\/') . '\\' . str_replace('/', '\\', $relativePath);

        return "gestor://$action?path=" . urlencode($fullPath);
    }
}

if (!function_exists('record_folder_url')) {
    function record_folder_url(Model $record): ?string
    {
        if ($record instanceof File) {
            return filled($record->path) ? exec_url($record->path, endpoint: 'folder') : null;
        }
        if ($record instanceof Document) {
            return filled($record->current?->path) ? exec_url($record->current->path, endpoint: 'folder') : null;
        }
        if (method_exists($record, 'documents')) {
            $document = $record->documents()->with('current')->latest('created_at')->first();
            return filled($document?->current?->path) ? exec_url($document->current->path, endpoint: 'folder') : null;
        }
        return null;
    }
}