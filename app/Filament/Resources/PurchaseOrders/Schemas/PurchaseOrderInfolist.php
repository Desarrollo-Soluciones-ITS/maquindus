<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PurchaseOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('supplier.name')->label('Proveedor'),
                TextEntry::make('order_no')->label('Código de orden'),
                TextEntry::make('description')->label('Descripción'),
                TextEntry::make('equipment.name')->label('Equipos vinculados')->badge()->separator(', ')->columnSpanFull(),
                TextEntry::make('created_at')->label('Creado el'),
                TextEntry::make('updated_at')->label('Actualizado el'),
            ]);
    }
}
