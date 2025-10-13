<?php

use App\Enums\Category;
use App\Enums\Type;
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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('path');
            $table->string('mime');
            $table->integer('version');
            $table->enum('type', [
                Type::BLUEPRINT,
                Type::MANUAL,
                Type::TECHNICAL,
            ])->nullable();
            $table->enum('category', [
                Category::SET,
                Category::DETAIL,
                Category::TO_BUILD,
                Category::AS_BUILT,
                Category::OPERATION,
                Category::MAINTENANCE,
            ])->nullable();
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
