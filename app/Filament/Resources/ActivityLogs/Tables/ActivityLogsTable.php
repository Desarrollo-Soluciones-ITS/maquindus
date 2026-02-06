<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Filament\Filters\DateFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas')
                    ->sortable(),
                TextColumn::make('subject_type')
                    ->label('Registro')
                    ->formatStateUsing(function (string $state, $record) {
                        if (!$record->subject) {
                            return 'N/A';
                        }

                        $shouldShow = method_exists($record->subject, 'shouldShowInActivityLog')
                            ? $record->subject->shouldShowInActivityLog()
                            : true;

                        return $shouldShow ? 'Ver registro' : 'N/A';
                    })
                    ->url(function ($record) {
                        if (!$record->subject) {
                            return null;
                        }

                        if (method_exists($record->subject, 'getActivityLogUrl')) {
                            return $record->subject->getActivityLogUrl();
                        }

                        return null;
                    }),
                TextColumn::make('log_name')
                    ->label('Módulo')
                    ->badge()
                    ->searchable(),
                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => translate_activity_event($state))
                    ->color(fn(string $state): string => get_activity_color($state))
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->html(true)
                    ->formatStateUsing(function (string $state, $record) {
                        if (!$record->subject) {
                            return $state;
                        }

                        if (method_exists($record->subject, 'getActivityLogDescription')) {
                            return new HtmlString(
                                $record->subject->getActivityLogDescription($record->event, $record->description, $record->properties->toArray())
                            );
                        }

                        return $state;
                    })
                    ->tooltip(function (TextColumn $column) {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return new HtmlString($state);
                    }),
                TextColumn::make('causer.name')
                    ->label('Causado por')
                    ->default('Sistema'),
            ])
            ->filters([
                DateFilter::make(),
                SelectFilter::make('log_name')
                    ->label('Módulo')
                    ->searchable()
                    ->options([
                        'Actividades' => 'Actividades',
                        'Archivos' => 'Archivos',
                        'Autenticación' => 'Autenticación',
                        'Clientes' => 'Clientes',
                        'Contactos' => 'Contactos',
                        'Documentos' => 'Documentos',
                        'Equipos' => 'Equipos',
                        'Permisos' => 'Permisos',
                        'Proveedores' => 'Proveedores',
                        'Proyectos' => 'Proyectos',
                        'Repuestos' => 'Repuestos',
                        'Roles' => 'Roles',
                        'Usuarios' => 'Usuarios',
                    ]),
                SelectFilter::make('event')
                    ->label('Evento')
                    ->options([
                        'created' => 'Creación',
                        'updated' => 'Actualización',
                        'deleted' => 'Archivación',
                        'authenticated' => 'Inicio de Sesión',
                        'logged_out' => 'Cierre de Sesión',
                        'login_failed' => 'Fallo de Inicio de Sesión',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission(permission: 'activity_logs.show')),
                ])
            ]);
    }
}