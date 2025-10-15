<?php

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
        Schema::create('activity_person', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_v4()'));
            $table->foreignUuid('activity_id')->constrained();
            $table->foreignUuid('person_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_person');
    }
};
