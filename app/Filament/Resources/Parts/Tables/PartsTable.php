<?php

namespace App\Filament\Resources\Parts\Tables;

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
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
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
                            public function collection() { return $this->parts->map(fn($part) => ['Código' => $part->code, 'Nombre' => $part->name, 'Equipos relacionados' => $part->equipment->pluck('name')->join(', '), 'Descripción' => $part->about, 'Fecha' => $part->created_at]); }
                            public function headings(): array { return ['Código', 'Nombre', 'Equipos relacionados', 'Descripción', 'Fecha']; }
                        }, $fileName);
                    }),
            ]);
    }
}
