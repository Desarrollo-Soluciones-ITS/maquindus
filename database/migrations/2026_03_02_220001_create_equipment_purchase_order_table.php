<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_purchase_order', function (Blueprint $table) {
            $table->uuid('equipment_id');
            $table->uuid('purchase_order_id');
            $table->primary(['equipment_id', 'purchase_order_id']);
            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_purchase_order');
    }
};
