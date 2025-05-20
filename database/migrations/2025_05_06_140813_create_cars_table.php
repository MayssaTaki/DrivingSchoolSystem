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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate')->unique();
            $table->string('make')->index();
            $table->string('model');
            $table->string('color');
            $table->string('year');
            $table->string('image')->nullable();
            $table->enum('transmission', ['automatic', 'manual']);
            $table->boolean('is_for_special_needs')->default(false);
            $table->enum('status', ['booked', 'available', 'in_repair'])->default('available');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
