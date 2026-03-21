<?php

namespace App\Filament\Resources\Equipment\Schemas;

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
                TextEntry::make('year')
                    ->label('Año'),
                TextEntry::make('about')
                    ->label('Descripción'),
                TextEntry::make('linked_projects')
                    ->label('Proyectos')
                    ->state(fn($record) => $record->projects->pluck('name')->join(', ') ?: 'Sin proyectos vinculados')
                    ->columnSpanFull(),
                TextEntry::make('linked_suppliers')
                    ->label('Proveedores')
                    ->state(fn($record) => $record->suppliers->pluck('name')->join(', ') ?: 'Sin proveedores vinculados')
                    ->columnSpanFull(),
                TextEntry::make('linked_client_orders')
                    ->label('Órdenes compra cliente')
                    ->state(fn($record) => $record->purchaseOrders->pluck('order_no')->join(', ') ?: 'Sin órdenes vinculadas')
                    ->columnSpanFull(),
                TextEntry::make('linked_supplier_orders')
                    ->label('Órdenes compra proveedor')
                    ->state(fn($record) => $record->supplierPurchaseOrders->pluck('order_no')->join(', ') ?: 'Sin órdenes vinculadas')
                    ->columnSpanFull(),
            
            ]);
    }
}
