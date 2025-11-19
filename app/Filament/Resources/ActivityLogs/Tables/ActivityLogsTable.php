<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
                    ->sortable(),
                TextColumn::make('log_name')
                    ->label('Módulo')
                    ->badge(),
                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => translate_activity_event($state))
                    ->color(fn(string $state): string => get_activity_color($state))
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Descripción'),
                TextColumn::make('causer.name')
                    ->label('Causado por')
                    ->default('Sistema'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ])
            ])
            ->defaultSort('created_at', 'desc');
    }
}
