<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class OpenFolderAction
{
    public static function make(): Action
    {
        return Action::make('folder')
            ->label('Abrir carpeta')
            ->icon(Heroicon::FolderOpen)
            ->hidden(is_localhost_request())
            ->action(function ($record) {
                $segments = str($record->current->path)
                    ->explode('/');

                $segments->pop();

                $folder = $segments->join('\\');
                $exists = Storage::directoryExists($folder);

                if (!$exists) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                    return;
                }

                $path = str(Storage::path($folder))
                    ->replace('/', DIRECTORY_SEPARATOR)
                    ->replace('\\',DIRECTORY_SEPARATOR);

                exec("explorer \"$path\"");
            });
    }
}
