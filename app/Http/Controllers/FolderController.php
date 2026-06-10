<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FolderController extends Controller
{
    public function open(Request $request)
    {
        $path = $request->query('path');

        if (!$path) {
            abort(400, 'Missing path parameter');
        }

        $fullPath = Storage::disk('local')->path($path);

        if (!file_exists($fullPath)) {
            abort(404, 'File or directory not found');
        }

        $arg = escapeshellarg($fullPath);

        if (is_file($fullPath)) {
            $cmd = 'cmd /c explorer /select,' . $arg;
        } else {
            $cmd = 'cmd /c explorer ' . $arg;
        }

        exec($cmd, $out, $code);

        return response()->json([
            'cmd' => $cmd,
            'code' => $code,
            'path' => $fullPath,
        ]);
    }
}