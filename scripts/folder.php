<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$input = $_GET['path'] ?? null;

if (!$input) {
	http_response_code(400);
	echo json_encode(['error' => 'Missing path parameter']);
	exit;
}

$input = trim(urldecode($input));
$input = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $input);

if ($input === '') {
	http_response_code(400);
	echo json_encode(['error' => 'Empty path']);
	exit;
}

// Si ya viene como ruta absoluta (C:\) o UNC (\\), usarla directamente
if (!preg_match('/^(?:[A-Za-z]:\\\\|\\\\\\\\)/', $input)) {
	// Si es relativa, prepender el storage root
	$root = getenv('SHELL_SHARE_ROOT');
	if (!$root) {
		// Fallback: usar la ruta esperada del servidor
		$root = 'C:\\inetpub\\wwwroot\\gestor-archivos\\storage\\app\\private';
	}
	$root = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR);
	$input = $root . DIRECTORY_SEPARATOR . ltrim($input, DIRECTORY_SEPARATOR);
}

if (!file_exists($input)) {
	http_response_code(404);
	echo json_encode(['error' => 'File or directory not found', 'path' => $input]);
	exit;
}

$arg = escapeshellarg($input);
$out = [];
$code = 0;

if (is_file($input)) {
	$cmd = 'cmd /c explorer /select,' . $arg;
} else {
	$cmd = 'cmd /c explorer ' . $arg;
}

exec($cmd, $out, $code);

echo json_encode(compact('cmd', 'out', 'code', 'path'));