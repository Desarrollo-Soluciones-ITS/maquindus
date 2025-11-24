<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Filament\Inputs\PhoneInput;
use App\Models\Country;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        $venId = Country::venezuela()->id;

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
                PhoneInput::make(),
                Select::make('country_id')
                    ->label('País')
                    ->selectablePlaceholder(false)
                    ->relationship(
                        name: 'country',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->oldest()
                    )
                    ->live()
                    ->required()
                    ->default($venId),
                Select::make('state_id')
                    ->label('Estado')
                    ->relationship('state', 'name')
                    ->live()
                    ->hidden(fn (Get $get) => $get('country_id') !== $venId)
                    ->required(),
                Select::make('city_id')
                    ->label('Ciudad')
                    ->relationship(
                        name: 'city',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query, Get $get) =>
                        $query->where('state_id', '=', $get('state_id'))
                    )
                    ->hidden(fn (Get $get) => $get('country_id') !== $venId)
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
