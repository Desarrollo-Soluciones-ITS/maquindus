<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Filament\Filters\DateFilter;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use App\Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
                    ->searchable()
                    ->sortable(),
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
                DateFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('Asignar rol')
                        ->icon('heroicon-o-tag')
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

                            if (!empty($toAdd)) {
                                User::whereIn('id', $toAdd)->update(['role_id' => $record->id]);
                            }

                            if (!empty($toRemove)) {
                                User::whereIn('id', $toRemove)->update(['role_id' => null]);
                            }

                            $totalAdded = count($toAdd);
                            $totalRemoved = count($toRemove);

                            Notification::make()
                                ->title('Usuarios actualizados')
                                ->body(Str::markdown("Se asignaron **{$totalAdded}** usuarios y se removieron **{$totalRemoved}** usuarios."))
                                ->success()
                                ->send();
                        })
                        ->successNotificationTitle('Se asignó el rol a todos los usuarios seleccionados')
                        ->hidden(fn($record) => $record->name === 'Administrador'),

                    Action::make('permissions')
                        ->label('Editar permisos')
                        ->icon('heroicon-o-lock-closed')
                        ->modalWidth('7xl')
                        ->fillForm(function (Role $record): array {
                            $permissionIds = $record->permissions->pluck('id')->toArray();

                            $formData = [];
                            $tree = Permission::$permissions;
                            $permissionSlugs = Permission::pluck('slug', 'id')->toArray();
                            $slugToId = array_flip($permissionSlugs);

                            foreach ($tree as $key => $value) {
                                if (is_string($key) && is_array($value)) {
                                    $fieldName = "permissions_group_$key";
                                    $formData[$fieldName] = [];

                                    foreach ($value as $action) {
                                        if (!is_string($key) || !is_string($action))
                                            continue;
                                        $slug = "$key.$action";
                                        if (isset($slugToId[$slug]) && in_array($slugToId[$slug], $permissionIds)) {
                                            $formData[$fieldName][] = $slugToId[$slug];
                                        }
                                    }
                                } elseif ($key === 'relationships') {
                                    foreach ($value as $relatedModel => $actions) {
                                        $fieldName = "permissions_group_relationships_$relatedModel";
                                        $formData[$fieldName] = [];

                                        foreach ($actions as $action) {
                                            if (!is_string($relatedModel) || !is_string($action))
                                                continue;
                                            $slug = "relationships.$relatedModel.$action";
                                            if (isset($slugToId[$slug]) && in_array($slugToId[$slug], $permissionIds)) {
                                                $formData[$fieldName][] = $slugToId[$slug];
                                            }
                                        }
                                    }
                                } elseif (is_string($value)) {
                                    $fieldName = "permissions_group_general";
                                    if (!isset($formData[$fieldName])) {
                                        $formData[$fieldName] = [];
                                    }
                                    $slug = $value;
                                    if (isset($slugToId[$slug]) && in_array($slugToId[$slug], $permissionIds)) {
                                        $formData[$fieldName][] = $slugToId[$slug];
                                    }
                                }
                            }

                            return $formData;
                        })
                        ->schema(function () {
                            $tree = Permission::$permissions;
                            $permissionOptions = Permission::orderBy('name')->pluck('name', 'id')->toArray();
                            $permissionSlugs = Permission::pluck('slug', 'id')->toArray();
                            $slugToId = array_flip($permissionSlugs);

                            $groups = [];

                            foreach ($tree as $key => $value) {
                                if (is_string($key) && is_array($value)) {
                                    $groupLabel = ucfirst(Permission::$resourceLabels[$key] ?? $key);
                                    $options = [];
                                    foreach ($value as $action) {
                                        if (!is_string($key) || !is_string($action))
                                            continue;
                                        $slug = "$key.$action";
                                        if (isset($slugToId[$slug])) {
                                            $options[$slugToId[$slug]] = $permissionOptions[$slugToId[$slug]] ?? $slug;
                                        }
                                    }
                                    if (!empty($options)) {
                                        $groups[$groupLabel] = CheckboxList::make("permissions_group_$key")
                                            ->label($groupLabel)
                                            ->selectAllAction(
                                                fn(Action $action) => $action->label('Seleccionar todos')
                                            )
                                            ->deselectAllAction(
                                                fn(Action $action) => $action->label('Limpiar selección')
                                            )
                                            ->options($options)
                                            ->columns(1)
                                            ->bulkToggleable()
                                            ->extraAttributes(['class' => 'permission-group', 'data-group-label' => strtolower($groupLabel)]);
                                    }
                                } elseif ($key === 'relationships') {
                                    foreach ($value as $relatedModel => $actions) {
                                        $relatedLabel = Permission::$resourceLabels[$relatedModel] ?? $relatedModel;
                                        $groupLabel = "Relaciones de " . ucfirst($relatedLabel);
                                        $options = [];
                                        foreach ($actions as $action) {
                                            if (!is_string($relatedModel) || !is_string($action))
                                                continue;
                                            $slug = "relationships.$relatedModel.$action";
                                            if (isset($slugToId[$slug])) {
                                                $options[$slugToId[$slug]] = $permissionOptions[$slugToId[$slug]] ?? $slug;
                                            }
                                        }
                                        if (!empty($options)) {
                                            $groups[$groupLabel] = CheckboxList::make("permissions_group_relationships_$relatedModel")
                                                ->label($groupLabel)
                                                ->selectAllAction(
                                                    fn(Action $action) => $action->label('Seleccionar todos')
                                                )
                                                ->deselectAllAction(
                                                    fn(Action $action) => $action->label('Limpiar selección')
                                                )
                                                ->options($options)
                                                ->columns(1)
                                                ->bulkToggleable()
                                                ->extraAttributes(['class' => 'permission-group', 'data-group-label' => strtolower($groupLabel)]);
                                        }
                                    }
                                } elseif (is_string($value)) {
                                    if (!isset($groups['General'])) {
                                        $groups['General'] = CheckboxList::make('permissions_group_general')
                                            ->label('General')
                                            ->selectAllAction(
                                                fn(Action $action) => $action->label('Seleccionar todos')
                                            )
                                            ->deselectAllAction(
                                                fn(Action $action) => $action->label('Limpiar selección')
                                            )
                                            ->columns(1)
                                            ->bulkToggleable()
                                            ->extraAttributes(['class' => 'permission-group', 'data-group-label' => 'general']);
                                    }
                                }
                            }

                            $generalOptions = [];
                            foreach ($tree as $key => $value) {
                                if (is_string($value)) {
                                    $slug = $value;
                                    if (isset($slugToId[$slug])) {
                                        $generalOptions[$slugToId[$slug]] = $permissionOptions[$slugToId[$slug]] ?? $slug;
                                    }
                                }
                            }

                            if (!empty($generalOptions) && isset($groups['General'])) {
                                $groups['General'] = CheckboxList::make('permissions_group_general')
                                    ->label('General')
                                    ->selectAllAction(
                                        fn(Action $action) => $action->label('Seleccionar todos')
                                    )
                                    ->deselectAllAction(
                                        fn(Action $action) => $action->label('Limpiar selección')
                                    )
                                    ->options($generalOptions)
                                    ->columns(1)
                                    ->bulkToggleable()
                                    ->extraAttributes(['class' => 'permission-group', 'data-group-label' => 'general']);
                            }

                            $sectionComponents = [];
                            foreach ($groups as $label => $checkboxList) {
                                $sectionComponents[] = Section::make($label)
                                    ->schema([$checkboxList])
                                    ->collapsible()
                                    ->compact()
                                    ->extraAttributes([
                                        'class' => 'permission-section',
                                        'data-search-label' => strtolower($label),
                                    ]);
                            }

                            return [
                                // TODO -> comentado porque no funciona y por ahora no hay chance de arreglarlo
                                // TextInput::make('permission_search')
                                // ->label('Buscar permisos')
                                // ->placeholder('Escriba para filtrar permisos...')
                                // ->live()
                                // ->debounce(300)
                                // ->afterStateUpdated(function ($state, $set) {
                                //     $searchTerm = strtolower($state);
                                //     $script = "
                                //         const searchTerm = '{$searchTerm}';
                                //         const sections = document.querySelectorAll('.permission-section');
                
                                //         sections.forEach(section => {
                                //             const sectionHeader = section.querySelector('.fi-section-header-heading');
                                //             const sectionLabel = sectionHeader ? sectionHeader.textContent.toLowerCase() : '';
                                //             const permissionOptions = section.querySelectorAll('.fi-fo-checkbox-list-option-label');
                                //             let hasVisibleItems = false;
                
                                //             // Reset all options first
                                //             permissionOptions.forEach(optionLabel => {
                                //                 const optionContainer = optionLabel.closest('.fi-fo-checkbox-list-option-ctn');
                                //                 if (optionContainer) {
                                //                     optionContainer.style.display = 'flex';
                                //                 }
                                //             });
                
                                //             // Show section by default
                                //             section.style.display = 'block';
                
                                //             // Check if section header matches
                                //             if (sectionLabel.includes(searchTerm)) {
                                //                 hasVisibleItems = true;
                                //             } else {
                                //                 // Check individual options
                                //                 permissionOptions.forEach(optionLabel => {
                                //                     const labelText = optionLabel.textContent.toLowerCase();
                                //                     const optionContainer = optionLabel.closest('.fi-fo-checkbox-list-option-ctn');
                
                                //                     if (labelText.includes(searchTerm)) {
                                //                         hasVisibleItems = true;
                                //                         if (optionContainer) {
                                //                             optionContainer.style.display = 'flex';
                                //                         }
                                //                     } else if (optionContainer && searchTerm.length > 0) {
                                //                         optionContainer.style.display = 'none';
                                //                     }
                                //                 });
                                //             }
                
                                //             // Hide section if no matches found
                                //             if (!hasVisibleItems && searchTerm.length > 0) {
                                //                 section.style.display = 'none';
                                //             }
                                //         });
                                //     ";
                                //     $set('search_script', $script);
                                // })
                                // ->dehydrated(false),
                
                                Fieldset::make('Acciones rápidas')
                                    ->schema([
                                        Grid::make()
                                            ->columns(2)
                                            ->schema([
                                                Action::make('select_all_permissions')
                                                    ->label('Seleccionar todos los permisos')
                                                    ->button()
                                                    ->color('primary')
                                                    ->action(function ($livewire, $set) {
                                                        $tree = Permission::$permissions;
                                                        $permissionSlugs = Permission::pluck('slug', 'id')->toArray();
                                                        $slugToId = array_flip($permissionSlugs);

                                                        foreach ($tree as $key => $value) {
                                                            if (is_string($key) && is_array($value)) {
                                                                $fieldName = "permissions_group_$key";
                                                                $fieldPermissions = [];

                                                                foreach ($value as $action) {
                                                                    if (!is_string($key) || !is_string($action))
                                                                        continue;
                                                                    $slug = "$key.$action";
                                                                    if (isset($slugToId[$slug])) {
                                                                        $fieldPermissions[] = $slugToId[$slug];
                                                                        $allPermissionIds[] = $slugToId[$slug];
                                                                    }
                                                                }
                                                                $set($fieldName, $fieldPermissions);
                                                            } elseif ($key === 'relationships') {
                                                                foreach ($value as $relatedModel => $actions) {
                                                                    $fieldName = "permissions_group_relationships_$relatedModel";
                                                                    $fieldPermissions = [];

                                                                    foreach ($actions as $action) {
                                                                        if (!is_string($relatedModel) || !is_string($action))
                                                                            continue;
                                                                        $slug = "relationships.$relatedModel.$action";
                                                                        if (isset($slugToId[$slug])) {
                                                                            $fieldPermissions[] = $slugToId[$slug];
                                                                            $allPermissionIds[] = $slugToId[$slug];
                                                                        }
                                                                    }
                                                                    $set($fieldName, $fieldPermissions);
                                                                }
                                                            } elseif (is_string($value)) {
                                                                $fieldName = "permissions_group_general";
                                                                $slug = $value;
                                                                if (isset($slugToId[$slug])) {
                                                                    $allPermissionIds[] = $slugToId[$slug];
                                                                }
                                                            }
                                                        }

                                                        $generalPermissions = [];
                                                        foreach ($tree as $key => $value) {
                                                            if (is_string($value)) {
                                                                $slug = $value;
                                                                if (isset($slugToId[$slug])) {
                                                                    $generalPermissions[] = $slugToId[$slug];
                                                                }
                                                            }
                                                        }
                                                        if (!empty($generalPermissions)) {
                                                            $set('permissions_group_general', $generalPermissions);
                                                        }
                                                    }),

                                                Action::make('deselect_all_permissions')
                                                    ->label('Deseleccionar todos los permisos')
                                                    ->button()
                                                    ->color('danger')
                                                    ->action(function ($set) {
                                                        $tree = Permission::$permissions;

                                                        foreach ($tree as $key => $value) {
                                                            if (is_string($key) && is_array($value)) {
                                                                $set("permissions_group_$key", []);
                                                            } elseif ($key === 'relationships') {
                                                                foreach (array_keys($value) as $relatedModel) {
                                                                    $set("permissions_group_relationships_$relatedModel", []);
                                                                }
                                                            }
                                                        }
                                                        $set('permissions_group_general', []);
                                                    }),
                                            ])
                                    ]),

                                Grid::make(4)
                                    ->schema($sectionComponents),
                            ];
                        })
                        ->action(function (Role $record, array $data) {
                            $permissionIds = [];

                            foreach ($data as $key => $value) {
                                if (str_starts_with($key, 'permissions_group_') && is_array($value)) {
                                    $permissionIds = array_merge($permissionIds, $value);
                                }
                            }

                            $permissionIds = array_unique($permissionIds);

                            $record->permissions()->sync($permissionIds);

                            $total = count($permissionIds);
                            Notification::make()
                                ->title('Permisos actualizados')
                                ->body(Str::markdown("Se sincronizaron **$total** permisos al rol **{$record->name}** correctamente"))
                                ->success()
                                ->send();
                        })
                        ->hidden(fn(Role $role) => $role->name === 'Administrador'),

                    EditAction::make()->hidden(fn(Role $record) => $record->name === 'Administrador'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
