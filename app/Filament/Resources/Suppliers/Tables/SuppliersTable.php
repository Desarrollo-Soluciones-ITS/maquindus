<?php

namespace App\Filament\Resources\Suppliers\Tables;

use App\Filament\Actions\Documents\OpenFolderAction;
use Filament\Actions\ActionGroup;
use App\Filament\Filters\ArchivedFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rif')
                    ->label('RIF')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('equipment.name')
                    ->label('Equipos relacionados')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),
                TextColumn::make('parts.name')
                    ->label('Repuestos relacionados')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
            ])
            ->filters([
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    OpenFolderAction::make(),
                    ViewAction::make()->hidden(!currentUserHasPermission('suppliers.show')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
                \Filament\Actions\Action::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $suppliers = $query->get();

                        $rows = $suppliers->map(function ($supplier) {
                            return [
                                $supplier->rif,
                                $supplier->name,
                                $supplier->email,
                                $supplier->equipment->pluck('name')->join(', '),
                                $supplier->parts->pluck('name')->join(', '),
                                $supplier->phone,
                            ];
                        })->all();

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\RelationManagerExcelExport(
                                'Proveedores',
                                ['RIF', 'Nombre', 'Correo', 'Equipos relacionados', 'Repuestos relacionados', 'Teléfono'],
                                $rows,
                            ),
                            'proveedores.xlsx',
                        );
                    }),
            ]);
    }
}
