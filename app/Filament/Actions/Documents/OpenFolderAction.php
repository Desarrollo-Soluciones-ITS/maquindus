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
                // 1. Intentar con protocolo gestor:// (funciona en cualquier PC con PowerShell)
                $file = $record instanceof File
                    ? $record
                    : ($record->current ?? null);

                if ($file instanceof File && filled($file->path)) {
                    $gestorUrl = gestor_net_url($file->path, 'select');
                    if ($gestorUrl) {
                        return $gestorUrl;
                    }
                }

                // 2. Fallback 1: redirect a la vista network (usa gestor:// via navegador)
                if ($record instanceof File) {
                    return route('network.folder', ['file' => $record->id]);
                }
                if ($record instanceof Document && $record->current) {
                    return route('network.folder', ['file' => $record->current->id]);
                }

                // 3. Fallback 2: URL del servidor PHP auxiliar (solo funciona en servidor)
                $folderUrl = record_folder_url($record);
                if ($folderUrl) {
                    return $folderUrl;
                }

                return null;
            }, shouldOpenInNewTab: false);
    }
}