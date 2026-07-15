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
            ->hidden(fn($record) => blank($record->current?->path ?? $record->path))
            ->action(function ($record, $livewire) {
                $file = $record->current ?? $record;
                if (!$file || blank($file->path)) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                    return;
                }

                try {
                    // Usar exec_url para generar URL al servidor auxiliar PHP
                    $url = exec_url($file->path, endpoint: 'folder');
                    if ($url) {
                        $livewire->js("fetch('$url')");
                    }
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                }
            });
    }
}