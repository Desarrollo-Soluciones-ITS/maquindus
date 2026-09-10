<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Filament\Actions\Documents\OpenFolderAction;
use App\Filament\Filters\ArchivedFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.name')->label('Proveedor')->searchable()->sortable(),
                TextColumn::make('order_no')->label('Código de orden')->searchable()->sortable(),
                TextColumn::make('description')->label('Descripción')->limit(40),
                TextColumn::make('equipment.name')->label('Equipos relacionados')->badge()->separator(', ')->toggleable(),
                TextColumn::make('created_at')->label('Creado el')->dateTime('d/m/Y H:i'),

            ])
            ->filters([
                ArchivedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    OpenFolderAction::make(),
                    ViewAction::make()->hidden(!currentUserHasPermission('purchase_orders.read')),
                    EditAction::make()->hidden(!currentUserHasPermission('purchase_orders.edit')),
                ]),
            ])
            ->toolbarActions([
                \Filament\Actions\Action::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $orders = $query->get();

                        $rows = $orders->map(function ($order) {
                            return [
                                $order->supplier?->name,
                                $order->order_no,
                                $order->description,
                                $order->equipment->pluck('name')->join(', '),
                                $order->created_at
                                    ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y')
                                    : null,
                            ];
                        })->all();

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\RelationManagerExcelExport(
                                'Órdenes de compra proveedor',
                                ['Proveedor', 'Código de orden', 'Descripción', 'Equipos relacionados', 'Creado el'],
                                $rows,
                            ),
                            'ordenes-compra-proveedor.xlsx',
                        );
                    }),
            ]);
    }
}
