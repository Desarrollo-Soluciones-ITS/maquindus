<?php

namespace App\Filament\Actions\Documents;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class PreviewAction
{
    private static array $browserPreviewable = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'svg',
        'bmp',
        'ico',
        'tiff',
        'tif',
        'pdf',
        'txt',
        'html',
        'htm',
        'csv',
        'json',
        'xml',
        'md',
        'log',
        'mp4',
        'webm',
        'ogg',
        'mov',
        'mp3',
        'wav',
        'ogg',
        'm4a',
    ];

    public static function make(): Action
    {
        return Action::make('preview')
            ->label('Abrir archivo')
            ->icon(Heroicon::OutlinedEye)
            ->action(function ($record, $livewire) {
                $file = $record->current ?? $record;

                try {
                    $path = $file->path;
                    $diskName = $file->disk ?? config('filesystems.default');

                    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    $fileName = basename($path);

                    // Generar URL temporal firmada para el disco específico
                    $url = self::generateAuthenticatedUrl($path, $diskName);

                    if (in_array($extension, self::$browserPreviewable)) {
                        $livewire->js("window.open('{$url}', '_blank');");
                        Notification::make()
                            ->title('Abriendo vista previa')
                            ->body("El archivo se abrirá en una nueva pestaña.")
                            ->success()
                            ->send();
                    } else {
                        $livewire->js("
                            const link = document.createElement('a');
                            link.href = '{$url}';
                            link.download = '" . addslashes($fileName) . "';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        ");
                        Notification::make()
                            ->title('Descargando archivo')
                            ->body("El formato {$extension} se descargará automáticamente.")
                            ->info()
                            ->send();
                    }
                } catch (\Throwable $th) {
                    Notification::make()
                        ->title('Error al abrir archivo')
                        ->body('No se pudo abrir el documento: ' . $th->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Generar URL temporal con autenticación para archivos protegidos
     */
    private static function generateAuthenticatedUrl(string $path, string $diskName): string
    {
        // Normalizar slashes para consistencia
        $path = str_replace('\\', '/', $path);

        $expires = now()->addMinutes(30)->timestamp;
        $signature = hash_hmac('sha256', $path . '|' . $diskName . '|' . $expires, config('app.key'));

        return route('storage.protected', [
            'token' => base64_encode(json_encode([
                'path' => $path,
                'disk' => $diskName,
                'expires' => $expires,
                'signature' => $signature,
            ])),
        ]);
    }
}