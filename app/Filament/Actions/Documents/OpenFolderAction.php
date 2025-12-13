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
            ->label('Ver en carpeta')
            ->icon(Heroicon::FolderOpen)
            ->action(function ($record) {
                $file = $record->current ?? $record;
                try {
                    $path = path($file->path);
                    exec("explorer /select,\"$path\"");
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                }
            });
    }
}
