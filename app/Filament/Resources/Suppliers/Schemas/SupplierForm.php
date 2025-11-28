<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Filament\Inputs\PhoneInput;
use App\Models\Country;
use App\Rules\PreventIllegalCharacters;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        $venId = Country::venezuela()->id;

        return $schema
            ->components([
                TextInput::make('rif')
                    ->label('RIF')
                    ->placeholder('Ej. J-07621321-0')
                    ->mask(RawJs::make(<<<'JS'
                        'J-99999999-9'
                    JS))
                    ->maxLength(12)
                    ->unique()
                    ->required()
                    ->afterStateUpdated(function ($state, $set) {
                        $trimmed = trim($state);
                        if ($trimmed !== $state) {
                            $set('rif', $trimmed);
                        }
                    }),
                TextInput::make('name')
                    ->label('Nombre')
                    ->placeholder('Ej. Suministros Industriales CA')
                    ->rule(PreventIllegalCharacters::apply())
                    ->maxLength(80)
                    ->unique()
                    ->required()
                    ->afterStateUpdated(function ($state, $set) {
                        $trimmed = trim($state);
                        if ($trimmed !== $state) {
                            $set('name', $trimmed);
                        }
                    }),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->placeholder('Ej. info@sumindus.com')
                    ->email()
                    ->unique()
                    ->maxLength(255)
                    ->required()
                    ->afterStateUpdated(function ($state, $set) {
                        $trimmed = trim($state);
                        if ($trimmed !== $state) {
                            $set('email', $trimmed);
                        }
                    }),
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
                    ->placeholder('Ej. Calle 10, Avenida MC')
                    ->maxLength(255)
                    ->required()
                    ->afterStateUpdated(function ($state, $set) {
                        $trimmed = trim($state);
                        if ($trimmed !== $state) {
                            $set('address', $trimmed);
                        }
                    }),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Ej. Empresa de suministro de equipamiento.')
                    ->maxLength(255)
                    ->default(null)
                    ->afterStateUpdated(function ($state, $set) {
                        $trimmed = trim($state);
                        if ($trimmed !== $state) {
                            $set('about', $trimmed);
                        }
                    }),
            ]);
    }
}
