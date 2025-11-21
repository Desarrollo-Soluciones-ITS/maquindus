<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class LatestActivityLogs extends TableWidget
{
    protected static ?string $heading = 'Acciones recientes';

    public function getColumnSpan(): array|int|string
    {
        return 'full';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => Activity::query()->latest()->limit(5))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas'),
                TextColumn::make('log_name')
                    ->label('Módulo')
                    ->badge(),
                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => translate_activity_event($state))
                    ->color(fn(string $state): string => get_activity_color($state)),
                TextColumn::make('description')
                    ->label('Descripción'),
                TextColumn::make('causer.name')
                    ->label('Causado por')
                    ->default('Sistema'),
            ])
            ->paginated(false)
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
