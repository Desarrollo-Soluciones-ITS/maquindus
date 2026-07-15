<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class OpenFolderAction
{
    public static function make(): Action
    {
        return Action::make('folder')
            ->label('Ver en carpeta')
            ->icon(Heroicon::FolderOpen)
            ->hidden(fn(Model $record) => blank(record_folder_url($record)))
            ->action(function (Model $record) {
                $url = record_folder_url($record);
                if ($url) {
                    Http::timeout(5)->get($url);
                }
            });
    }
}