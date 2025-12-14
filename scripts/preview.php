<?php

$input = $_GET['path'] ?? null;
if (!$input) return;

$path = urldecode($input);
$arg = escapeshellarg($path);

$app = match ($file->mime) {
    'Excel' => 'excel',
    'Word' => 'winword',
    'PowerPoint' => 'powerpnt',
    default => "\"\"",
};

exec("start $app $arg");
