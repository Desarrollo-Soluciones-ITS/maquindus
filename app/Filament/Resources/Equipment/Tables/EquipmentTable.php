<?php

namespace App\Filament\Resources\Equipment\Tables;

use App\Filament\Filters\DateFilter;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Filters\ArchivedFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('equipments.show')),
                    EditAction::make()->hidden(!currentUserHasPermission('equipments.edit')),
                    ArchiveAction::make()->hidden(fn($record) => $record->trashed() || currentUserHasPermission('equipments.delete')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}
