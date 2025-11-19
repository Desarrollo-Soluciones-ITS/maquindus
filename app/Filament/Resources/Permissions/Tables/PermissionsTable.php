<?php

namespace App\Filament\Resources\Permissions\Tables;

use App\Models\Permission;
use App\Models\Role;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('Asignar')
                    ->icon(Heroicon::Tag)
                    ->fillForm(fn(Permission $record): array => [
                        'roles' => $record->roles()->pluck('id')->toArray(),
                    ])
                    ->form([
                        Select::make('roles')
                            ->label('Roles')
                            ->multiple()
                            ->searchable(['name'])
                            ->required()
                            ->placeholder('Seleccionar roles')
                            ->loadingMessage('Cargando roles...')
                            ->noSearchResultsMessage('No se encontraron roles.')
                            ->searchPrompt('Busque roles por nombre...')
                            ->searchingMessage('Buscando...')
                            ->getSearchResultsUsing(function (string $search) {
                                return Role::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'id');
                            })
                            ->getOptionLabelsUsing(
                                fn(array $values): array =>
                                Role::query()
                                    ->whereIn('id', $values)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                    ])
                    ->action(function (Permission $record, array $data) {
                        $record->roles()->sync($data['roles'] ?? []);
                    })
                    ->successNotificationTitle('Permiso asignado a los roles seleccionados')

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
