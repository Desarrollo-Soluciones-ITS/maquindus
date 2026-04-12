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
        // Add user_id to media table
        if (Schema::hasTable('media')) {
            Schema::table('media', function (Blueprint $table) {
                if (!Schema::hasColumn('media', 'user_id')) {
                    $table->foreignIdFor(config('auth.providers.users.model'), 'user_id')
                        ->nullable()
                        ->after('model_type')
                        ->nullOnDelete();
                }
            });
        }

        // Add user_id and media_id to files table
        Schema::table('files', function (Blueprint $table) {
            if (!Schema::hasColumn('files', 'user_id')) {
                $table->foreignIdFor(config('auth.providers.users.model'), 'user_id')
                    ->nullable()
                    ->after('document_id')
                    ->nullOnDelete();
            }
            
            if (!Schema::hasColumn('files', 'media_id')) {
                $table->unsignedBigInteger('media_id')
                    ->nullable()
                    ->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropColumn('media_id');
        });
    }
};
