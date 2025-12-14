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
            ->action(function ($record, $livewire) {
                $file = $record->current ?? $record;
                try {
                    $path = urlencode(path($file->path));
                    $base = env('EXEC_URL');
                    $livewire->js("fetch('$base/folder.php?path=$path')");
                } catch (\Throwable $th) {
                    dd($th);
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                }
            });
    }
}
