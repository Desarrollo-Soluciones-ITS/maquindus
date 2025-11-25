<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle del Evento')
                    ->schema([
                        TextEntry::make('log_name')
                            ->label('Módulo')
                            ->badge(),
                        TextEntry::make('event')
                            ->label('Evento')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => translate_activity_event($state))
                            ->color(fn(string $state): string => get_activity_color($state)),
                        TextEntry::make('description')
                            ->label('Descripción'),
                        TextEntry::make('subject.name')
                            ->label('Registro afectado'),
                        TextEntry::make('causer.name')
                            ->label('Causado por')
                            ->default('Sistema'),
                        TextEntry::make('created_at')
                            ->label('Fecha')
                            ->date('d/m/Y - g:i A')
                            ->timezone('America/Caracas'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
