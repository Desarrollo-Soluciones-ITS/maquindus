<?php

$input = $_GET['path'] ?? null;

if (!$input) {
	http_response_code(400);
	echo 'Missing path parameter';
	exit;
}

$input = trim(urldecode($input));
$input = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $input);

if ($input === '') {
	http_response_code(400);
	echo 'Empty path';
	exit;
}

// Si ya viene como ruta absoluta (C:\) o UNC (\\), usarla directamente
if (!preg_match('/^(?:[A-Za-z]:\\\\|\\\\\\\\)/', $input)) {
	$root = getenv('SHELL_SHARE_ROOT');
	if (!$root) {
		$root = 'C:\\inetpub\\wwwroot\\gestor-archivos\\storage\\app\\private';
	}
	$root = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR);
	$input = $root . DIRECTORY_SEPARATOR . ltrim($input, DIRECTORY_SEPARATOR);
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