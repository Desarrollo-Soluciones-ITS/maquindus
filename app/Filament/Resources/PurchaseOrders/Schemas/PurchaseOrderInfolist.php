<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PurchaseOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order_no')->label('N° de Orden'),
                TextEntry::make('description')->label('Descripción'),
                TextEntry::make('project.name')->label('Proyecto'),
                TextEntry::make('created_at')->label('Creado el'),
                TextEntry::make('updated_at')->label('Actualizado el'),
            ]);
    }
}
