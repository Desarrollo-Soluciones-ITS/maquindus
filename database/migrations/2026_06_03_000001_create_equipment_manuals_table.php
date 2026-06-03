<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_manuals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained(table: 'equipment')->cascadeOnDelete();
            $table->string('name');
            $table->string('revision')->nullable();
            $table->date('document_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_manuals');
    }
};
