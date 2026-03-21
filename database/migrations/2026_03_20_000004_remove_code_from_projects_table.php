<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite' && Schema::hasColumn('projects', 'code')) {
            DB::statement('DROP INDEX IF EXISTS projects_code_unique');
        }

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'code')) {
                $table->dropColumn('code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'code')) {
                $table->string('code')->unique()->nullable()->after('name');
            }
        });
    }
};