<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use App\Models\SupplierPurchaseOrder;
use Illuminate\Support\Collection;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipment extends CreateRecord
{
    protected static string $resource = EquipmentResource::class;

    protected array $supplierPurchaseOrdersData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->supplierPurchaseOrdersData = $this->normalizePurchaseOrders($data['supplier_purchase_orders_data'] ?? []);

        unset($data['supplier_purchase_orders_data']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncRelationships($this->getRecord());
    }

    protected function normalizePurchaseOrders(array $values): array
    {
        return collect($values)
            ->map(function ($value) {
                return [
                    'supplier_id' => $value['supplier_id'] ?? null,
                    'order_no' => trim((string) ($value['order_no'] ?? '')),
                    'description' => filled($value['description'] ?? null) ? trim((string) $value['description']) : null,
                ];
            })
            ->filter(fn(array $value) => filled($value['supplier_id']) && filled($value['order_no']))
            ->unique('order_no')
            ->values()
            ->all();
    }

    protected function syncRelationships(Equipment $equipment): void
    {
        $equipment->supplierPurchaseOrders()->sync(
            $this->resolveIds($this->supplierPurchaseOrdersData, fn(array $orderData) => SupplierPurchaseOrder::updateOrCreate(
                ['order_no' => $orderData['order_no']],
                [
                    'supplier_id' => $orderData['supplier_id'],
                    'description' => $orderData['description'],
                ],
            )->getKey())
        );
    }

    protected function resolveIds(array $values, callable $resolver): array
    {
        return Collection::make($values)
            ->map($resolver)
            ->values()
            ->all();
    }
}
