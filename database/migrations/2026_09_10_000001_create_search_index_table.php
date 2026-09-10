<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('search_index', function (Blueprint $table) {
            $table->id();
            $table->string('model_type', 150);
            $table->string('model_id', 191);
            $table->text('searchable_content');
            $table->text('searchable_content_normalized');
            $table->string('result_name');
            $table->text('result_description')->nullable();
            $table->timestamps();

            $table->unique(['model_type', 'model_id']);
            $table->index('model_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_index');
    }
};
