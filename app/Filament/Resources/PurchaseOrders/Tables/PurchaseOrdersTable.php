<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_no')->label('N° de Orden')->searchable()->sortable(),
                TextColumn::make('description')->label('Descripción')->limit(40),
                TextColumn::make('project.name')->label('Proyecto')->searchable()->sortable(),
                TextColumn::make('created_at')->label('Creado el')->dateTime('d/m/Y H:i'),
            ])
            ->filters([])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\CreateAction::make(),
            ]);
    }
}
