<?php

namespace App\Filament\Widgets;

use App\Models\Part;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class LatestParts extends TableWidget
{
    protected static ?string $heading = 'Últimas partes';

    protected function getCachedParts()
    {
        return Cache::remember('latest_parts_widget', 120, function () {
            return Part::latest()
                ->limit(5)
                ->get(['id', 'code', 'about', 'name']);
        });
    }

    public function table(Table $table): Table
    {
        $parts = $this->getCachedParts();

        return $table
            ->query(
                fn(): Builder => Part::whereIn('id', $parts->pluck('id'))
            )
            ->columns([
                TextColumn::make('code')
                    ->label('Código'),
                TextColumn::make('name')
                    ->label('Nombre'),
                TextColumn::make('about')
                    ->label('Descripción'),
            ])
            ->paginated(false)
            ->recordUrl(
                fn(Part $record): string => route('filament.dashboard.resources.parts.view', ['record' => $record]),
            )
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
