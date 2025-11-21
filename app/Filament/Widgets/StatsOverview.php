<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use BackedEnum;
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

            return [
                Stat::make('Espacio ocupado total (GB)', $diskStats['used_gb'])
                    ->description('Del total del sistema')
                    ->descriptionIcon(Heroicon::Server),

                Stat::make('Espacio ocupado por documentable', $this->getDocumentableSpace($fileCount))
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

    private function getDocumentableSpace(int $fileCount): string
    {
        // Replace with your actual business logic
        return $fileCount > 0 ? round(($fileCount / 1000) * 100, 2) . '%' : '0%';
    }
}