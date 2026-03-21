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
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('model')->nullable()->after('name');
            $table->string('serial')->nullable()->after('model');
            $table->string('type')->nullable()->after('serial');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropUnique('equipment_code_unique');
            $table->dropColumn(['code', 'details']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('name');
            $table->json('details')->nullable()->after('about');
            $table->dropColumn(['model', 'serial', 'type']);
        });
    }
};