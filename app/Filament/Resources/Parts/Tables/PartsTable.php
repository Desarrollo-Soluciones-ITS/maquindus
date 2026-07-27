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
                        $fileName = 'repuestos.xlsx';
                        if (method_exists($livewire, 'getOwnerRecord')) {
                            $ownerRecord = $livewire->getOwnerRecord();
                            $ownerName = \Illuminate\Support\Str::slug($ownerRecord->name ?? 'registro');
                            $fileName = "{$ownerName}-repuestos.xlsx";
                        }
                        return \Maatwebsite\Excel\Facades\Excel::download(new class($parts) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                            protected $parts;
                            public function __construct($parts) { $this->parts = $parts; }
                            public function collection() { return $this->parts->map(fn($part) => ['N° de parte' => $part->part_number, 'N° de catálogo' => $part->catalog_number, 'N° de parte del cliente' => $part->customer_part_number, 'Equipos relacionados' => $part->equipment->pluck('name')->join(', '), 'Descripción' => $part->about, 'Fecha' => $part->created_at]); }
                            public function headings(): array { return ['N° de parte', 'N° de catálogo', 'N° de parte del cliente', 'Equipos relacionados', 'Descripción', 'Fecha']; }
                        }, $fileName);
                    }),
            ]);
    }
}
