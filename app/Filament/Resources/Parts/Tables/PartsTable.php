<?php

namespace App\Filament\Resources\Parts\Tables;

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
                    EditAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('parts.edit')),
                    ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('parts.delete')),
                    RestoreAction::make()->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('parts.restore')),
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
                            public function collection() { return $this->parts->map(fn($part) => ['Código' => $part->code, 'Nombre' => $part->name, 'Descripción' => $part->about, 'Fecha' => $part->created_at]); }
                            public function headings(): array { return ['Código', 'Nombre', 'Descripción', 'Fecha']; }
                        }, $fileName);
                    }),
            ]);
    }
}
