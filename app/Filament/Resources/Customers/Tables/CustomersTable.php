<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Filters\ArchivedFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use App\Filament\Actions\EditAction;
use App\Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
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
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
            ])
            ->filters([
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('customers.show')),
                    EditAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('customers.edit')),
                    ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('customers.delete')),
                    RestoreAction::make()->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('customers.restore')),
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
                        $customers = $query->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(new class($customers) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                            protected $customers;
                            public function __construct($customers) { $this->customers = $customers; }
                            public function collection() { return $this->customers->map(function($customer) {
                                return [
                                    'RIF' => $customer->rif,
                                    'Nombre' => $customer->name,
                                    'Correo' => $customer->email,
                                    'Teléfono' => $customer->phone,
                                ];
                            }); }
                            public function headings(): array { return ['RIF', 'Nombre', 'Correo', 'Teléfono']; }
                        }, 'clientes.xlsx');
                    }),
            ]);
    }
}
