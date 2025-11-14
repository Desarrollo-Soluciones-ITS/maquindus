<?php

namespace App\Filament\Resources\People\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PersonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('fullname')
                    ->label('Nombre'),
                TextEntry::make('email')
                    ->label('Correo electrónico'),
                TextEntry::make('phone')
                    ->label('Teléfono'),
                TextEntry::make('personable.name')
                    ->label('Empresa relacionada')
                    ->placeholder('N/A'),
                TextEntry::make('position')
                    ->label('Cargo')
                    ->placeholder('N/A'),
                TextEntry::make('country.name')
                    ->label('País'),
                TextEntry::make('state.name')
                    ->label('Estado')
                    ->placeholder('N/A'),
                TextEntry::make('city.name')
                    ->label('Ciudad')
                    ->placeholder('N/A'),
                TextEntry::make('address')
                    ->label('Dirección'),
            ]);
    }
}
