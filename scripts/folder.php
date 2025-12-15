<?php

$input = $_GET['path'] ?? null;
if (!$input) return;

$path = "\\\\192.168.56.10\\Proyecto Base de Datos\\" . urldecode($input);
$arg = escapeshellarg($path);

$out = null;
$code = null;
$cmd = "explorer /select,$arg";

exec($cmd, $out, $code);

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

echo json_encode(compact('cmd', 'out', 'code'));
