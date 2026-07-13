<?php
/**
 * Script de diagnóstico para acciones "Ver en carpeta" y "Abrir archivo"
 * Ejecutar: php tmp_debug.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DIAGNÓSTICO DE ACCIONES ===\n\n";

// 1. Verificar variables de entorno
echo "1. VARIABLES DE ENTORNO:\n";
echo "   SHELL_API_URL: " . (env('SHELL_API_URL') ?: 'NO DEFINIDA') . "\n";
echo "   STORAGE_LOCAL_PATH: " . (env('STORAGE_LOCAL_PATH') ?: 'NO DEFINIDA') . "\n";
echo "   STORAGE_NETWORK_PATH: " . (env('STORAGE_NETWORK_PATH') ?: 'NO DEFINIDA') . "\n";
echo "   NETWORK_SHARE_ROOT: " . (env('NETWORK_SHARE_ROOT') ?: 'NO DEFINIDA') . "\n\n";

// 2. Probar conexión al servidor PHP auxiliar
echo "2. PROBANDO SERVIDOR PHP AUXILIAR (127.0.0.1:8970):\n";
$ch = curl_init('http://127.0.0.1:8970/folder.php?path=.');
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "   ERROR: $error\n";
} else {
    echo "   HTTP Status: $httpCode\n";
    echo "   Respuesta: " . substr($response, 0, 200) . "\n";
}
echo "\n";

// 3. Buscar un File en la BD con path válido
echo "3. VERIFICANDO UN ARCHIVO EN BD:\n";
try {
    $file = \App\Models\File::whereNotNull('path')->latest()->first();
    if ($file) {
        echo "   File ID: {$file->id}\n";
        echo "   Path en BD: {$file->path}\n";
        
        // Verificar si existe en Storage
        $exists = \Illuminate\Support\Facades\Storage::exists($file->path);
        echo "   Existe en Storage: " . ($exists ? 'SI' : 'NO') . "\n";
        
        // Probar exec_url
        echo "   Probando exec_url():\n";
        try {
            $url = exec_url($file->path, 'folder');
            echo "   exec_url result: " . ($url ?: 'null') . "\n";
        } catch (\Throwable $e) {
            echo "   exec_url ERROR: " . $e->getMessage() . "\n";
        }
        
        // Probar record_folder_url
        echo "   Probando record_folder_url():\n";
        try {
            $url2 = record_folder_url($file);
            echo "   record_folder_url result: " . ($url2 ?: 'null') . "\n";
        } catch (\Throwable $e) {
            echo "   record_folder_url ERROR: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   No hay archivos en la BD\n";
    }
} catch (\Throwable $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// 4. Probar path() helper
echo "4. PROBANDO path() HELPER:\n";
try {
    if ($file ?? null) {
        $absolutePath = path($file->path, base: true);
        echo "   path('{$file->path}', base:true) = $absolutePath\n";
    } else {
        echo "   No hay file para probar\n";
    }
} catch (\Throwable $e) {
    echo "   path() ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== DIAGNÓSTICO COMPLETADO ===\n";