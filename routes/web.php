<?php

use App\Http\Controllers\NetworkFileController;
use App\Http\Controllers\PreviewFileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['permission.any:documents.show_file,files.show_file'])
        ->get('/files/{file}/preview', PreviewFileController::class)
        ->name('files.preview');

// Rutas para clientes LAN (usan protocolo gestor://)
Route::prefix('network')->middleware(['permission.any:documents.show_file,files.show_file'])->group(function () {
    Route::get('/folder/{file}', [NetworkFileController::class, 'openFolder'])
        ->name('network.folder');
    Route::get('/file/{file}', [NetworkFileController::class, 'openFile'])
        ->name('network.file');
});
