<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('rif')
                    ->label('RIF'),
                TextEntry::make('name')
                    ->label('Nombre'),
                TextEntry::make('email')
                    ->label('Correo electrónico'),
                TextEntry::make('phone')
                    ->label('Teléfono'),
                TextEntry::make('state.name')
                    ->label('Estado'),
                TextEntry::make('city.name')
                    ->label('Ciudad'),
                TextEntry::make('address')
                    ->label('Dirección'),
                TextEntry::make('about')
                    ->placeholder('N/A')
                    ->label('Descripción'),
            ]);
    }
}
