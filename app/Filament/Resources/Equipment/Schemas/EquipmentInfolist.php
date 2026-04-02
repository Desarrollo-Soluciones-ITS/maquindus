<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EquipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('name')
                    ->label('Equipo'),
                TextEntry::make('model')
                    ->label('Modelo'),
                TextEntry::make('serial')
                    ->label('Serial'),
                TextEntry::make('type')
                    ->label('Tipo'),
                TextEntry::make('manufacturing_date')
                    ->label('Fecha de fabricación')
                    ->date('d/m/Y'),
                TextEntry::make('about')
                    ->label('Descripción'),
                TextEntry::make('linked_suppliers')
                    ->label('Proveedores')
                    ->state(fn($record) => $record->suppliers->pluck('name')->join(', ') ?: 'Sin proveedores vinculados')
                    ->columnSpanFull(),
                RepeatableEntry::make('supplierPurchaseOrders')
                    ->label('Órdenes compra proveedor')
                    ->grid(1)
                    ->contained()
                    ->schema([
                        TextEntry::make('supplier.name')
                            ->label('Proveedor')
                            ->badge()
                            ->color('info'),
                        TextEntry::make('order_no')
                            ->label('Código')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('description')
                            ->label('Descripción')
                            ->placeholder('Sin descripción')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            
            ]);
    }
}
