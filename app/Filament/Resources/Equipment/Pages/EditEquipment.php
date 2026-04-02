<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use App\Models\SupplierPurchaseOrder;
use App\Traits\PreventsEditingTrashed;
use App\Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EditEquipment extends EditRecord
{
    use PreventsEditingTrashed;

    protected static string $resource = EquipmentResource::class;

    protected array $supplierPurchaseOrdersData = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->hidden(!currentUserHasPermission('equipments.show')),
            ArchiveAction::make()->hidden(fn($record) => $record->trashed() || !currentUserHasPermission('equipments.delete')),
            RestoreAction::make()->hidden(fn($record) => !$record->trashed() || !currentUserHasPermission('equipments.restore')),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $data['supplier_purchase_orders_data'] = $record->supplierPurchaseOrders()
            ->with('supplier')
            ->get()
            ->map(fn(SupplierPurchaseOrder $order) => [
                'supplier_id' => $order->supplier_id,
                'order_no' => $order->order_no,
                'description' => $order->description,
            ])
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->supplierPurchaseOrdersData = $this->normalizePurchaseOrders($data['supplier_purchase_orders_data'] ?? []);

        unset($data['supplier_purchase_orders_data']);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data = collect($data);
        $oldName = $record->name;
        $newName = $data['name'];

        $isDocumentable = method_exists($record, 'documents');

        if ($isDocumentable && $oldName !== $newName) {
            handle_documentable_name_change($record, $oldName, $newName);
        }

        $record->update($data->all());

        return $record;
    }

    protected function afterSave(): void
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
