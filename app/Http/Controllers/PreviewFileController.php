<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PreviewFileController extends Controller
{
    public function __invoke(File $file): BinaryFileResponse
    {
        abort_unless(Storage::exists($file->path), 404);

        $storagePath = Storage::path($file->path);
        $mimeType = Storage::mimeType($file->path) ?: 'application/octet-stream';

        return response()->file($storagePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($file->path) . '"',
        ]);
    }
}