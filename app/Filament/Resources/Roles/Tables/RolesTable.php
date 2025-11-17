<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Models\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->counts(relationships: 'users')
                    ->sortable()
                    ->alignment('center')
                    ->weight('bold')
                    ->color('primary')
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(fn(Role $record) => $record->name === 'Administrador'),
                Action::make('Asignar')
                    ->icon(Heroicon::Tag)
                    ->form(function ($form, Role $record) {
                        return $form->schema([
                            Select::make('users')
                                ->label('Usuarios')
                                ->placeholder('Selecciona los usuarios para añadir este rol')
                                ->options(
                                    User::query()
                                        ->where('email', '!=', 'admin@example.com')
                                        ->where(function ($query) use ($record) {
                                            $query->whereNull('role_id')
                                                ->orWhere('role_id', '!=', $record->id);
                                        })
                                        ->select(['id', 'name'])
                                        ->get()
                                        ->pluck('name', 'id')
                                )
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->rules([
                                    'required',
                                    'array',
                                    'min:1'
                                ])
                                ->getSearchResultsUsing(function (string $search, Role $record) {
                                    return User::query()
                                        ->where('email', '!=', 'admin@example.com')
                                        ->where(function ($query) use ($record) {
                                            $query->whereNull('role_id')
                                                ->orWhere('role_id', '!=', $record->id);
                                        })
                                        ->where('name', 'like', "%{$search}%")
                                        ->select(['id', 'name'])
                                        ->limit(50)
                                        ->get()
                                        ->pluck('name', 'id');
                                })
                        ]);
                    })
                    ->action(function (Role $record, array $data) {
                        self::handleAssignAction($record, $data);
                    })
                    ->hidden(fn(Role $record) => $record->name === 'Administrador')
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function handleAssignAction(Role $record, array $data): void
    {
        if (isset($data['users']) && !empty($data['users'])) {
            User::whereIn('id', $data['users'])
                ->where('email', '!=', 'admin@example.com')
                ->update(['role_id' => $record->id]);
        }
    }
}