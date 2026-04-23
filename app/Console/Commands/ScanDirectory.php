<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ScanDirectory extends Command
{
    protected $signature = 'scan:directory 
                            {path : Ruta del directorio a escanear} 
                            {--output=directory_map.txt : Ruta o nombre del archivo de salida}';

    protected $description = 'Escanea recursivamente un directorio y genera un mapa jerárquico en formato .txt';

    public function handle()
    {
        $inputPath = rtrim($this->argument('path'), DIRECTORY_SEPARATOR);
        $resolvedPath = realpath($inputPath);
        $outputFile = $this->option('output');

        if ($resolvedPath === false || !is_dir($resolvedPath)) {
            $this->error("❌ La ruta '{$inputPath}' no es un directorio válido o no existe.");
            return self::FAILURE;
        }

        $this->info("🔍 Escaneando directorio: {$resolvedPath}...");

        // 1️⃣ Generar el árbol
        $treeContent = $this->buildTree($resolvedPath, 0);

        // 2️⃣ Prependar la ruta escaneada al inicio del archivo
        $content = "📂 Ruta escaneada: {$resolvedPath}\n\n" . $treeContent;

        // Resolver ruta absoluta de salida si es relativa
        if (!str_starts_with($outputFile, '/') && !str_starts_with($outputFile, '\\') && !preg_match('/^[a-zA-Z]:/', $outputFile)) {
            $outputFile = storage_path("app/{$outputFile}");
        }

        // Asegurar que el directorio de destino exista
        File::ensureDirectoryExists(pathinfo($outputFile, PATHINFO_DIRNAME));
        File::put($outputFile, $content);

        $this->info("✅ Mapa generado exitosamente en: {$outputFile}");
        return self::SUCCESS;
    }

    protected function buildTree(string $dir, int $level): string
    {
        $output = '';
        $indent = str_repeat('  ', $level);
        $dirName = basename($dir);

        // Imprimir nombre de la carpeta actual
        $output .= $indent . "[ 📁 ] {$dirName}\n";

        $items = [];
        foreach (File::directories($dir) as $d) {
            $items[] = ['path' => $d, 'is_dir' => true];
        }
        foreach (File::files($dir) as $f) {
            $items[] = ['path' => $f->getPathname(), 'is_dir' => false];
        }

        // Ordenar alfabéticamente (case-insensitive)
        usort($items, fn($a, $b) => strcasecmp(basename($a['path']), basename($b['path'])));

        foreach ($items as $item) {
            $name = basename($item['path']);
            $childIndent = str_repeat('  ', $level + 1);

            // Evitar enlaces simbólicos que podrían causar bucles infinitos
            if (is_link($item['path'])) {
                $output .= "{$childIndent}- [ 🔗 ] {$name} (enlace simbólico)\n";
                continue;
            }

            if ($item['is_dir']) {
                $output .= $this->buildTree($item['path'], $level + 1);
            } else {
                $size = $this->humanFileSize(filesize($item['path']));
                $output .= "{$childIndent}- [ 📄 ] {$name} ({$size})\n";
            }
        }

        return $output;
    }

    protected function humanFileSize(int $bytes, int $precision = 2): string
    {
        if ($bytes === 0)
            return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / (1 << (10 * $pow)), $precision) . ' ' . $units[$pow];
    }
}