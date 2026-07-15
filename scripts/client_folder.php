<?php
/**
 * client_folder.php para PCs cliente
 * 
 * Recibe una URL del protocolo gestor:// via parametro gestor=
 * y ejecuta explorer en la ruta UNC extraida.
 * 
 * Uso:
 *   http://127.0.0.1:8970/client_folder.php?gestor=gestor%3A%2F%2Fselect%3Fpath%3D%5C%5C...
 *   http://127.0.0.1:8970/client_folder.php?path=\\192.168.0.4\private\...
 */

// Compatibilidad: aceptar tanto ?gestor= como ?path= directo (para folder.php legacy)
$input = $_GET['gestor'] ?? $_GET['path'] ?? null;

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameter']);
    exit;
}

$input = trim(urldecode($input));

// Si viene como URL gestor:// (via gestor=), extraer la ruta UNC
if (preg_match('/^gestor/', $input)) {
    $parts = parse_url($input);
    parse_str($parts['query'] ?? '', $query);
    $path = $query['path'] ?? null;
    if (!$path) {
        http_response_code(400);
        echo json_encode(['error' => 'No path found in gestor URL']);
        exit;
    }
    $input = urldecode($path);
}

$input = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $input);
$input = rtrim($input, DIRECTORY_SEPARATOR);

if ($input === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Empty path']);
    exit;
}

// Asegurar formato UNC si es ruta relativa
if (!preg_match('/^\\\\/', $input)) {
    $uncRoot = getenv('SHELL_SHARE_ROOT') ?: '\\\\192.168.0.4\\private';
    $uncRoot = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $uncRoot), DIRECTORY_SEPARATOR);
    $input = $uncRoot . DIRECTORY_SEPARATOR . ltrim($input, DIRECTORY_SEPARATOR);
}

$arg = escapeshellarg($input);
$code = 0;

if (is_file($input)) {
    $cmd = 'cmd /c explorer /select,' . $arg;
} else {
    $cmd = 'cmd /c explorer ' . $arg;
}

exec($cmd, $out, $code);
?>
<!DOCTYPE html>
<html>
<head><title>Abriendo carpeta...</title></head>
<body>
<script>
	// Cerrar esta pestaña automáticamente
	window.close();
</script>
</body>
</html>