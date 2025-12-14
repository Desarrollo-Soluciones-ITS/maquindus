<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class PreviewAction
{
    public static function make(): Action
    {
        return Action::make('preview')
            ->label('Abrir archivo')
            ->icon(Heroicon::OutlinedEye)
            ->action(function ($record, $livewire) {
                $file = $record->current ?? $record;
                try {
                    $path = urlencode(path($file->path));
                    $base = env('EXEC_URL');
                    $livewire->js("fetch('$base/preview.php?path=$path', { mode: 'no-cors' })");
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                }
            });

    }
}
