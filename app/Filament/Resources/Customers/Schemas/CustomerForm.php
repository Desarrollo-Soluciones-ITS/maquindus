<?php

namespace App\Filament\Resources\Customers\Schemas;

use App\Filament\Inputs\PhoneInput;
use App\Models\Country;
use App\Rules\PreventIllegalCharacters;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
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
                    ->placeholder('Ej. J-87654321-0')
                    ->mask(RawJs::make(<<<'JS'
                        'J-99999999-9'
                    JS))
                    ->maxLength(12)
                    ->unique()
                    ->required(),
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Ej. Construcciones López CA')
                    ->rule(PreventIllegalCharacters::apply())
                    ->maxLength(80)
                    ->unique()
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->placeholder('Ej. info@clopez.com')
                    ->email()
                    ->unique()
                    ->maxLength(255)
                    ->required(),
                PhoneInput::make(),
                Select::make('country_id')
                    ->label('País')
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->optionsLimit(15)
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
                    ->hidden(fn(Get $get) => $get('country_id') !== $venId)
                    ->required(),
                Select::make('city_id')
                    ->label('Ciudad')
                    ->relationship(
                        name: 'city',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query, Get $get) =>
                        $query->where('state_id', '=', $get('state_id'))
                    )
                    ->hidden(fn(Get $get) => $get('country_id') !== $venId)
                    ->required(),
                TextInput::make('address')
                    ->label('Dirección')
                    ->placeholder('Ej. Calle 15, Avenida FG')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Ej. Empresa de construcción de urbanizaciones.')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }
}
