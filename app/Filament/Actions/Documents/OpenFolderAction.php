<?php

namespace App\Filament\Actions\Documents;

use App\Models\Document;
use App\Models\File;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class OpenFolderAction
{
    public static function make(): Action
    {
        return Action::make('folder')
            ->label('Ver en carpeta')
            ->icon(Heroicon::FolderOpen)
            ->hidden(function (Model $record): bool {
                $file = static::resolveFile($record);
                return blank($file?->path);
            })
            ->action(function (Model $record, $livewire) {
                $file = static::resolveFile($record);
                if (!$file || blank($file->path)) {
                    Notification::make()
                        ->title('No se encontró el documento.')
                        ->danger()
                        ->send();
                    return;
                }

                $gestorUrl = record_folder_gestor_url($record);
                if ($gestorUrl) {
                    $livewire->js("window.location.href = '{$gestorUrl}'");
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