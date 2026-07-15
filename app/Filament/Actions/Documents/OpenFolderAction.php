<?php

namespace App\Filament\Actions\Documents;

use App\Models\Document;
use App\Models\File;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class OpenFolderAction
{
    public static function make(): Action
    {
        // Detectar si estamos en modo LAN (red con protocolo gestor://)
        $isLanMode = filled(env('STORAGE_NETWORK_PATH'));

        if ($isLanMode) {
            // MODO LAN: el navegador redirige a la ruta network.folder
            // que renderiza una página que lanza el protocolo gestor:// en el cliente
            return static::makeLanAction();
        }

        // MODO SERVIDOR LOCAL: el servidor llama a la shell API PHP auxiliar
        // (Http::get a http://127.0.0.1:8970/folder.php)
        return static::makeServerAction();
    }

    private static function makeLanAction(): Action
    {
        return Action::make('folder')
            ->label('Ver en carpeta')
            ->icon(Heroicon::FolderOpen)
            ->hidden(function (Model $record): bool {
                $file = $record instanceof File ? $record
                    : ($record instanceof Document ? $record->current
                    : (method_exists($record, 'documents') ? $record->documents()->with('current')->latest('created_at')->first()?->current : null));

                return blank($file?->path);
            })
            ->url(function (Model $record): ?string {
                $file = $record instanceof File ? $record
                    : ($record instanceof Document ? $record->current
                    : (method_exists($record, 'documents') ? $record->documents()->with('current')->latest('created_at')->first()?->current : null));

                if ($file && filled($file->path)) {
                    return route('network.folder', ['file' => $file->id]);
                }

                return null;
            }, shouldOpenInNewTab: false);
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
}
