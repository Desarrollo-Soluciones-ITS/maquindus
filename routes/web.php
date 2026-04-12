<?php

use App\Http\Controllers\PreviewFileController;
use App\Http\Controllers\ProtectedFileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use LivewireFilemanager\Filemanager\Http\Controllers\Files\FileController;

Route::redirect('/', '/dashboard');

Route::middleware(['permission.any:documents.show_file,files.show_file'])
    ->get('/files/{file}/preview', PreviewFileController::class)
    ->name('files.preview');
Route::get('/storage/protected', [ProtectedFileController::class, 'serve'])
    ->name('storage.protected')
    ->middleware(['web', 'auth']);

// Serve files from filemanager disk (MUST be before catch-all)
Route::get('/filemanager-files/{path}', function ($path) {
    $disk = Storage::disk('filemanager');
    $path = urldecode($path);

    if (!$disk->exists($path)) {
        \Log::warning('Filemanager file not found', ['path' => $path, 'root' => config('filesystems.disks.filemanager.root')]);
        abort(404, 'Archivo no encontrado: ' . $path);
    }

    $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

    return response()->stream(function () use ($disk, $path) {
        echo $disk->get($path);
    }, 200, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        'Content-Length' => $disk->size($path),
    ]);
})->where('path', '.*')->name('filemanager.files.show');

// Catch-all route
Route::get('{path}', [FileController::class, 'show'])
    ->where('path', '.*')
    ->name('assets.show');
