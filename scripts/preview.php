<?php

$input = $_GET['path'] ?? null;
if (!$input) return;

$path = urldecode($input);
$arg = escapeshellarg($path);

exec("start \"\" $arg");
