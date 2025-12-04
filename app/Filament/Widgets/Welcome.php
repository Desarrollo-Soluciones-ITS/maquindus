<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Welcome extends StatsOverviewWidget
{
  public function getColumnSpan(): array|int|string
  {
    return 4;
  }

  protected function getColumns(): int|array
  {
    return 4;
  }

  protected function getStats(): array
  {
    $user = auth()->user();
    return [
      Stat::make('Bienvenido', $user->name),
    ];
  }
}