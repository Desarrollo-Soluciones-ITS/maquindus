<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class OpenFolderAction
{
    public static function make(): Action
    {
        return Action::make('folder')
            ->label('Ver en carpeta')
            ->icon(Heroicon::FolderOpen)
            ->hidden(fn(Model $record) => blank(record_folder_url($record)))
            ->alpineClickAction(fn(Model $record): string => 'fetch(\'' . record_folder_url($record) . '\')');
    }
}
