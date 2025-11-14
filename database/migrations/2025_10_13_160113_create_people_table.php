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
        Schema::create('people', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('surname');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('address');
            $table->string('position')->nullable();
            $table->nullableUuidMorphs('personable');
            $table->foreignUuid('country_id')->constrained();
            $table->foreignUuid('state_id')->nullable()->constrained();
            $table->foreignUuid('city_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
