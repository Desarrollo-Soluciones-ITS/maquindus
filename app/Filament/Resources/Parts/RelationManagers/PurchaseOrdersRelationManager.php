<?php

namespace App\Filament\Resources\Parts\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class PurchaseOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseOrders';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('order_no')->label('N° de Orden'),
                TextColumn::make('description')->label('Descripción'),
                TextColumn::make('created_at')->label('Creado el')->dateTime(),
            ]);
    }
}
