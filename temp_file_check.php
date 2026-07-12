<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\File;

echo 'ENV STORAGE_ROOT=' . env('STORAGE_ROOT') . PHP_EOL;
echo 'CONFIG ROOT=' . Config::get('filesystems.disks.local.root') . PHP_EOL;
echo 'DEFAULT DISK=' . Config::get('filesystems.default') . PHP_EOL;
echo 'FILES COUNT=' . DB::table('files')->count() . PHP_EOL;
echo 'FILES LIMIT 20:' . PHP_EOL;
foreach (File::limit(20)->get() as $file) {
    $exists = Storage::disk('local')->exists($file->path) ? 'YES' : 'NO';
    echo $file->id . ' | ' . $file->path . ' | ' . $exists . PHP_EOL;
}

echo 'EQUIPOS FILES LIMIT 20:' . PHP_EOL;
foreach (File::where('path', 'like', 'Equipos/%')->limit(20)->get() as $file) {
    $exists = Storage::disk('local')->exists($file->path) ? 'YES' : 'NO';
    echo $file->id . ' | ' . $file->path . ' | ' . $exists . PHP_EOL;
}
