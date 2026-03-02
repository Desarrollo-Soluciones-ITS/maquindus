<?php

namespace App\Filament\Resources\Equipment\Tables;

use App\Filament\Filters\DateFilter;
use App\Filament\Actions\ArchiveAction;
use App\Filament\Filters\ArchivedFilter;
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
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
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
                DateFilter::make(),
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('equipments.show')),
                    EditAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('equipments.edit')),
                    ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('equipments.delete')),
                    RestoreAction::make()->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('equipments.restore')),
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
                        $equipments = $query->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(new class($equipments) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                            protected $equipments;
                            public function __construct($equipments) { $this->equipments = $equipments; }
                            public function collection() { return $this->equipments->map(function($equipment) {
                                return [
                                    'Código' => $equipment->code,
                                    'Nombre' => $equipment->name,
                                    'Descripción' => $equipment->about,
                                    'Fecha' => $equipment->created_at,
                                ];
                            }); }
                            public function headings(): array { return ['Código', 'Nombre', 'Descripción', 'Fecha']; }
                        }, 'equipos.xlsx');
                    }),
            ]);
    }
}
