<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\SupplierPurchaseOrder;
use App\Rules\PreventIllegalCharacters;
use Filament\Forms\Components\TagsInput;
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
                TextInput::make('year')
                    ->label('Año')
                    ->numeric()
                    ->minValue(1900)
                    ->maxValue((int) date('Y') + 10)
                    ->required(),
                TextInput::make('about')
                    ->label('Descripción')
                    ->placeholder('Ej. Compresor centrífugo')
                    ->maxLength(255)
                    ->required(),
                TagsInput::make('project_names')
                    ->label('Nombre de proyecto')
                    ->suggestions(fn(): array => Project::query()->orderBy('name')->pluck('name')->all())
                    ->nestedRecursiveRules(['string', 'max:80'])
                    ->reorderable(false)
                    ->splitKeys(['Tab', ','])
                    ->columnSpanFull(),
                Select::make('suppliers')
                    ->label('Proveedores vinculados')
                    ->relationship('suppliers', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                TagsInput::make('client_purchase_orders')
                    ->label('Órdenes de compra cliente')
                    ->suggestions(fn(): array => PurchaseOrder::query()->orderBy('order_no')->pluck('order_no')->all())
                    ->nestedRecursiveRules(['string', 'max:80'])
                    ->reorderable(false)
                    ->splitKeys(['Tab', ','])
                    ->columnSpanFull(),
                TagsInput::make('supplier_purchase_orders')
                    ->label('Órdenes de compra proveedor')
                    ->suggestions(fn(): array => SupplierPurchaseOrder::query()->orderBy('order_no')->pluck('order_no')->all())
                    ->nestedRecursiveRules(['string', 'max:80'])
                    ->reorderable(false)
                    ->splitKeys(['Tab', ','])
                    ->columnSpanFull(),
            ]);
    }
}
