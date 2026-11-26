<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$root = (string) config('filesystems.disks.local.root');

if ($root === '') {
    fwrite(STDERR, "No se pudo obtener filesystems.disks.local.root\n");
    exit(1);
}

echo rtrim($root, "\\/"), PHP_EOL;
