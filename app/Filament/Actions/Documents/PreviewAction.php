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
            ->hidden(is_not_localhost())
            ->action(function ($record) {
                $file = $record->current ?? $record;
                try {
                    $path = path($file->path);
                    $app = match ($file->mime) {
                        'Excel' => 'excel',
                        'Word' => 'winword',
                        'PowerPoint' => 'powerpnt',
                        default => "\"\"",
                    };

                    $command = "start $app \"$path\"";

                    exec($command);
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                }
            });

    }
}
