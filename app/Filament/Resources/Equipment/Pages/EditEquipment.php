<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Resources\Equipment\EquipmentResource;
use App\Models\Equipment;
use App\Models\Project;
use App\Models\PurchaseOrder;
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

    protected array $projectNames = [];

    protected array $clientPurchaseOrders = [];

    protected array $supplierPurchaseOrders = [];

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

        $data['project_names'] = $record->projects()->pluck('name')->all();
        $data['client_purchase_orders'] = $record->purchaseOrders()->pluck('order_no')->all();
        $data['supplier_purchase_orders'] = $record->supplierPurchaseOrders()->pluck('order_no')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->projectNames = $this->normalizeTags($data['project_names'] ?? []);
        $this->clientPurchaseOrders = $this->normalizeTags($data['client_purchase_orders'] ?? []);
        $this->supplierPurchaseOrders = $this->normalizeTags($data['supplier_purchase_orders'] ?? []);

        unset($data['project_names'], $data['client_purchase_orders'], $data['supplier_purchase_orders']);

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
