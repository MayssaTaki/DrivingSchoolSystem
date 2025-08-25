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
        Schema::create('feedback_students', function (Blueprint $table) {
            $table->id();
             $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade')->unique();

            $table->enum('level', ['beginner', 'intermediate', 'excellent']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_students');
    }
};
