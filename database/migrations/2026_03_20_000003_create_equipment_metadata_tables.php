<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment_data_sheets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained(table: 'equipment')->cascadeOnDelete();
            $table->string('sheet_number');
            $table->string('revision')->nullable();
            $table->date('document_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment_blueprints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained(table: 'equipment')->cascadeOnDelete();
            $table->string('blueprint_number');
            $table->string('name');
            $table->string('revision')->nullable();
            $table->date('document_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment_catalogs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained(table: 'equipment')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment_technical_specifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained(table: 'equipment')->cascadeOnDelete();
            $table->string('revision_name');
            $table->string('revision')->nullable();
            $table->date('document_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment_standards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained(table: 'equipment')->cascadeOnDelete();
            $table->string('name');
            $table->string('revision')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment_field_queries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained(table: 'equipment')->cascadeOnDelete();
            $table->date('document_date')->nullable();
            $table->string('document_name');
            $table->string('document_type');
            $table->string('issuer')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment_spare_parts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained(table: 'equipment')->cascadeOnDelete();
            $table->string('part_number');
            $table->string('catalog_number')->nullable();
            $table->string('client_part_number')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('equipment_id')->constrained(table: 'equipment')->cascadeOnDelete();
            $table->date('document_date')->nullable();
            $table->string('document_name');
            $table->string('document_type');
            $table->string('issuer')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_reports');
        Schema::dropIfExists('equipment_spare_parts');
        Schema::dropIfExists('equipment_field_queries');
        Schema::dropIfExists('equipment_standards');
        Schema::dropIfExists('equipment_technical_specifications');
        Schema::dropIfExists('equipment_catalogs');
        Schema::dropIfExists('equipment_blueprints');
        Schema::dropIfExists('equipment_data_sheets');
    }
};