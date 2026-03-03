<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('part_purchase_order', function (Blueprint $table) {
            $table->uuid('part_id');
            $table->uuid('purchase_order_id');
            $table->primary(['part_id', 'purchase_order_id']);
            $table->foreign('part_id')->references('id')->on('parts')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_purchase_order');
    }
};
