<?php

namespace App\Filament\Actions\Documents;

use App\Filament\Pages\FileManagerPage;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class ViewAction
{
    public static function make(): Action
    {
        return Action::make('view')
            ->label('Ver en Gestor')
            ->icon(Heroicon::FolderOpen)
            ->url(function ($record) {
                // Get the current file
                $file = $record->current;

                if (!$file) {
                    return null;
                }

                // Pass the file ID as a parameter
                return FileManagerPage::getUrl([
                    'fileId' => $file->id,
                ]);
            })
            ->openUrlInNewTab();
    }
}

