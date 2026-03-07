<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Str;

class PurchaseOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseOrders';

    protected static ?string $title = 'Órdenes de compra';

    protected static ?string $modelLabel = 'orden de compra';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('order_no')->label('N° de Orden'),
                TextColumn::make('description')->label('Descripción'),
                TextColumn::make('created_at')->label('Creado el')->dateTime(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
                Action::make('export')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $ownerRecord = $livewire->getOwnerRecord();
                        $ownerName = Str::slug($ownerRecord->name ?? 'registro');
                        $fileName = "{$ownerName}-ordenes-de-compra.xlsx";
                        $orders = $query->get();
                        return \Maatwebsite\Excel\Facades\Excel::download(new class($orders) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                            protected $orders;
                            public function __construct($orders) { $this->orders = $orders; }
                            public function collection() { return $this->orders->map(fn($order) => [
                                'N° de Orden' => $order->order_no,
                                'Descripción' => $order->description,
                                'Creado el' => $order->created_at,
                            ]); }
                            public function headings(): array { return ['N° de Orden', 'Descripción', 'Creado el']; }
                        }, $fileName);
                    }),
            ]);
    }
}
