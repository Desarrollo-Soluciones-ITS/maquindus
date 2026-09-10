<?php

namespace App\Filament\Resources\Equipment\Tables;

use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Filters\DateFilter;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Filters\ArchivedFilter;
use App\Filament\Filters\TextFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use App\Filament\Actions\EditAction;
use App\Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre equipo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('serial')
                    ->label('Serial')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('about')
                    ->label('Descripción')
                    ->limit(75),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->sortable(is_not_relation_manager())
                    ->date('d/m/Y - g:i A')
                    ->timezone('America/Caracas'),
            ])
            ->filters([
                ...TextFilter::forColumns([
                    'name' => 'Nombre equipo',
                    'model' => 'Modelo',
                    'serial' => 'Serial',
                    'about' => 'Descripción',
                ], \App\Models\Equipment::class),
                DateFilter::make(),
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    OpenFolderAction::make(),
                    ViewAction::make()->hidden(!currentUserHasPermission('equipments.show')),
                    EditAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('equipments.edit')),
                    ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('equipments.delete')),
                    RestoreAction::make()->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('equipments.restore')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
                \Filament\Actions\Action::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $equipments = $query->get();

                        $title = 'Equipos';
                        $fileName = 'equipos.xlsx';
                        if (method_exists($livewire, 'getOwnerRecord') && $ownerRecord = $livewire->getOwnerRecord()) {
                            $ownerName = (string) ($ownerRecord->name ?? 'registro');
                            $title = $ownerName . ' — Equipos';
                            $fileName = \Illuminate\Support\Str::slug($ownerName) . '-equipos.xlsx';
                        }

                        $rows = $equipments->map(function ($equipment) {
                            return [
                                $equipment->name,
                                $equipment->model,
                                $equipment->serial,
                                $equipment->about,
                                $equipment->created_at
                                    ? \Carbon\Carbon::parse($equipment->created_at)->format('d/m/Y')
                                    : null,
                            ];
                        })->all();

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\RelationManagerExcelExport(
                                $title,
                                ['Nombre equipo', 'Modelo', 'Serial', 'Descripción', 'Fecha'],
                                $rows,
                            ),
                            $fileName,
                        );
                    }),
            ]);
    }
}
