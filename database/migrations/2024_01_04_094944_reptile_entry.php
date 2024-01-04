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
        Schema::create('reptile_entry', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('caller_name');
            $table->string('caller_number');
            $table->string('caller_address');
            $table->string('caller_aadhar_number');
            $table->string('rescued_reptile_type');
            $table->string('snake')->nullable();
            $table->string('venom')->nullable();
            $table->string('reptile_condition');
            $table->string('reptile_sex')->nullable();
            $table->string('reptile_description')->nullable();
            $table->string('charges');
            $table->string('reptile_image');
            $table->string('ip_address');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();

            // Foreign key constraint to link with the users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
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
