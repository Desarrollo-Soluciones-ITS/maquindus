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
            ->url(function ($record) {
                $file = $record->current ?? $record;
                return exec_url($file->path, 'folder');
            }, true)
            ->openUrlInNewTab();
    }
}
