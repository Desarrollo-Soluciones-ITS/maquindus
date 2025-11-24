<?php

namespace App\Filament\Actions\Documents;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction as FilamentDeleteAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteAction
{
    public static function make(): Action
    {
        return FilamentDeleteAction::make()
            ->using(function (Model $record) {
                DB::beginTransaction();
                try {
                    if ($record instanceof Document) {
                        $files = $record->files()->get();

                        foreach ($files as $file) {
                            $newPath = "Superado/{$file->path}";
                            $oldPath = $file->path;

                            if (Storage::exists($newPath)) {
                                Notification::make()
                                    ->title('No se pudo archivar el documento')
                                    ->body("El archivo \"{$file->path}\" ya existe en la carpeta \"Superado\".")
                                    ->danger()
                                    ->send();

                                DB::rollBack();
                                return;
                            }

                            $file->update(['path' => $newPath]);
                            Storage::move($oldPath, $newPath);
                            $file->delete();
                        }

                        $record->delete();
                    } else {
                        $documents = $record->documents()->get();

                        foreach ($documents as $document) {
                            $files = $document->files()->get();

                            foreach ($files as $file) {
                                $newPath = "Superado/{$file->path}";
                                $oldPath = $file->path;

                                if (Storage::exists($newPath)) {
                                    Notification::make()
                                        ->title('No se pudo archivar el registro')
                                        ->body("Ya existe un registro con esta ruta en la carpeta \"Superado\".")
                                        ->danger()
                                        ->send();

                                    DB::rollBack();
                                    return;
                                }

                                $file->update(['path' => $newPath]);
                                Storage::move($oldPath, $newPath);
                                $file->delete();
                            }

                            $document->delete();
                        }

                        $record->delete();
                    }

                    $name = model_to_spanish($record::class);
                    $description = strtolower($name);
                    Notification::make()
                        ->title("$name archivado")
                        ->body("El $description y sus documentos asociados han sido archivados correctamente.")
                        ->success()
                        ->send();

                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            })
            ->modalHeading(fn(Model $record): string => "Archivar '{$record->getAttribute('name')}'")
            ->modalDescription(function (Model $record) {
                $name = model_to_spanish($record::class);
                return "¿Estás seguro de que deseas archivar este $name? Esta acción lo moverá a la carpeta 'Superado' y lo ocultará en la interfaz.";
            })
            ->modalIcon(Heroicon::ArchiveBoxArrowDown)
            ->modalSubmitActionLabel('Archivar')
            ->modalCancelActionLabel('Cancelar')
            ->label('Archivar')
            ->icon(Heroicon::ArchiveBoxArrowDown);
    }
}
