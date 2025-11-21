<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class LatestEquipments extends TableWidget
{
    protected static ?string $heading = 'Últimos equipos';

    protected function getCachedEquipments()
    {
        return Cache::remember('latest_equipments_widget', 120, function () {
            return Equipment::latest()
                ->limit(5)
                ->get(['id', 'code', 'about', 'name']);
        });
    }

    public function table(Table $table): Table
    {
        $equipments = $this->getCachedEquipments();

        return $table
            ->query(
                fn(): Builder => Equipment::whereIn('id', $equipments->pluck('id'))
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
                fn(Equipment $record): string => route('filament.dashboard.resources.equipment.view', ['record' => $record]),
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
