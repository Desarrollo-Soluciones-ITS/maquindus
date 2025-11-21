<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EquipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('code')
                    ->label('Código'),
                TextEntry::make('about')
                    ->label('Descripción'),
                KeyValueEntry::make('details')
                    ->label('Características')
                    ->columnSpanFull(),
            ]);
    }
}
