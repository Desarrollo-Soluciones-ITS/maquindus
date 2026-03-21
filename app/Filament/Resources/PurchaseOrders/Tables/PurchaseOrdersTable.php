<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Filament\Filters\ArchivedFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_no')->label('Código de orden')->searchable()->sortable(),
                TextColumn::make('description')->label('Descripción')->limit(40),
                TextColumn::make('equipment.name')->label('Equipos relacionados')->badge()->separator(', ')->toggleable(),
                TextColumn::make('projects.name')->label('Proyectos relacionados')->badge()->separator(', ')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('parts.name')->label('Repuestos relacionados')->badge()->separator(', ')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Creado el')->dateTime('d/m/Y H:i'),

            ])
            ->filters([
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->hidden(!currentUserHasPermission('purchase_orders.show')),
                ]),
            ])
            ->toolbarActions([
                \Filament\Actions\Action::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $orders = $query->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new class ($orders) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                            protected $orders;
                            public function __construct($orders)
                            {
                                $this->orders = $orders; }
                            public function collection()
                            {
                                return $this->orders->map(function ($order) {
                                    return [
                                        'Código de orden' => $order->order_no,
                                        'Descripción' => $order->description,
                                        'Equipos relacionados' => $order->equipment->pluck('name')->join(', '),
                                        'Proyectos relacionados' => $order->projects->pluck('name')->join(', '),
                                        'Repuestos relacionados' => $order->parts->pluck('name')->join(', '),
                                        'Creado el' => $order->created_at,
                                    ];
                                }); }
                            public function headings(): array
                            {
                                return ['Código de orden', 'Descripción', 'Equipos relacionados', 'Proyectos relacionados', 'Repuestos relacionados', 'Creado el'];
                            }
                            },
                            'ordenes.xlsx'
                        );
                    }),
            ]);
    }
}
