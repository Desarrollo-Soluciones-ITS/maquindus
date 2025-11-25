<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),
                TextColumn::make('role.name')
                    ->label('Rol')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->sortable()
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('users.show')),
                    EditAction::make()->hidden(!currentUserHasPermission('users.edit'))
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}
