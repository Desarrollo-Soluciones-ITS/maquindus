<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                if (!Schema::hasColumn('suppliers', 'contact_name')) {
                    $table->string('contact_name')->nullable();
                }

                if (!Schema::hasColumn('suppliers', 'contact_position')) {
                    $table->string('contact_position')->nullable();
                }

                if (!Schema::hasColumn('suppliers', 'contact_phone')) {
                    $table->string('contact_phone')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $columns = array_filter([
                    Schema::hasColumn('suppliers', 'contact_name') ? 'contact_name' : null,
                    Schema::hasColumn('suppliers', 'contact_position') ? 'contact_position' : null,
                    Schema::hasColumn('suppliers', 'contact_phone') ? 'contact_phone' : null,
                ]);

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
