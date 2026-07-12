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
            ->url(function ($record) {
                $file = $record->current ?? $record;
                return exec_url($file->path, 'preview');
            }, true)
            ->openUrlInNewTab();
    }
}
