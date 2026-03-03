<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_purchase_order', function (Blueprint $table) {
            $table->uuid('project_id');
            $table->uuid('purchase_order_id');
            $table->primary(['project_id', 'purchase_order_id']);
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_purchase_order');
    }
};
