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
            $freeSpace = $this->getDiskStats();
            $fileCount = $this->getFileCount();
            $freeSpaceFormatted = File::formatBytes($freeSpace);
            $usedSpace = File::formatBytes($this->getDocumentableSpace());

            return [
                Stat::make('Espacio ocupado total', $usedSpace)
                    ->description('Del total del sistema')
                    ->descriptionIcon(Heroicon::Server),

                Stat::make('Cantidad archivos', $fileCount)
                    ->description('En almacenamiento público')
                    ->descriptionIcon(Heroicon::Folder),

                Stat::make('Espacio disponible en disco', $freeSpaceFormatted)
                    ->description('Espacio libre restante')
                    ->descriptionIcon(Heroicon::ComputerDesktop)
                    ->color($freeSpace < 10 ? 'danger' : 'success'),
            ];

        } catch (\Exception $e) {
            \Log::error('Error loading disk stats: ' . $e->getMessage());

            return [
                Stat::make('Error', 'No disponible')
                    ->color('danger'),
            ];
        }
    }
    private function getDiskStats(): int
    {
        return Cache::remember('disk_stats', 300, function () {
            $path = '/';

            $freeSpaceBytes = @disk_free_space($path);
            $totalSpaceBytes = @disk_total_space($path);

            if ($freeSpaceBytes === false || $totalSpaceBytes === false) {
                throw new \Exception('Unable to read disk space');
            }

            return $freeSpaceBytes;
        });
    }

    private function getFileCount(): int
    {
        return Cache::remember('file_count', 300, function () {
            try {
                $files = Storage::disk('public')->allFiles('');
                return count($files);
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