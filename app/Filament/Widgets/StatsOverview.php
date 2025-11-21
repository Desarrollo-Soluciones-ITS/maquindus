<?php

namespace App\Filament\Widgets;

use App\Models\File;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Filament\Support\Icons\Heroicon;

class StatsOverview extends StatsOverviewWidget
{
    public function getColumnSpan(): array|int|string
    {
        return 'full';
    }

    protected function getStats(): array
    {
        try {
            $diskStats = $this->getDiskStats();
            $fileCount = $this->getFileCount();
            $usedSpace = File::formatBytes($this->getDocumentableSpace());
            $usedDiskByDocuments = $this->getSpacePercentage($fileCount);

            return [
                Stat::make('Espacio ocupado total (GB)', $usedSpace)
                    ->description('Del total del sistema')
                    ->descriptionIcon(Heroicon::Server),

                Stat::make('Espacio ocupado por documentos', $usedDiskByDocuments)
                    ->description('Basado en archivos almacenados')
                    ->descriptionIcon(Heroicon::Document),

                Stat::make('Cantidad archivos', $fileCount)
                    ->description('En almacenamiento público')
                    ->descriptionIcon(Heroicon::Folder),

                Stat::make('Espacio disponible en disco (GB)', $diskStats['free_gb'])
                    ->description('Espacio libre restante')
                    ->descriptionIcon(Heroicon::ComputerDesktop)
                    ->color($diskStats['free_gb'] < 10 ? 'danger' : 'success'),
            ];

        } catch (\Exception $e) {
            \Log::error('Error loading disk stats: ' . $e->getMessage());

            return [
                Stat::make('Error', 'No disponible')
                    ->color('danger'),
            ];
        }
    }
    private function getDiskStats(): array
    {
        return Cache::remember('disk_stats', 300, function () {
            $path = '/';

            $freeSpaceBytes = @disk_free_space($path);
            $totalSpaceBytes = @disk_total_space($path);

            if ($freeSpaceBytes === false || $totalSpaceBytes === false) {
                throw new \Exception('Unable to read disk space');
            }

            $usedSpaceBytes = $totalSpaceBytes - $freeSpaceBytes;

            return [
                'free_gb' => round($freeSpaceBytes / (1024 * 1024 * 1024), 2),
                'used_gb' => round($usedSpaceBytes / (1024 * 1024 * 1024), 2),
                'total_gb' => round($totalSpaceBytes / (1024 * 1024 * 1024), 2),
            ];
        });
    }

    private function getFileCount(): int
    {
        return Cache::remember('file_count', 600, function () {
            try {
                $files = Storage::disk('public')->allFiles('');
                return count($files);
            } catch (\Exception $e) {
                return 0;
            }
        });
    }

    /**
     * ! TODO - convertir a grafico de tortas agrupado por tipo de modelo
     * Espacio ocupado por documentos de Equipos (25 GB - 50%)
     * Espacio ocupado por documentos de Proyectos (10 GB - 20%)
     * Espacio ocupado por documentos de Repuestos (5 GB - 10%)
     * Espacio ocupado por documentos de Clientes (5 GB - 10%)
     * Espacio ocupado por documentos de Proveedores (2.5 GB - 5%)
     * Espacio ocupado por documentos de Contactos (2.5 GB - 5%)
     */
    private function getDocumentableSpace(): int
    {
        return Cache::remember('documents_space', 600, function () {
            try {
                return (int) File::sum('file_size') >> 20;
            } catch (\Exception $e) {
                return 0;
            }
        });
    }

    private function getSpacePercentage(int $fileCount): string
    {
        return $fileCount > 0 ? round(($fileCount / 1000) * 100, 2) . '%' : '0%';
    }
}