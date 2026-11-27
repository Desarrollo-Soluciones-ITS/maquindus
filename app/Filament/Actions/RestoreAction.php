<?php

namespace App\Filament\Actions;

use App\Models\Document;
use App\Models\Equipment;
use Filament\Actions\Action;
use Filament\Actions\RestoreAction as FilamentRestoreAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RestoreAction
{
    public static function make(): Action
    {
        return FilamentRestoreAction::make()
            ->icon(Heroicon::ArrowUturnLeft)
            ->color(Color::Green)
            ->using(function (Model $record) {
                DB::beginTransaction();
                try {
                    $restoreNameMap = null;

                    if ($record instanceof Equipment) {
                        $restoreNameMap = static::prepareRestoredEquipmentName($record);
                    }

                    if ($record instanceof Document) {
                        static::restoreDocument($record, 'No se pudo restaurar el documento', "El documento ya existe fuera de la carpeta \"Superado\".");
                    } else {
                        $documents = $record->documents()
                            ->withTrashed()
                            ->get();

                        foreach ($documents as $document) {
                            static::restoreDocument(
                                $document,
                                'No se pudo restaurar el registro',
                                "El registro ya existe fuera de la carpeta \"Superado\".",
                                $restoreNameMap,
                            );
                        }

                        if ($restoreNameMap) {
                            $record->saveQuietly();
                        }

                        $record->restore();
                    }

                    $name = model_to_spanish($record::class);
                    $body = 'El registro se ha restaurado de forma exitosa.';

                    if ($restoreNameMap) {
                        $body .= ' Se añadió la marca "(restaurado)" al nombre para distinguirlo del equipo con el mismo nombre.';
                    }

                    Notification::make()
                        ->title("$name restaurado")
                        ->body($body)
                        ->success()
                        ->send();

                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            });
    }

    /**
     * Si ya existe un equipo activo con el mismo nombre, se prepara un nombre
     * con una marca sutil para el equipo que se esta restaurando.
     *
     * @return array{old: string, new: string}|null
     */
    protected static function prepareRestoredEquipmentName(Equipment $equipment): ?array
    {
        $oldName = $equipment->getOriginal('name') ?: $equipment->name;

        if (!filled($oldName)) {
            return null;
        }

        $hasActiveDuplicate = Equipment::where('name', $oldName)
            ->whereKeyNot($equipment->getKey())
            ->exists();

        if (!$hasActiveDuplicate) {
            return null;
        }

        $newName = static::availableRestoredName($oldName, $equipment->getKey());
        $equipment->name = $newName;

        return [
            'old' => $oldName,
            'new' => $newName,
        ];
    }

    /**
     * Genera un nombre disponible con el sufijo "(restaurado)". Si ya existe,
     * usa "(restaurado 2)", "(restaurado 3)", etc.
     */
    public static function availableRestoredName(string $baseName, ?string $ignoreId = null): string
    {
        $suffix = ' (restaurado)';
        $candidate = static::withRestoredSuffix($baseName, $suffix);
        $counter = 2;

        while (static::restoredNameIsTaken($candidate, $ignoreId)) {
            $suffix = " (restaurado {$counter})";
            $candidate = static::withRestoredSuffix($baseName, $suffix);
            $counter++;
        }

        return $candidate;
    }

    protected static function restoredNameIsTaken(string $candidate, ?string $ignoreId): bool
    {
        $query = Equipment::where('name', $candidate);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists()
            || Storage::disk('local')->exists('Equipos/' . $candidate);
    }

    protected static function withRestoredSuffix(string $name, string $suffix): string
    {
        $maxBaseLength = max(1, 80 - mb_strlen($suffix));

        return mb_substr($name, 0, $maxBaseLength) . $suffix;
    }

    /**
     * @param  array{old: string, new: string}|null  $restoreNameMap
     */
    public static function restoreDocument(Document $record, string $error, string $message, ?array $restoreNameMap = null)
    {
        $files = $record->files()->get();
        $moves = [];

        foreach ($files as $file) {
            $oldPath = $file->path;
            $newPath = static::restoredFilePath($oldPath, $restoreNameMap);

            if (Storage::exists($newPath)) {
                Notification::make()
                    ->title($error)
                    ->body($message)
                    ->danger()
                    ->send();

                throw new \RuntimeException($message);
            }

            $moves[] = [$file, $oldPath, $newPath];
        }

        foreach ($moves as [$file, $oldPath, $newPath]) {
            $file->update(['path' => $newPath]);
            Storage::move($oldPath, $newPath);
        }

        $record->restore();
    }

    /**
     * @param  array{old: string, new: string}|null  $restoreNameMap
     */
    protected static function restoredFilePath(string $path, ?array $restoreNameMap): string
    {
        $segments = explode('/', $path);

        // Quitar el prefijo "Superado" añadido al archivar.
        array_shift($segments);

        if (
            $restoreNameMap
            && ($segments[0] ?? null) === 'Equipos'
            && ($segments[1] ?? null) === $restoreNameMap['old']
        ) {
            $segments[1] = $restoreNameMap['new'];
        }

        return implode('/', $segments);
    }
}
