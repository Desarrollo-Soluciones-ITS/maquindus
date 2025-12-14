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
                    $url = exec_url($file->path, endpoint: 'folder');
                    $livewire->js("fetch('$url')");
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
