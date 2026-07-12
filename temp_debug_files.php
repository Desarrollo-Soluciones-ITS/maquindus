<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\File;
$files = File::limit(10)->get();
foreach ($files as $file) {
    $path = storage_path('app/private/' . $file->path);
    echo $file->path . ' => ' . $path . ' => ' . (file_exists($path) ? 'EXISTS' : 'MISSING') . PHP_EOL;
}
