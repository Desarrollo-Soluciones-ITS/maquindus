<?php

namespace App\Filament\Resources\Parts\Tables;

use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Filters\DateFilter;
use App\Filament\Filters\ArchivedFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('part_number')
                    ->label('N° de parte')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('catalog_number')
                    ->label('N° de catálogo')
                    ->searchable(),
                TextColumn::make('customer_part_number')
                    ->label('N° de parte del cliente')
                    ->searchable(),
                TextColumn::make('equipment.name')
                    ->label('Equipos relacionados')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),
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
                DateFilter::make(),
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    OpenFolderAction::make(),
                    ViewAction::make()->hidden(!currentUserHasPermission('parts.show')),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
                \Filament\Actions\Action::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $parts = $query->get();

                        $title = 'Repuestos';
                        $fileName = 'repuestos.xlsx';
                        if (method_exists($livewire, 'getOwnerRecord') && $ownerRecord = $livewire->getOwnerRecord()) {
                            $ownerName = (string) ($ownerRecord->name ?? 'registro');
                            $title = $ownerName . ' — Repuestos';
                            $fileName = \Illuminate\Support\Str::slug($ownerName) . '-repuestos.xlsx';
                        }

                        $rows = $parts->map(function ($part) {
                            return [
                                $part->part_number,
                                $part->catalog_number,
                                $part->customer_part_number,
                                $part->equipment->pluck('name')->join(', '),
                                $part->about,
                                $part->created_at
                                    ? \Carbon\Carbon::parse($part->created_at)->format('d/m/Y')
                                    : null,
                            ];
                        })->all();

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\RelationManagerExcelExport(
                                $title,
                                ['N° de parte', 'N° de catálogo', 'N° de parte del cliente', 'Equipos relacionados', 'Descripción', 'Fecha'],
                                $rows,
                            ),
                            $fileName,
                        );
                    }),
            ]);
    }
}
