<?php

namespace App\Filament\Actions\Documents;

use App\Models\Document;
use App\Models\File;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class OpenFolderAction
{
    public static function make(): Action
    {
        // Detectar si estamos en modo LAN
        $isLanMode = filled(env('STORAGE_NETWORK_PATH'));

        if ($isLanMode) {
            // MODO LAN: el navegador del cliente hace fetch a su propio
            // servidor auxiliar http://127.0.0.1:8970/folder.php
            // con la ruta UNC convertida a URL
            return static::makeLanAction();
        }

        // MODO SERVIDOR LOCAL: el servidor llama a la shell API PHP auxiliar
        return static::makeServerAction();
    }

    private static function makeLanAction(): Action
    {
        return Action::make('folder')
            ->label('Ver en carpeta')
            ->icon(Heroicon::FolderOpen)
            ->hidden(function (Model $record): bool {
                $file = static::resolveFile($record);
                return blank($file?->path);
            })
            ->url(function (Model $record): ?string {
                $file = static::resolveFile($record);
                if ($file && filled($file->path)) {
                    // Redirigir a la ruta network.folder (Laravel)
                    // que renderiza una pagina HTML que lanza el protocolo gestor://
                    return route('network.folder', ['file' => $file->id]);
                }
                return null;
            })
            ->openUrlInNewTab(false);
    }

    private static function makeServerAction(): Action
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

    private static function resolveFile(Model $record): ?File
    {
        if ($record instanceof File) return $record;
        if ($record instanceof Document) return $record->current;
        if (method_exists($record, 'documents')) {
            $doc = $record->documents()->with('current')->latest('created_at')->first();
            return $doc?->current;
        }
        return null;
    }
}