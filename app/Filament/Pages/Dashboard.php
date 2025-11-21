<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DocumentsChart;
use App\Filament\Widgets\LatestActivityLogs;
use App\Filament\Widgets\LatestDocuments;
use App\Filament\Widgets\LatestEquipments;
use App\Filament\Widgets\LatestParts;
use App\Filament\Widgets\LatestProjects;
use App\Filament\Widgets\StatsOverview;
use Filament\Pages\Page;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Dashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

    protected static ?string $title = 'Inicio';

    protected static ?int $navigationSort = -2;

    public function getColumns(): int|array
    {
        return 4;
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 4;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            'stats' => StatsOverview::class,
            'chart' => DocumentsChart::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LatestActivityLogs::class,
            LatestEquipments::class,
            LatestParts::class,
            LatestProjects::class,
            LatestDocuments::class,
        ];
    }

    public static function canAccess(): bool
    {
        return currentUserHasPermission('dashboard');
    }
}