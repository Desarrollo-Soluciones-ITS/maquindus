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
                    $url = exec_url($file->path, endpoint: 'preview');
                    $livewire->js("fetch('$url')");
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                }
            });

    }
}
