<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        PurchaseOrder::create([
            'order_no' => 'PO000001',
            'description' => 'Orden de compra de prueba',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
