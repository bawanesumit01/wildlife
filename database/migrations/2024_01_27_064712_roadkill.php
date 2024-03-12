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
        Schema::create('road_kill', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('rescuer_name');
            $table->string('rescued_type');
            $table->string('description');
            $table->string('image');
            $table->string('ip_address');
            $table->string('latitude');
            $table->string('longitude');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('road_kill');
    }
};
