<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('contact_name')->nullable();
            $table->string('contact_position')->nullable();
            $table->string('contact_phone')->nullable();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('contact_name')->nullable();
            $table->string('contact_position')->nullable();
            $table->string('contact_phone')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'contact_position', 'contact_phone']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'contact_position', 'contact_phone']);
        });
    }
};
