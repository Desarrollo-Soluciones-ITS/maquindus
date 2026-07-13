<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$input = $_GET['path'] ?? null;
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing path']);
    exit;
}

$relativePath = trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, urldecode($input)), DIRECTORY_SEPARATOR);

$pathsToTry = [];
$localRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'private');
if ($localRoot !== false) {
    $pathsToTry[] = $localRoot;
}
$sharedRoot = getenv('SHELL_SHARE_ROOT');
if ($sharedRoot) {
    $pathsToTry[] = rtrim($sharedRoot, DIRECTORY_SEPARATOR);
}
$pathsToTry[] = 'C:\\inetpub\\wwwroot\\gestor-archivos\\storage\\app\\private';
$pathsToTry[] = '\\192.168.0.4\\gestor-archivos\\storage\\app\\private';

$path = null;
foreach ($pathsToTry as $root) {
    $candidate = $root . DIRECTORY_SEPARATOR . $relativePath;
    if (file_exists($candidate)) {
        $path = $candidate;
        break;
    }
}

if ($path === null) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found', 'path' => end($pathsToTry) . DIRECTORY_SEPARATOR . $relativePath]);
    exit;
}

$quotedPath = '"' . str_replace('"', '\\"', $path) . '"';
$out = null;
$code = null;
$cmd = "start \"\" {$quotedPath}";

exec($cmd, $out, $code);

echo json_encode(compact('cmd', 'out', 'code'));
