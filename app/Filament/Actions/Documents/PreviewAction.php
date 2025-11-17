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
            ->label('Previsualizar')
            ->icon(Heroicon::OutlinedEye)
            ->action(function ($record) {
                try {
                    dd('Previewing');
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                }
            });

    }
}
