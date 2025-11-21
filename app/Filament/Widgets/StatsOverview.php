<?php

namespace App\Filament\Widgets;

use App\Models\File;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Filament\Support\Icons\Heroicon;

class StatsOverview extends StatsOverviewWidget
{
    public function getColumnSpan(): array|int|string
    {
        return [
            'sm' => 1,
            'md' => 2,
        ];
    }

    protected function getColumns(): int|array
    {
        return [
            'sm' => 1,
            'md' => 2,
        ];
    }

    protected function getStats(): array
    {
        try {
            $diskStats = $this->getDiskStats();
            $freeSpace = $diskStats['free'];
            $totalSpace = $diskStats['total'];

            $usedBytes = $this->getDocumentableSpace();
            $usedSpaceFormatted = File::formatBytes($usedBytes);
            $freeSpaceFormatted = File::formatBytes($freeSpace);

            $usedSpacePercentage = $totalSpace > 0
                ? round(($usedBytes / $totalSpace) * 100, 2)
                : 0;

            return [
                Stat::make('Espacio total ocupado', $usedSpaceFormatted)
                    ->description('Del total del sistema')
                    ->descriptionIcon(Heroicon::Server),

                Stat::make('Cantidad archivos', $this->getFileCount())
                    ->description('Registrados en el sistema')
                    ->descriptionIcon(Heroicon::Folder),

                Stat::make('Espacio disponible en disco', $freeSpaceFormatted)
                    ->description('Espacio libre restante')
                    ->descriptionIcon(Heroicon::ComputerDesktop)
                    ->color($freeSpace < 10 ? 'danger' : 'success'),

                Stat::make('Espacio ocupado', "{$usedSpacePercentage}%")
                    ->description('Dentro del servidor')
                    ->descriptionIcon(Heroicon::Cloud),
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

            return [
                'free' => $freeSpaceBytes,
                'total' => $totalSpaceBytes,
            ];
        });
    }

    private function getFileCount(): int
    {
        return Cache::remember('file_count', 300, function () {
            try {
                return File::count('id');
            } catch (\Exception $e) {
                return 0;
            }
        });
    }

    private function getDocumentableSpace(): int
    {
        return Cache::remember('documents_space', 300, function () {
            try {
                return File::sum('file_size');
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