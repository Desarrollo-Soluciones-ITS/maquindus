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
                TextEntry::make('part_number')
                    ->label('N° de parte'),
                TextEntry::make('catalog_number')
                    ->label('N° de catálogo'),
                TextEntry::make('customer_part_number')
                    ->label('N° de parte del cliente'),
                TextEntry::make('about')
                    ->label('Descripción')
                    ->columnSpanFull(),
               ]);
    }
}
