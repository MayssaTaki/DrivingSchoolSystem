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
        Schema::create('car_reservation_reports', function (Blueprint $table) {
            $table->id();
     $table->unsignedBigInteger('car_id');
    $table->date('date');
    $table->unsignedInteger('total_reservations')->default(0);
    $table->timestamps();

    $table->unique(['car_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_reservation_reports');
    }
};
