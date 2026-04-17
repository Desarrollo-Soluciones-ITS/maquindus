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
            if (! Schema::hasColumn('equipment', 'model')) {
                $table->string('model')->nullable()->after('name');
            }

            if (! Schema::hasColumn('equipment', 'serial')) {
                $table->string('serial')->nullable()->after('model');
            }

            if (! Schema::hasColumn('equipment', 'type')) {
                $table->string('type')->nullable()->after('serial');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $columnsToDrop = collect(['model', 'serial', 'type'])
                ->filter(fn (string $column): bool => Schema::hasColumn('equipment', $column))
                ->all();

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};