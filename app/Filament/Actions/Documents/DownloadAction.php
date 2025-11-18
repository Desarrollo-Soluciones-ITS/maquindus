<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class DownloadAction
{
    public static function make(): Action
    {
        return Action::make('download')
            ->label('Descargar')
            ->icon(Heroicon::ArrowDownTray)
            ->action(function ($record) {
                try {
                    $file = $record->current ?? $record;
                    return Storage::download($file->path);
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                }
            });

    }
}
