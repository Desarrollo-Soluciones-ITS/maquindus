<?php

namespace App\Filament\Actions\Documents;

use App\Models\Document;
use App\Models\File;
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
            ->url(function (Model $record): ?string {
                // Redirigir a la vista network que maneja la apertura en nueva pestaña
                // la vista se cierra sola y la pestaña original no se pierde
                $file = $record instanceof File
                    ? $record
                    : ($record->current ?? null);

                if ($file instanceof File && filled($file->path)) {
                    return route('network.folder', ['file' => $file->id]);
                }
                if ($record instanceof Document && $record->current) {
                    return route('network.folder', ['file' => $record->current->id]);
                }

                $folderUrl = record_folder_url($record);
                if ($folderUrl) {
                    return $folderUrl;
                }

                return null;
            }, shouldOpenInNewTab: true); // <-- NUEVA PESTAÑA, no pierde la vista original
    }
}