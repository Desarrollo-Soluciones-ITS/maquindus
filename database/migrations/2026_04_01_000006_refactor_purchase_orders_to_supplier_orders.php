<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('supplier_purchase_orders') && !Schema::hasColumn('supplier_purchase_orders', 'supplier_id')) {
            Schema::table('supplier_purchase_orders', function (Blueprint $table) {
                $table->foreignUuid('supplier_id')->nullable()->after('description')->constrained('suppliers');
            });
        }

        $this->backfillSupplierIds();

        Schema::dropIfExists('equipment_purchase_order');
        Schema::dropIfExists('part_purchase_order');
        Schema::dropIfExists('purchase_orders');
    }

    public function down(): void
    {
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('order_no', 80)->unique();
                $table->string('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('equipment_purchase_order')) {
            Schema::create('equipment_purchase_order', function (Blueprint $table) {
                $table->uuid('equipment_id');
                $table->uuid('purchase_order_id');
                $table->primary(['equipment_id', 'purchase_order_id']);
                $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('part_purchase_order')) {
            Schema::create('part_purchase_order', function (Blueprint $table) {
                $table->uuid('part_id');
                $table->uuid('purchase_order_id');
                $table->primary(['part_id', 'purchase_order_id']);
                $table->foreign('part_id')->references('id')->on('parts')->onDelete('cascade');
                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            });
        }
    }

    private function backfillSupplierIds(): void
    {
        if (!Schema::hasTable('supplier_purchase_orders') || !Schema::hasTable('equipment_supplier_purchase_order') || !Schema::hasTable('equipment_supplier')) {
            return;
        }

        $orderIds = DB::table('supplier_purchase_orders')
            ->whereNull('supplier_id')
            ->pluck('id');

        foreach ($orderIds as $orderId) {
            $supplierIds = DB::table('equipment_supplier_purchase_order as espo')
                ->join('equipment_supplier as es', 'es.equipment_id', '=', 'espo.equipment_id')
                ->where('espo.supplier_purchase_order_id', $orderId)
                ->distinct()
                ->pluck('es.supplier_id');

            if ($supplierIds->count() === 1) {
                DB::table('supplier_purchase_orders')
                    ->where('id', $orderId)
                    ->update(['supplier_id' => $supplierIds->first()]);
            }
        }
    }
};