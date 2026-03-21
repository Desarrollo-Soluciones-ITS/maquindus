<?php

namespace App\Filament\Actions\Documents;

use App\Models\File;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class PreviewAction
{
    public static function make(): Action
    {
        return Action::make('preview')
            ->label('Abrir archivo')
            ->icon(Heroicon::OutlinedEye)
            ->url(function ($record): ?string {
                $file = $record->current ?? $record;

                if (! $file instanceof File || blank($file->path)) {
                    return null;
                }

                return route('files.preview', ['file' => $file]);
            }, shouldOpenInNewTab: true);

    }
}
