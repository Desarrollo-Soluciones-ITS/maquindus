<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Filters\DateFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->sortable(),
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
                DateFilter::make(),
                SelectFilter::make('role.name')
                    ->label('Rol')
                    ->relationship('role', 'name'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('users.show')),
                    EditAction::make()->hidden(!currentUserHasPermission('users.edit')),
                    DeleteAction::make()->hidden(function (Model $record) {
                        $nodelete = $record->id === Auth::user()->id
                            || $record->role->name === 'Administrador';
                        return $nodelete || !currentUserHasPermission('users.delete');
                    }),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}
