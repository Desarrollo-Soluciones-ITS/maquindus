<?php

use App\Enums\Category;
use App\Enums\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_v4()'));
            $table->string('name');
            $table->string('path');
            $table->string('mime');
            $table->integer('version');
            $table->enum('type', Type::cases())->nullable();
            $table->enum('category', Category::cases())->nullable();
            $table->uuidMorphs('documentable');
            $table->foreignUuid('user_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
