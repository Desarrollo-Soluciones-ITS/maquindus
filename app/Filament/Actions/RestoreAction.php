<?php

namespace App\Filament\Actions;

use App\Models\Document;
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
                    if ($record instanceof Document) {
                        static::restoreDocument($record, 'No se pudo restaurar el documento', "El documento ya existe fuera de la carpeta \"Superado\".");
                    } else {
                        $documents = $record->documents()
                            ->withTrashed()
                            ->get();

                        foreach ($documents as $document) {
                            static::restoreDocument($document, 'No se pudo restaurar el registro', "El registro ya existe fuera de la carpeta \"Superado\".");
                        }

                        $record->restore();
                    }

                    $name = model_to_spanish($record::class);
                    Notification::make()
                        ->title("$name archivado")
                        ->body("El registro se ha restaurado de forma exitosa.")
                        ->success()
                        ->send();

                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            });
    }

    public static function restoreDocument(Document $record, string $error, string $message)
    {
        $files = $record->files()
            ->get();

        foreach ($files as $file) {
            $oldPath = $file->path;
            $newPath = str($oldPath)->explode('/');
            
            $newPath->shift();
            
            $newPath = $newPath->join('/');

            if (Storage::exists($newPath)) {
                Notification::make()
                    ->title($error)
                    ->body($message)
                    ->danger()
                    ->send();

                DB::rollBack();
                return;
            }

            $file->update(['path' => $newPath]);
            Storage::move($oldPath, $newPath);
        }

        $record->restore();
    }
}
