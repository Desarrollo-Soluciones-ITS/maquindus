<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProtectedFileController extends Controller
{
    public function serve(Request $request)
    {
        try {
            $token = $request->query('token');
            if (empty($token)) {
                abort(400, 'Token no proporcionado');
            }

            $decoded = base64_decode($token);
            if ($decoded === false) {
                abort(400, 'Token mal formado');
            }

            $data = json_decode($decoded, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                abort(400, 'Token JSON inválido');
            }

            if (!isset($data['path'], $data['disk'], $data['expires'], $data['signature'])) {
                abort(400, 'Token incompleto');
            }

            $path = str_replace('\\', '/', $data['path']);
            $diskName = $data['disk'];
            $expires = (int) $data['expires'];
            $signature = $data['signature'];

            // Verificar firma incluyendo el nombre del disco
            $expectedSignature = hash_hmac('sha256', $path . '|' . $diskName . '|' . $expires, config('app.key'));
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Firma inválida en ProtectedFileController', ['path' => $path, 'disk' => $diskName]);
                abort(401, 'Firma inválida');
            }

            // Verificar expiración
            if (now()->timestamp > $expires) {
                abort(401, 'El enlace ha expirado');
            }

            // Usar el disco correcto
            $disk = Storage::disk($diskName);

            if (!$disk->exists($path)) {
                Log::error('Archivo no encontrado', [
                    'disk' => $diskName,
                    'path' => $path,
                    'full_path' => $disk->path($path)
                ]);
                abort(404, 'Archivo no encontrado en disco ' . $diskName);
            }

            // Servir archivo
            $fullPath = $disk->path($path);
            $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
            $fileName = basename($path);

            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error en ProtectedFileController: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            abort(500, 'Error interno del servidor');
        }
    }
}