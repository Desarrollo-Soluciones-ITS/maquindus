<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DocumentsChart;
use App\Filament\Widgets\LatestActivityLogs;
use App\Filament\Widgets\LatestDocuments;
use App\Filament\Widgets\LatestEquipments;
use App\Filament\Widgets\LatestParts;
use App\Filament\Widgets\LatestProjects;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\Welcome;
use Filament\Pages\Page;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Support\Arr;

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
        if (currentUserHasPermission('dashboard')) {
            return [
                'stats' => StatsOverview::class,
                'chart' => DocumentsChart::class,
            ];
        }

        return [Welcome::class];
    }

    protected function getFooterWidgets(): array
    {
        $widgets = [];

        if (currentUserHasPermission('dashboard.view')) {
            $widgets[] = LatestActivityLogs::class;
        }

        if (currentUserHasPermission('equipments.view')) {
            $widgets[] = LatestEquipments::class;
        }

        if (currentUserHasPermission('parts.view')) {
            $widgets[] = LatestParts::class;
        }

        if (currentUserHasPermission('projects.view')) {
            $widgets[] = LatestProjects::class;
        }

        if (currentUserHasPermission('documents.view')) {
            $widgets[] = LatestDocuments::class;
        }

        return $widgets;

    }
}