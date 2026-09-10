<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Models\Supplier;
use App\Rules\PreventIllegalCharacters;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('name')
                    ->label('Equipo')
                    ->placeholder('Ej. Compresor Atlas')
                    ->rule(PreventIllegalCharacters::apply())
                    ->maxLength(80)
                    ->unique(),
                TextInput::make('model')
                    ->label('Modelo')
                    ->placeholder('Ej. MODELOX123')
                    ->alphaNum()
                    ->minLength(10)
                    ->maxLength(80),
                TextInput::make('serial')
                    ->label('Serial')
                    ->placeholder('Ej. SERIAL0001')
                    ->alphaNum()
                    ->minLength(10)
                    ->maxLength(80),
                TextInput::make('type')
                    ->label('Tipo')
                    ->placeholder('Ej. Bomba centrífuga vertical')
                    ->maxLength(80),
                DatePicker::make('manufacturing_date')
                    ->label('Fecha de fabricación')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(now()),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Ej. Compresor centrífugo')
                    ->maxLength(255),
                Select::make('suppliers')
                    ->label('Proveedores vinculados')
                    ->relationship('suppliers', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->placeholder('Selecciona uno o varios proveedores')
                    ->createOptionForm(SupplierForm::getComponents())
                    ->createOptionUsing(fn(array $data): string => Supplier::create($data)->getKey()),
            ]);
    }
}