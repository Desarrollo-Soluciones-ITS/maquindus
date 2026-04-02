<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Filament\Resources\PurchaseOrders\Schemas\PurchaseOrderForm;
use App\Models\SupplierPurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->hidden(!currentUserHasPermission('purchase_orders.create')),
            Action::make('bulkCreate')
                ->label('Carga múltiple')
                ->icon('heroicon-o-rectangle-stack')
                ->form(PurchaseOrderForm::getBulkComponents())
                ->action(function (array $data): void {
                    DB::transaction(function () use ($data): void {
                        foreach ($data['orders'] ?? [] as $orderData) {
                            SupplierPurchaseOrder::updateOrCreate(
                                ['order_no' => $orderData['order_no']],
                                [
                                    'supplier_id' => $orderData['supplier_id'],
                                    'description' => $orderData['description'] ?? null,
                                ],
                            );
                        }
                    });

                    Notification::make()
                        ->success()
                        ->title('Órdenes de compra cargadas correctamente.')
                        ->send();
                })
                ->hidden(!currentUserHasPermission('purchase_orders.create')),
        ];
    }
}
