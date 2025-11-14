<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class DownloadAction
{
    public static function make(): Action
    {
        return Action::make('download')
            ->label('Descargar')
            ->icon(Heroicon::ArrowDown)
            ->action(function ($record) {
                try {
                    return Storage::download($record->current->path);
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                }
            });

    }
}
