<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Filament\Resources\PurchaseOrders\Schemas\PurchaseOrderForm;
use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Models\Supplier;
use App\Rules\PreventIllegalCharacters;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

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
                    ->unique()
                    ->required(),
                TextInput::make('model')
                    ->label('Modelo')
                    ->placeholder('Ej. MODELOX123')
                    ->alphaNum()
                    ->minLength(10)
                    ->maxLength(80)
                    ->required(),
                TextInput::make('serial')
                    ->label('Serial')
                    ->placeholder('Ej. SERIAL0001')
                    ->alphaNum()
                    ->minLength(10)
                    ->maxLength(80)
                    ->required(),
                TextInput::make('type')
                    ->label('Tipo')
                    ->placeholder('Ej. INDUSTRIAL1')
                    ->alphaNum()
                    ->minLength(10)
                    ->maxLength(80)
                    ->required(),
                DatePicker::make('manufacturing_date')
                    ->label('Fecha de fabricación')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->maxDate(now())
                    ->required(),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Ej. Compresor centrífugo')
                    ->maxLength(255)
                    ->required(),
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
                Repeater::make('supplier_purchase_orders_data')
                    ->label('Órdenes de compra proveedor')
                    ->schema(PurchaseOrderForm::getComponents(shouldValidateUniqueness: false))
                    ->columns(2)
                    ->defaultItems(0)
                    ->cloneable()
                    ->reorderable(false)
                    ->addActionLabel('Agregar orden')
                    ->addActionAlignment(Alignment::End)
                    ->addAction(fn (Action $action) => $action
                        ->icon('heroicon-m-plus')
                        ->color('primary'))
                    ->columnSpanFull(),
            ]);
    }
}
