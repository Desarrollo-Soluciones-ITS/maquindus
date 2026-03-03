<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Filters\ArchivedFilter;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_no')->label('N° de Orden')->searchable()->sortable(),
                TextColumn::make('description')->label('Descripción')->limit(40),
                TextColumn::make('created_at')->label('Creado el')->dateTime('d/m/Y H:i'),

            ])
            ->filters([
                ArchivedFilter::make(),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('purchase_orders.delete')),
                RestoreAction::make()->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('purchase_orders.restore')),
            ])
            ->toolbarActions([
                \Filament\Actions\CreateAction::make(),
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
                                        'N° de Orden' => $order->order_no,
                                        'Descripción' => $order->description,
                                        'Creado el' => $order->created_at,
                                    ];
                                }); }
                            public function headings(): array
                            {
                                return ['N° de Orden', 'Descripción', 'Creado el'];
                            }
                            },
                            'ordenes.xlsx'
                        );
                    }),
            ]);
    }
}
