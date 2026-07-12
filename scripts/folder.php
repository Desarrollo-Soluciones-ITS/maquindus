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

$relativePath = trim(str_replace('/', DIRECTORY_SEPARATOR, urldecode($input)), DIRECTORY_SEPARATOR);

// Replace this with the actual UNC share path visible from each LAN client.
// Example: if the server shares the folder `gestor-archivos` and the private files are under
// C:\inetpub\wwwroot\gestor-archivos\storage\app\private, use:
// \\192.168.0.4\gestor-archivos\storage\app\private
$sharedRoot = '\\\\192.168.0.4\\gestor-archivos\\storage\\app\\private';
$root = rtrim($sharedRoot, DIRECTORY_SEPARATOR);
$path = $root . DIRECTORY_SEPARATOR . $relativePath;

if (!file_exists($path)) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found', 'path' => $path]);
    exit;
}

$arg = escapeshellarg($path);
$out = null;
$code = null;
$cmd = "explorer /select,$arg";

exec($cmd, $out, $code);

echo json_encode(compact('cmd', 'out', 'code'));
