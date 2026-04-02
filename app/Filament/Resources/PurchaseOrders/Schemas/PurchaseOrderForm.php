<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Models\Supplier;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function getComponents(bool $shouldValidateUniqueness = true, bool $hideSupplier = false, mixed $defaultSupplierId = null): array
    {
        $orderNo = TextInput::make('order_no')
            ->label('Código de orden')
            ->placeholder('Ej. OC-PROV-001')
            ->minLength(3)
            ->maxLength(80)
            ->required();

        if ($shouldValidateUniqueness) {
            $orderNo->unique(ignoreRecord: true);
        }

        return [
            Select::make('supplier_id')
                ->label('Proveedor')
                ->options(fn(): array => Supplier::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->preload()
                ->native(false)
                ->required()
                ->hidden($hideSupplier)
                ->default($defaultSupplierId)
                ->createOptionForm(SupplierForm::getComponents())
                ->createOptionUsing(fn(array $data): string => Supplier::create($data)->getKey()),
            $orderNo,
            TextInput::make('description')
                ->label('Descripción')
                ->placeholder('Descripción de la orden')
                ->maxLength(255),
        ];
    }

    public static function getBulkComponents(): array
    {
        return [
            Repeater::make('orders')
                ->label('Órdenes de compra proveedor')
                ->schema(static::getComponents(shouldValidateUniqueness: false))
                ->columns(2)
                ->defaultItems(1)
                ->minItems(1)
                ->cloneable()
                ->reorderable(false)
                ->addActionLabel('Agregar orden')
                ->columnSpanFull(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(static::getComponents());
    }
}
