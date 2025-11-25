<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->sortable()
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas'),
                TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->sortable()
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
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
                    ->schema([
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
                            }),
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
                    ->successNotificationTitle('Se asignó el rol a todos los usuarios seleccionados'),

                Action::make('permissions')
                    ->label('Sincronizar permisos')
                    ->icon(Heroicon::LockClosed)
                    ->color('info')
                    ->fillForm(fn(Role $record): array => [
                        'permissions' => $record->permissions->pluck('id')->toArray(),
                    ])
                    ->schema([
                        Select::make('permissions')
                            ->label('Permisos')
                            ->multiple()
                            ->searchable()
                            ->placeholder('Seleccionar permisos...')
                            ->options(function () {
                                return Permission::orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->required(false),
                    ])
                    ->action(function (Role $record, array $data) {
                        $permissionIds = $data['permissions'] ?? [];
                        if (empty($permissionIds)) {

                            Notification::make()
                                ->title('Datos no encontrados')
                                ->body('No se seleccionaron permisos a sincronizar')
                                ->info()
                                ->send();
                        } else {
                            $record->permissions()->sync($permissionIds);

                            $totalPermissions = count($permissionIds);

                            Notification::make()
                                ->title('Rol actualizado')
                                ->body(Str::markdown("Se sincronizaron **$totalPermissions** permisos correctamente"))
                                ->success()
                                ->send();
                        }

                    })
                    ->hidden(fn(Role $r) => $r->name === 'Administrador'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                ]),
            ]);
    }
}
