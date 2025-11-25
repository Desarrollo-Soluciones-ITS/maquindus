<?php

namespace App\Filament\Resources\Suppliers\Tables;

use Filament\Actions\ActionGroup;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Filters\ArchivedFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rif')
                    ->label('RIF')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
            ])
            ->filters([
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('suppliers.show')),
                    EditAction::make()->hidden(!currentUserHasPermission('suppliers.edit')),
                    ArchiveAction::make()->hidden(!currentUserHasPermission('suppliers.delete')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}
