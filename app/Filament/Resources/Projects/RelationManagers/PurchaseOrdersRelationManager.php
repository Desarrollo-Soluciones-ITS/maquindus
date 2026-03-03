<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
            ])
            ->filters([
                // Puedes agregar filtros aquí si lo necesitas
            ]);
    }
}
