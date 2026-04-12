<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seeded_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('seeder_class')->unique();
            $table->timestamp('executed_at')->useCurrent();
            $table->string('executed_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seeded_permissions');
    }
};
