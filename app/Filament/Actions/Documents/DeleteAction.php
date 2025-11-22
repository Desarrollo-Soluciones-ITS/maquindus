<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction as FilamentDeleteAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DeleteAction
{
    public static function make(): Action
    {
        return FilamentDeleteAction::make()
            ->using(function (Model $record) {
                \DB::beginTransaction();
                try {
                    foreach ($record->files as $file) {
                        $newPath = "Superado/" . $file->path;
                        $oldPath = $file->path;

                        if (Storage::exists($newPath)) {
                            Notification::make()
                                ->title('No se pudo archivar el documento')
                                ->body('El archivo "' . $file->path . '" ya existe en la carpeta "Superado".')
                                ->danger()
                                ->send();

                            \DB::rollBack();
                            return;
                        }

                        $file->update(['path' => $newPath]);
                        Storage::move($oldPath, $newPath);
                        $file->delete();
                    }

                    $record->delete();
                    \DB::commit();
                } catch (\Throwable $e) {
                    \DB::rollBack();
                    throw $e;
                }

                $record->delete();
            })
            ->modalHeading('Archivar Documento')
            ->modalDescription('¿Estás seguro de que deseas archivar este documento? Esta acción moverá todas sus versiones a la carpeta "Superado" y ocultará el documento en la tabla.')
            ->modalIcon(Heroicon::ArchiveBoxArrowDown)
            ->modalSubmitActionLabel('Archivar')
            ->modalCancelActionLabel('Cancelar')
            ->label('Archivar')
            ->icon(Heroicon::ArchiveBoxArrowDown);
    }
}
