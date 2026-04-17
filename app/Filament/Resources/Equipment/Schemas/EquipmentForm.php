<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Filament\Resources\PurchaseOrders\Schemas\PurchaseOrderForm;
use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Models\Supplier;
use App\Models\SupplierPurchaseOrder;
use App\Rules\PreventIllegalCharacters;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
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
                    ->placeholder('Ej. Bomba centrífuga vertical')
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
                Section::make('Órdenes de compra proveedor')
                    ->description('Selecciona las órdenes existentes vinculadas al equipo o crea una nueva.')
                    ->headerActions([
                        Action::make('createSupplierPurchaseOrder')
                            ->label('Nueva orden')
                            ->icon('heroicon-m-plus')
                            ->color('primary')
                            ->hidden(!currentUserHasPermission('purchase_orders.create'))
                            ->schema(PurchaseOrderForm::getComponents())
                            ->modalHeading('Crear orden de compra proveedor')
                            ->modalSubmitActionLabel('Crear orden')
                            ->successNotificationTitle('Orden creada correctamente')
                            ->action(function (array $data): void {
                                SupplierPurchaseOrder::updateOrCreate(
                                    ['order_no' => $data['order_no']],
                                    [
                                        'supplier_id' => $data['supplier_id'],
                                        'description' => $data['description'] ?? null,
                                    ],
                                );
                            }),
                    ])
                    ->schema([
                        Select::make('supplierPurchaseOrders')
                            ->label('Órdenes registradas')
                            ->relationship(
                                name: 'supplierPurchaseOrders',
                                titleAttribute: 'order_no',
                                modifyQueryUsing: fn(Builder $query) => $query
                                    ->with('supplier')
                                    ->orderByDesc('created_at')
                                    ->orderBy('order_no'),
                            )
                            ->getOptionLabelFromRecordUsing(fn(SupplierPurchaseOrder $record): string => collect([
                                $record->order_no,
                                $record->supplier?->name,
                            ])->filter()->join(' · '))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Selecciona una o varias órdenes registradas')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
