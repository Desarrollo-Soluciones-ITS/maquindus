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
                TextColumn::make('name')
                    ->searchable(),
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
                    ->fillForm(fn(Role $record): array => [
                        'users' => $record->users()
                            ->where('email', '!=', 'admin@example.com')
                            ->pluck('id')
                            ->toArray(),
                    ])
                    ->form([
                        Select::make('users')
                            ->label('Usuarios')
                            ->multiple()
                            ->searchable(['name', 'email'])
                            ->required()
                            ->placeholder('Seleccionar usuarios')
                            ->loadingMessage('Cargando usuarios...')
                            ->noSearchResultsMessage('No se encontraron usuarios con este nombre.')
                            ->searchPrompt('Busque uno o varios usuarios por su nombre...')
                            ->searchingMessage('Buscando usuarios...')
                            ->getSearchResultsUsing(function (string $search, Role $record) {
                                return User::query()
                                    ->where('email', '!=', 'admin@example.com')
                                    ->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                                    ->limit(50)
                                    ->pluck('name', 'id');
                            })
                            ->getOptionLabelsUsing(function (array $values, Role $record): array {
                                return User::query()
                                    ->whereIn('id', $values)
                                    ->where('email', '!=', 'admin@example.com')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                    ])
                    ->action(function (Role $record, array $data) {
                        $selectedUserIds = $data['users'] ?? [];

                        $adminEmail = 'admin@example.com';

                        $currentUserIds = User::where('role_id', $record->id)
                            ->where('email', '!=', $adminEmail)
                            ->pluck('id')
                            ->toArray();

                        $toAdd = array_diff($selectedUserIds, $currentUserIds);

                        $toRemove = array_diff($currentUserIds, $selectedUserIds);

                        if (!empty($toRemove)) {
                            User::whereIn('id', $toRemove)
                                ->where('email', '!=', $adminEmail)
                                ->update(['role_id' => null]);
                        }

                        if (!empty($toAdd)) {
                            User::whereIn('id', $toAdd)
                                ->where('email', '!=', $adminEmail)
                                ->update(['role_id' => $record->id]);
                        }
                    })
                    ->hidden(fn(Role $r) => $r->name === 'Administrador')
                    ->successNotificationTitle('Se asignó el rol a todos los usuarios seleccionados')
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}