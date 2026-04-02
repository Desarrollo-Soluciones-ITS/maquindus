<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\SupplierPurchaseOrder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $equipmentIds = \App\Models\Equipment::query()->pluck('id')->all();

        foreach (Supplier::query()->get()->values() as $index => $supplier) {
            for ($order = 1; $order <= 2; $order++) {
                $purchaseOrder = SupplierPurchaseOrder::updateOrCreate(
                    ['order_no' => sprintf('PO-%02d-%03d', $index + 1, $order)],
                    [
                        'supplier_id' => $supplier->id,
                        'description' => "Orden {$order} asociada al proveedor {$supplier->name}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );

                if ($equipmentIds !== []) {
                    $purchaseOrder->equipment()->syncWithoutDetaching(array_slice($equipmentIds, 0, min(2, count($equipmentIds))));
                }
            }
        }
    }
}
