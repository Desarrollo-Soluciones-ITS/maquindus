<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\SupplierPurchaseOrder;
use Illuminate\Support\Collection;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipment extends CreateRecord
{
    protected static string $resource = EquipmentResource::class;

    protected array $projectNames = [];

    protected array $clientPurchaseOrders = [];

    protected array $supplierPurchaseOrders = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->projectNames = $this->normalizeTags($data['project_names'] ?? []);
        $this->clientPurchaseOrders = $this->normalizeTags($data['client_purchase_orders'] ?? []);
        $this->supplierPurchaseOrders = $this->normalizeTags($data['supplier_purchase_orders'] ?? []);

        unset($data['project_names'], $data['client_purchase_orders'], $data['supplier_purchase_orders']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncRelationships($this->getRecord());
    }

    protected function normalizeTags(array $values): array
    {
        return collect($values)
            ->filter(fn($value) => filled($value))
            ->map(fn($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function syncRelationships(Equipment $equipment): void
    {
        $equipment->projects()->sync(
            $this->resolveIds($this->projectNames, fn(string $name) => Project::firstOrCreate(['name' => $name])->getKey())
        );

        $equipment->purchaseOrders()->sync(
            $this->resolveIds($this->clientPurchaseOrders, fn(string $orderNo) => PurchaseOrder::firstOrCreate(['order_no' => $orderNo])->getKey())
        );

        $equipment->supplierPurchaseOrders()->sync(
            $this->resolveIds($this->supplierPurchaseOrders, fn(string $orderNo) => SupplierPurchaseOrder::firstOrCreate(['order_no' => $orderNo])->getKey())
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
