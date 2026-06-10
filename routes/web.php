<?php

use App\Http\Controllers\PreviewFileController;
use App\Http\Controllers\FolderController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['permission.any:documents.show_file,files.show_file'])
        ->get('/files/{file}/preview', PreviewFileController::class)
        ->name('files.preview');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/folder/open', [FolderController::class, 'open'])->name('folder.open');
});