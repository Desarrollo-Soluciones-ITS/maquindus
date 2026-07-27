<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->string('part_number')->unique()->nullable()->after('id');
            $table->string('catalog_number')->nullable()->after('part_number');
            $table->string('customer_part_number')->nullable()->after('catalog_number');
        });
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn(['part_number', 'catalog_number', 'customer_part_number']);
        });
    }
};