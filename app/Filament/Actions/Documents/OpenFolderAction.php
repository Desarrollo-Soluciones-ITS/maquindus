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
            ->icon(Heroicon::FolderOpisen)
            ->hidden(fn(Model $record) => blank(record_folder_url($record)))
            ->url(function (Model $record): ?string {
                // 1. Intentar con protocolo gestor:// (funciona en cualquier PC)
                $file = $record instanceof File
                    ? $record
                    : ($record->current ?? null);

                if ($file instanceof File && filled($file->path)) {
                    $gestorUrl = gestor_net_url($file->path, 'select');
                    if ($gestorUrl) {
                        return $gestorUrl;
                    }
                }

                // 2. Fallback: redirect a la vista network (usa gestor:// vía navegador)
                if ($record instanceof File) {
                    return route('network.folder', ['file' => $record->id]);
                }
                if ($record instanceof Document && $record->current) {
                    return route('network.folder', ['file' => $record->current->id]);
                }

                return null;
            }, shouldOpenInNewTab: false);
    }
}