<?php

namespace App\Filament\Actions\Documents;

use App\Filament\Pages\FileManagerPage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class OpenFolderAction
{
    public static function make(): Action
    {
        return Action::make('folder')
            ->label('Ver en carpeta')
            ->icon(Heroicon::FolderOpen)
            ->url(function ($record) {
                $file = $record->current ?? $record;

                if (!$file) {
                    Notification::make()
                        ->title('Archivo no encontrado')
                        ->body('No se encontró el archivo actual del documento.')
                        ->danger()
                        ->send();
                    return null;
                }

                // Pass the file ID as a parameter to FileManagerPage
                return FileManagerPage::getUrl([
                    'fileId' => $file->id,
                ]);
            })
            ->openUrlInNewTab()
            ->hidden(fn() => !currentUserHasPermission('files.open_in_folder'));
    }
}
