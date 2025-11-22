<?php

namespace App\Filament\Resources\Equipment\Tables;

use App\Filament\Filters\DateFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->filtersLayout(FiltersLayout::Modal)
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('about')
                    ->label('Descripción'),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->sortable(is_not_relation_manager())
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas'),
            ])
            ->filters([
                DateFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('equipments.show')),
                    EditAction::make()->hidden(!currentUserHasPermission('equipments.edit')),
                    DeleteAction::make()->hidden(!currentUserHasPermission('equipments.delete')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->hidden(!currentUserHasPermission('equipments.delete')),
                ]),
            ]);
    }
}
