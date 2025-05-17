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
        Schema::create('training_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('schedule_id')->constrained('training_schedules')->onDelete('cascade');
    $table->foreignId('trainer_id')->constrained('trainers')->onDelete('cascade');
    $table->date('session_date')->index();
    $table->time('start_time');
    $table->time('end_time');
    $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
