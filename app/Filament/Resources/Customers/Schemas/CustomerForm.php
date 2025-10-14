<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('rif')
                    ->label('RIF')
                    ->placeholder('J-87654321-0')
                    ->required(),
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Construcciones López C.A.')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->placeholder('info@clopez.com')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->placeholder('02129876543')
                    ->tel()
                    ->required(),
                Select::make('state_id')
                    ->label('Estado')
                    ->relationship('state', 'name')
                    ->live()
                    ->required(),
                Select::make('city_id')
                    ->label('Ciudad')
                    ->relationship(
                        name: 'city',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, Get $get) =>
                            $query->where('state_id', '=', $get('state_id'))
                    )
                    ->required(),
                TextInput::make('address')
                    ->label('Dirección')
                    ->placeholder('Calle 15, Avenida FG')
                    ->required(),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Empresa de construcción de urbanizaciones.')
                    ->default(null),
            ]);
    }
}
