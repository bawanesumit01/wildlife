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
        Schema::create('panchnama', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('forest_department_name');
            $table->string('date');
            $table->string('location');
            $table->string('staff_name');
            $table->string('description');
            $table->string('panchnama_image');
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
        //
    }
};
