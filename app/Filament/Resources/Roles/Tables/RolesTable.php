<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Filament\Filters\DateFilter;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
                        ->successNotificationTitle('Se asignó el rol a todos los usuarios seleccionados'),

                    Action::make('permissions')
                        ->label('Editar permisos')
                        ->icon(Heroicon::LockClosed)
                        ->fillForm(fn(Role $record): array => [
                            'permissions' => $record->permissions->pluck('id')->toArray(),
                        ])
                        ->schema(function () {
                            $tree = static::getPermissionsTreeForFilament();
                            $permissionOptions = Permission::orderBy('name')->pluck('name', 'id')->toArray();
                            $permissionSlugs = Permission::pluck('slug', 'id')->toArray();
                            $slugToId = array_flip($permissionSlugs);

                            $groups = [];

                            foreach ($tree as $key => $value) {
                                if (is_string($key) && is_array($value)) {
                                    $groupLabel = ucfirst(static::$resourceLabels[$key] ?? $key);
                                    $options = [];
                                    foreach ($value as $action) {
                                        if (!is_string($key) || !is_string($action))
                                            continue;
                                        $slug = "$key.$action";
                                        if (isset($slugToId[$slug])) {
                                            $options[$slugToId[$slug]] = $permissionOptions[$slugToId[$slug]];
                                        }
                                    }
                                    if (!empty($options)) {
                                        $groups[$groupLabel] = CheckboxList::make("permissions_group_$key")
                                            ->label($groupLabel)
                                            ->options($options)
                                            ->columns(1)
                                            ->bulkToggleable()
                                            ->extraAttributes(['class' => 'permission-group', 'data-group-label' => strtolower($groupLabel)]);
                                    }
                                } elseif ($key === 'relationships') {
                                    foreach ($value as $relatedModel => $actions) {
                                        $relatedLabel = static::$resourceLabels[$relatedModel] ?? $relatedModel;
                                        $groupLabel = "Relaciones de " . ucfirst($relatedLabel);
                                        $options = [];
                                        foreach ($actions as $action) {
                                            if (!is_string($relatedModel) || !is_string($action))
                                                continue;
                                            $slug = "relationships.$relatedModel.$action";
                                            if (isset($slugToId[$slug])) {
                                                $options[$slugToId[$slug]] = $permissionOptions[$slugToId[$slug]];
                                            }
                                        }
                                        if (!empty($options)) {
                                            $groups[$groupLabel] = CheckboxList::make("permissions_group_relationships_$relatedModel")
                                                ->label($groupLabel)
                                                ->options($options)
                                                ->columns(1)
                                                ->bulkToggleable()
                                                ->extraAttributes(['class' => 'permission-group', 'data-group-label' => strtolower($groupLabel)]);
                                        }
                                    }
                                }
                            }

                            $topLevelPermissions = array_filter($tree, 'is_string');
                            if (!empty($topLevelPermissions)) {
                                $options = [];
                                foreach ($topLevelPermissions as $slug) {
                                    if (isset($slugToId[$slug])) {
                                        $options[$slugToId[$slug]] = $permissionOptions[$slugToId[$slug]];
                                    }
                                }
                                if (!empty($options)) {
                                    $groups['General'] = CheckboxList::make('permissions_group_general')
                                        ->label('General')
                                        ->options($options)
                                        ->columns(1)
                                        ->bulkToggleable()
                                        ->extraAttributes(['class' => 'permission-group', 'data-group-label' => 'general']);
                                }
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
                                \Filament\Forms\Components\TextInput::make('permission_search')
                                    ->label('Buscar permisos')
                                    ->placeholder('Escriba para filtrar permisos...')
                                    ->extraAttributes([
                                        'x-data' => '',
                                        'x-on:input.debounce.300ms' => "
                                        const searchTerm = \$event.target.value.toLowerCase();
                                        const sections = document.querySelectorAll('.permission-section');
                                        
                                        sections.forEach(section => {
                                            const sectionHeader = section.querySelector('.fi-section-header-heading');
                                            const sectionLabel = sectionHeader ? sectionHeader.textContent.toLowerCase() : '';
                                            const permissionOptions = section.querySelectorAll('.fi-fo-checkbox-list-option-label');
                                            let hasVisibleItems = false;
                                            
                                            // Reset all options first
                                            permissionOptions.forEach(optionLabel => {
                                                const optionContainer = optionLabel.closest('.fi-fo-checkbox-list-option-ctn');
                                                const gridParent = optionContainer.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode
                                                if (gridParent) {
                                                    gridParent.style.display = 'block';
                                                }
                                            });
                                            
                                            // Show section by default
                                            section.style.display = 'block';
                                            
                                            // Check if section header matches
                                            if (sectionLabel.includes(searchTerm)) {
                                                hasVisibleItems = true;
                                            } else {
                                                // Check individual options
                                                permissionOptions.forEach(optionLabel => {
                                                    const labelText = optionLabel.textContent.toLowerCase();
                                                    const optionContainer = optionLabel.closest('.fi-fo-checkbox-list-option-ctn');
                                                    const gridParent = optionContainer.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode
                                                    
                                                    if (labelText.includes(searchTerm)) {
                                                        hasVisibleItems = true;
                                                        if (optionContainer) {
                                                            optionContainer.style.display = 'flex';
                                                        }
                                                    } else if (optionContainer && searchTerm.length > 0) {
                                                        
                                                        gridParent.style.display = 'none';
                                                    }
                                                });
                                            }
                                            
                                            // Hide section if no matches found
                                            if (!hasVisibleItems && searchTerm.length > 0) {
                                                section.style.display = 'none';
                                            }
                                        });
                                    ",
                                    ])
                                    ->dehydrated(false),
                                Fieldset::make('Acciones rápidas')
                                    ->schema([
                                        Action::make('select_all_permissions')
                                            ->label('Seleccionar todos los permisos')
                                            ->button()
                                            ->color('primary')
                                            ->action(function ($livewire, $set) {
                                                $tree = static::getPermissionsTreeForFilament();
                                                $permissionSlugs = Permission::pluck('slug', 'id')->toArray();
                                                $slugToId = array_flip($permissionSlugs);

                                                $fieldPermissions = [];

                                                foreach ($tree as $key => $value) {
                                                    if (is_string($key) && is_array($value)) {
                                                        $fieldName = "permissions_group_$key";
                                                        $fieldPermissions[$fieldName] = [];

                                                        foreach ($value as $action) {
                                                            if (!is_string($key) || !is_string($action))
                                                                continue;
                                                            $slug = "$key.$action";
                                                            if (isset($slugToId[$slug])) {
                                                                $fieldPermissions[$fieldName][] = $slugToId[$slug];
                                                            }
                                                        }
                                                    } elseif ($key === 'relationships') {
                                                        foreach ($value as $relatedModel => $actions) {
                                                            $fieldName = "permissions_group_relationships_$relatedModel";
                                                            $fieldPermissions[$fieldName] = [];

                                                            foreach ($actions as $action) {
                                                                if (!is_string($relatedModel) || !is_string($action))
                                                                    continue;
                                                                $slug = "relationships.$relatedModel.$action";
                                                                if (isset($slugToId[$slug])) {
                                                                    $fieldPermissions[$fieldName][] = $slugToId[$slug];
                                                                }
                                                            }
                                                        }
                                                    } elseif (is_string($value)) {
                                                        $fieldName = "permissions_group_general";
                                                        if (!isset($fieldPermissions[$fieldName])) {
                                                            $fieldPermissions[$fieldName] = [];
                                                        }
                                                        $slug = $value;
                                                        if (isset($slugToId[$slug])) {
                                                            $fieldPermissions[$fieldName][] = $slugToId[$slug];
                                                        }
                                                    }
                                                }

                                                foreach ($fieldPermissions as $fieldName => $permissionIds) {
                                                    if (!empty($permissionIds)) {
                                                        $set($fieldName, $permissionIds);
                                                    }
                                                }

                                                if (method_exists($livewire, 'dispatchFormEvent')) {
                                                    $livewire->dispatchFormEvent('selectAllPermissionsUpdated');
                                                }
                                            }),

                                        Action::make('deselect_all_permissions')
                                            ->label('Deseleccionar todos los permisos')
                                            ->button()
                                            ->color('danger')
                                            ->action(function ($set, $get) {
                                                foreach ($get() as $field => $value) {
                                                    if (str_starts_with($field, 'permissions_group_')) {
                                                        $set($field, []);
                                                    }
                                                }
                                            }),
                                    ]),

                                Grid::make(3)
                                    ->schema($sectionComponents)
                                    ->extraAttributes([
                                        'x-data' => '',
                                    ]),
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

                            if (empty($permissionIds)) {
                                $record->permissions()->sync([]);
                                Notification::make()
                                    ->title('Rol actualizado')
                                    ->body(Str::markdown("Se removieron todos los permisos del rol **$record->name**"))
                                    ->warning()
                                    ->send();
                            } else {
                                $record->permissions()->sync($permissionIds);
                                $total = count($permissionIds);
                                Notification::make()
                                    ->title('Rol actualizado')
                                    ->body(Str::markdown("Se sincronizaron **$total** permisos al rol **$record->name** correctamente"))
                                    ->success()
                                    ->send();
                            }
                        })
                        ->hidden(fn(Role $r) => $r->name === 'Administrador'),
                    EditAction::make()
                        ->hidden(fn(Role $record) => $record->name === 'Administrador'),
                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    protected static array $actionLabels = [
        'create' => 'Crear',
        'edit' => 'Editar',
        'show' => 'Ver',
        'delete' => 'Archivar',
        'view' => 'Listar',
        'download' => 'Descargar',
        'upload' => 'Subir',
        'open_in_folder' => 'Abrir en carpeta',
        'show_file' => 'Ver archivo',
        'sync' => 'Vincular',
        'unsync' => 'Desvincular',
        'gallery' => 'Galería',
    ];

    protected static array $resourceLabels = [
        'equipments' => 'equipo',
        'parts' => 'repuesto',
        'projects' => 'proyecto',
        'documents' => 'documento',
        'suppliers' => 'proveedor',
        'customers' => 'cliente',
        'people' => 'persona',
        'users' => 'usuario',
        'activities' => 'actividad',
        'files' => 'archivo',
        'images' => 'imagen',
        'activity_logs' => 'registros',
    ];

    protected static function getPermissionsTreeForFilament(): array
    {
        return [
            'dashboard',
            'equipments' => ['create', 'edit', 'show', 'delete', 'view'],
            'parts' => ['create', 'show', 'view', 'delete', 'edit'],
            'projects' => ['create', 'show', 'view', 'delete', 'edit'],
            'documents' => ['view', 'delete', 'edit', 'show', 'open_in_folder', 'show_file', 'download', 'upload'],
            'suppliers' => ['create', 'show', 'view', 'delete', 'edit'],
            'customers' => ['create', 'show', 'view', 'delete', 'edit'],
            'people' => ['create', 'show', 'view', 'delete', 'edit'],
            'users' => ['create', 'show', 'view', 'delete', 'edit'],
            'activity_logs' => ['create', 'show', 'view', 'delete', 'edit'],
            'relationships' => [
                'parts' => ['create', 'sync', 'unsync', 'edit', 'show'],
                'equipments' => ['create', 'sync', 'unsync', 'edit', 'show'],
                'projects' => ['create', 'edit', 'show'],
                'documents' => ['create', 'delete', 'edit', 'show', 'download', 'open_in_folder', 'show_file'],
                'people' => ['create', 'sync', 'unsync', 'edit', 'show', 'delete'],
                'activities' => ['create', 'edit', 'show', 'delete', 'sync', 'unsync'],
                'suppliers' => ['create', 'sync', 'unsync', 'edit', 'show', 'delete'],
                'images' => ['download', 'edit', 'show', 'delete', 'create', 'gallery'],
                'files' => ['download', 'show', 'delete', 'create', 'open_in_folder', 'show_file'],
            ]
        ];
    }
}
