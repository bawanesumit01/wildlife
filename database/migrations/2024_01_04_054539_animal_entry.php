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
        Schema::create('animal_entry', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('caller_name');
            $table->string('caller_number');
            $table->string('caller_address');
            $table->string('caller_aadhar_number');
            $table->string('rescued_animal_type');
            $table->string('animal_condition');
            $table->string('animal_sex');
            $table->string('animal_description');
            $table->string('charges');
            $table->string('animal_image');
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
