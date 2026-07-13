<?php
/**
 * folder.php para PCs cliente
 * 
 * Recibe una ruta absoluta del servidor (C:\inetpub\...)
 * y la convierte a ruta UNC para abrir la carpeta en la PC del cliente.
 * 
 * Ejemplo:
 *   Entrada: C:\inetpub\wwwroot\gestor-archivos\storage\app\private\Equipos\...\archivo.pdf
 *   Salida:  \\192.168.0.4\private\Equipos\...\archivo.pdf
 * 
 * Uso desde el navegador:
 *   http://127.0.0.1:8970/folder.php?path=C%3A%5Cinetpub%5C...%5Carchivo.pdf
 */

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

// Obtener la raíz UNC desde variable de entorno
$uncRoot = getenv('SHELL_SHARE_ROOT');
if (!$uncRoot) {
    $uncRoot = '\\\\192.168.0.4\\private';
}
$uncRoot = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $uncRoot), DIRECTORY_SEPARATOR);

// Si la ruta es absoluta local (C:\), extraer la parte relativa después de "private"
// y reemplazar la raíz por la UNC
if (preg_match('/^[A-Za-z]:\\\\/', $input)) {
    // Buscar la última ocurrencia de "\private\" en la ruta
    $privatePos = stripos($input, '\private\\');
    if ($privatePos !== false) {
        $relative = substr($input, $privatePos + strlen('\private\\'));
        $input = $uncRoot . '\\' . $relative;
    } else {
        // Fallback: buscar la parte relativa completa
        $pos = stripos($input, '\storage\app\private\\');
        if ($pos !== false) {
            $relative = substr($input, $pos + strlen('\storage\app\private\\'));
            $input = $uncRoot . '\\' . $relative;
        } else {
            // Si no se encuentra, asumir que el nombre del archivo es la parte relativa
            $input = $uncRoot . '\\' . basename($input);
        }
    }
}

// Si la ruta no es UNC, asumir que es relativa y anteponer UNC
if (!preg_match('/^\\\\/', $input)) {
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