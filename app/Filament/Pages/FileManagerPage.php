<?php

namespace App\Filament\Pages;

use App\Models\File;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;

class FileManagerPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Folder;

    protected static ?string $title = 'Gestor de archivos';

    protected string $view = 'filament.pages.file-manager';

    protected static ?string $navigationLabel = 'Gestor de Archivos';
    protected static ?int $navigationSort = 99;

    public string $currentPath = '';
    public string $rootPath = '';
    public string $parentPath = '';
    public array $folders = [];
    public array $fileList = [];
    public array $breadcrumb = [];
    public bool $readOnly = false;

    #[Url()]
    public $fileId = null;

    // Cache configuration
    protected const CACHE_TTL = 300; // 5 minutes
    protected const CACHE_PREFIX = 'filemanager_';

    // MIME type cache for known extensions
    protected static array $KNOWN_MIME_TYPES = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'bmp' => 'image/bmp',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'txt' => 'text/plain',
        'html' => 'text/html',
        'htm' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'csv' => 'text/csv',
        'md' => 'text/markdown',
        'log' => 'text/plain',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'audio/ogg',
        'mov' => 'video/quicktime',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'm4a' => 'audio/mp4',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        '7z' => 'application/x-7z-compressed',
        'tar' => 'application/x-tar',
        'gz' => 'application/gzip',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'dwg' => 'application/acad',
        'dxf' => 'application/dxf',
        'dwf' => 'application/x-dwf',
    ];

    public function mount(): void
    {
        if (!currentUserHasPermission('filemanager.view')) {
            abort(403, 'No tienes permiso para acceder al gestor de archivos.');
        }

        $this->rootPath = rtrim(config('filesystems.disks.filemanager.root'), '/\\');

        if ($this->fileId) {
            $this->navigateToFile($this->fileId);
        } else {
            $this->currentPath = session('filemanager_path', $this->rootPath);
        }

        $this->readOnly = !currentUserHasPermission('filemanager.upload');
        $this->loadCurrentDirectory();
    }

    protected function navigateToFile(string $fileId): void
    {
        try {
            $file = File::find($fileId);

            if (!$file) {
                Notification::make()->title('Archivo no encontrado')->danger()->send();
                $this->currentPath = $this->rootPath;
                return;
            }

            $fullPath = rtrim($this->rootPath, '/\\') . DIRECTORY_SEPARATOR .
                str_replace('/', DIRECTORY_SEPARATOR, ltrim(dirname($file->path), '/\\'));

            $realRoot = realpath($this->rootPath);
            $realPath = realpath($fullPath);

            if ($realRoot && $realPath && strpos($realPath, $realRoot) === 0) {
                $this->currentPath = $fullPath;
                session(['filemanager_path' => $fullPath]);
            } else {
                Notification::make()->title('Ruta no válida')->danger()->send();
                $this->currentPath = $this->rootPath;
            }
        } catch (\Exception $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
            $this->currentPath = $this->rootPath;
        }
    }

    protected function getCacheKey(string $path): string
    {
        return self::CACHE_PREFIX . md5($path);
    }

    protected function invalidateCache(string $path): void
    {
        Cache::forget($this->getCacheKey($path));

        $parent = dirname($path);
        $realRoot = realpath($this->rootPath);

        while ($parent !== '.' && $parent !== $this->rootPath) {
            $realParent = realpath($parent);
            if (!$realRoot || !$realParent || strpos($realParent, $realRoot) !== 0) {
                break;
            }
            Cache::forget($this->getCacheKey($parent));
            $parent = dirname($parent);
        }
    }

    protected function loadCurrentDirectory(): void
    {
        if (!is_dir($this->currentPath)) {
            mkdir($this->currentPath, 0755, true);
        }

        $this->breadcrumb = $this->buildBreadcrumb();

        // Calculate parent path
        $parentPath = dirname($this->currentPath);
        $realRoot = realpath($this->rootPath);
        $realParent = realpath($parentPath);

        if (!$realRoot || !$realParent || strpos($realParent, $realRoot) !== 0) {
            $this->parentPath = $this->rootPath;
        } else {
            $this->parentPath = $parentPath;
        }

        // Try cache first
        $cached = Cache::get($this->getCacheKey($this->currentPath));
        if ($cached !== null) {
            $this->folders = $cached['folders'];
            $this->fileList = $cached['files'];
            return;
        }

        // Cache miss - use optimized DirectoryIterator
        $this->folders = [];
        $this->fileList = [];

        try {
            $iterator = new \FilesystemIterator($this->currentPath, \FilesystemIterator::SKIP_DOTS);

            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    $this->folders[] = [
                        'name' => $item->getFilename(),
                        'path' => $item->getPathname(),
                        'modified' => date('Y-m-d H:i', $item->getMTime()),
                    ];
                } else {
                    $this->fileList[] = [
                        'name' => $item->getFilename(),
                        'path' => $item->getPathname(),
                        'size' => $this->formatFileSize($item->getSize()),
                        'mime' => $this->getMimeType($item),
                        'modified' => date('Y-m-d H:i', $item->getMTime()),
                    ];
                }
            }

            // Sort alphabetically
            usort($this->folders, fn($a, $b) => strcasecmp($a['name'], $b['name']));
            usort($this->fileList, fn($a, $b) => strcasecmp($a['name'], $b['name']));

            // Cache results
            Cache::put($this->getCacheKey($this->currentPath), [
                'folders' => $this->folders,
                'files' => $this->fileList,
            ], self::CACHE_TTL);

        } catch (\Exception $e) {
            // Silently fail
        }
    }

    protected function getMimeType(\SplFileInfo $file): string
    {
        $extension = strtolower($file->getExtension());

        if (isset(self::$KNOWN_MIME_TYPES[$extension])) {
            return self::$KNOWN_MIME_TYPES[$extension];
        }

        return mime_content_type($file->getPathname()) ?: 'Desconocido';
    }

    protected function buildBreadcrumb(): array
    {
        $breadcrumb = [];
        $parts = explode(DIRECTORY_SEPARATOR, $this->currentPath);
        $rootParts = explode(DIRECTORY_SEPARATOR, $this->rootPath);
        $relativeParts = array_slice($parts, count($rootParts));

        $breadcrumb[] = ['name' => basename($this->rootPath), 'path' => $this->rootPath];

        $currentBuildPath = $this->rootPath;
        foreach ($relativeParts as $part) {
            if (empty($part))
                continue;
            $currentBuildPath .= DIRECTORY_SEPARATOR . $part;
            $breadcrumb[] = ['name' => $part, 'path' => $currentBuildPath];
        }

        return $breadcrumb;
    }

    public function navigateTo(string $path): void
    {
        // Validate path is within root
        $realRoot = realpath($this->rootPath);
        $realPath = realpath($path);

        if ($realRoot && $realPath && strpos($realPath, $realRoot) === 0) {
            $this->currentPath = $path;
            $this->parentPath = dirname($path);
            session(['filemanager_path' => $path]);
            $this->loadCurrentDirectory();
        }
    }

    public function navigateUp(): void
    {
        if ($this->currentPath !== $this->rootPath) {
            // Recalculate parent path
            $parentPath = dirname($this->currentPath);
            $realRoot = realpath($this->rootPath);
            $realParent = realpath($parentPath);

            if (!$realRoot || !$realParent || strpos($realParent, $realRoot) !== 0) {
                $parentPath = $this->rootPath;
            }

            $this->navigateTo($parentPath);
        }
    }

    public function navigateToBreadcrumb(int $index): void
    {
        if ($index < count($this->breadcrumb)) {
            $this->navigateTo($this->breadcrumb[$index]['path']);
        }
    }

    public function deleteFolder(string $path): void
    {
        if ($this->readOnly) {
            Notification::make()->title('Sin permiso')->body('No tienes permiso para eliminar carpetas.')->danger()->send();
            return;
        }

        if (!is_dir($path)) {
            Notification::make()->title('Error')->body('La carpeta no existe.')->danger()->send();
            return;
        }

        $this->deleteDirectory($path);
        $this->invalidateCache($path);

        Notification::make()->title('Carpeta eliminada')->success()->send();
        $this->loadCurrentDirectory();
    }

    protected function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir))
            return false;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        return rmdir($dir);
    }

    public function deleteFile(string $path): void
    {
        if ($this->readOnly) {
            Notification::make()->title('Sin permiso')->body('No tienes permiso para eliminar archivos.')->danger()->send();
            return;
        }

        if (!is_file($path)) {
            Notification::make()->title('Error')->body('El archivo no existe.')->danger()->send();
            return;
        }

        unlink($path);
        $this->invalidateCache(dirname($path));

        Notification::make()->title('Archivo eliminado')->success()->send();
        $this->loadCurrentDirectory();
    }

    public function downloadFile(string $path, string $fileName): void
    {
        try {
            if (!is_file($path)) {
                Notification::make()->title('Error')->body('El archivo no existe.')->danger()->send();
                return;
            }

            $contents = file_get_contents($path);
            $mimeType = mime_content_type($path);
            $dataUrl = "data:{$mimeType};base64," . base64_encode($contents);

            $this->dispatch('downloadFile', ['dataUrl' => $dataUrl, 'filename' => $fileName]);
            Notification::make()->title('Descarga exitosa')->body("{$fileName} descargado.")->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Error de descarga')->body($e->getMessage())->danger()->send();
        }
    }

    public function getFileUrl(string $path): string
    {
        $rootPath = config('filesystems.disks.filemanager.root');
        $relativePath = ltrim(str_replace($rootPath, '', $path), '/\\');
        return asset('filemanager-files/' . str_replace('\\', '/', $relativePath));
    }

    public function getFileCategoryLabel(string $mime): string
    {
        return match (true) {
            str_starts_with($mime, 'image/') => 'Imagen',
            str_contains($mime, 'pdf') => 'PDF',
            str_contains($mime, 'msword') || str_contains($mime, 'document') => 'Word',
            str_contains($mime, 'spreadsheet') || str_contains($mime, 'excel') => 'Excel',
            str_contains($mime, 'presentation') || str_contains($mime, 'powerpoint') => 'PowerPoint',
            str_contains($mime, 'text') => 'Texto',
            str_contains($mime, 'zip') || str_contains($mime, 'rar') || str_contains($mime, 'compressed') => 'Comprimido',
            str_contains($mime, 'video') => 'Video',
            str_contains($mime, 'audio') => 'Audio',
            default => 'Archivo',
        };
    }

    public function getFileBadgeClass(string $mime): string
    {
        return match (true) {
            str_starts_with($mime, 'image/') => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            str_contains($mime, 'pdf') => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            str_contains($mime, 'msword') || str_contains($mime, 'document') => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            str_contains($mime, 'spreadsheet') || str_contains($mime, 'excel') => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            str_contains($mime, 'presentation') || str_contains($mime, 'powerpoint') => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            str_contains($mime, 'text') => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
            str_contains($mime, 'zip') || str_contains($mime, 'rar') || str_contains($mime, 'compressed') => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
            str_contains($mime, 'video') => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
            str_contains($mime, 'audio') => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
            default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-600 dark:text-zinc-200',
        };
    }

    public function isImage(string $mime): bool
    {
        return str_starts_with($mime, 'image/');
    }

    protected function formatFileSize($bytes): string
    {
        $bytes = (int) $bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }

    public static function canAccess(): bool
    {
        return currentUserHasPermission('filemanager.view');
    }

    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    public function getViewData(): array
    {
        return [
            'readOnly' => $this->readOnly,
            'currentPath' => $this->currentPath,
            'rootPath' => $this->rootPath,
            'parentPath' => $this->parentPath,
            'breadcrumb' => $this->breadcrumb,
            'folders' => $this->folders,
            'files' => $this->fileList,
        ];
    }
}
