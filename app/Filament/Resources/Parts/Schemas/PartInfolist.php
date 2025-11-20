<?php

namespace App\Filament\Resources\Parts\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PartInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('name')
                    ->label('Modelo'),
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
