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
        Schema::create('snake_bite', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('patient_name');
            $table->string('patient_number');
            $table->string('patient_address');
            $table->string('admint_date');
            $table->string('discharge_date');
            $table->string('patient_status');
            $table->string('snake_type');
            $table->string('snake_species');
            $table->string('hospital_name');
            $table->string('description');
            $table->string('patient_image');
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
