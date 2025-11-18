<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class OpenFolderAction
{
    public static function make(): Action
    {
        return Action::make('folder')
            ->label('Abrir carpeta')
            ->icon(Heroicon::FolderOpen)
            ->hidden(is_localhost_request())
            ->action(function ($record) {
                try {
                    $path = path($record->current->path, asFolder: true);
                    exec("explorer \"$path\"");
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                }
            });
    }
}
