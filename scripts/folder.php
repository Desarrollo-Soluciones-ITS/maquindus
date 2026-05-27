<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$input = $_GET['path'] ?? null;

if (!$input) {
	http_response_code(400);
	echo json_encode(['error' => 'Missing path parameter']);
	exit;
}

$path = resolve_target_path((string) $input);
$arg = escapeshellarg($path);
$out = [];
$code = 0;

if (is_file($path)) {
	$cmd = 'cmd /c explorer /select,' . $arg;
} else {
	$cmd = 'cmd /c explorer ' . $arg;
}

exec($cmd, $out, $code);

echo json_encode(compact('cmd', 'out', 'code', 'path'));

function resolve_target_path(string $input): string
{
	$input = trim(urldecode($input));
	$input = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $input);

	if ($input === '') {
		return '';
	}

	if (preg_match('/^(?:[A-Za-z]:\\\\|\\\\\\\\)/', $input)) {
		return $input;
	}

	$root = getenv('SHELL_SHARE_ROOT') ?: '\\\\192.168.56.10\\Proyecto Base de Datos';
	$root = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR);

	return $root . DIRECTORY_SEPARATOR . ltrim($input, DIRECTORY_SEPARATOR);
}
