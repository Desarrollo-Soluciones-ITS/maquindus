<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Filters\ArchivedFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
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
                TextColumn::make('projects.name')
                    ->label('Proyectos relacionados')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),
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
                                    'Proyectos relacionados' => $customer->projects->pluck('name')->join(', '),
                                    'Teléfono' => $customer->phone,
                                ];
                            }); }
                            public function headings(): array { return ['RIF', 'Nombre', 'Correo', 'Proyectos relacionados', 'Teléfono']; }
                        }, 'clientes.xlsx');
                    }),
            ]);
    }
}
