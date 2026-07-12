<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\File;

try {
    echo 'files total=' . File::count() . PHP_EOL;
    echo 'equipos files=' . File::where('path','like','Equipos/%')->count() . PHP_EOL;
    foreach (File::where('path','like','Equipos/%')->limit(10)->get() as $f) {
        echo $f->id . ' | ' . $f->path . PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'error: ' . $e->getMessage() . PHP_EOL;
}
