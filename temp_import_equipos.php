<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;
use App\Models\Equipment;
use App\Models\Document;
use App\Models\File;

require_once __DIR__ . '/app/helpers.php';

$paths = Storage::disk('local')->allFiles('Equipos');
$imported = 0;
$skipped = 0;

foreach ($paths as $path) {
    try {
        if (File::where('path', $path)->exists()) {
            $skipped++;
            continue;
        }

        $segments = explode('/', $path);
        if (count($segments) < 2) {
            $skipped++;
            continue;
        }

        $equipmentName = $segments[1];
        $equipment = Equipment::firstOrCreate(['name' => $equipmentName]);

        $filenameWithExt = basename($path);
        $extension = pathinfo($filenameWithExt, PATHINFO_EXTENSION);
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

        $document = $equipment->documents()->firstOrCreate([
            'name' => $filename,
        ]);

        $mime = Storage::mimeType($path) ?: 'application/octet-stream';

        $document->files()->create([
            'path' => $path,
            'mime' => $mime,
            'version' => 1,
        ]);

        $imported++;
    } catch (Throwable $e) {
        echo 'error importing ' . $path . ' => ' . $e->getMessage() . PHP_EOL;
    }
}

echo "imported={$imported} skipped={$skipped} total_paths=" . count($paths) . PHP_EOL;
