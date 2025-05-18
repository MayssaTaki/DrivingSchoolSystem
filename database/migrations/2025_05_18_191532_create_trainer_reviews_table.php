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
        Schema::create('trainer_reviews', function (Blueprint $table) {
            $table->id();
              $table->foreignId('student_id')->constrained()->onDelete('cascade');
    $table->foreignId('trainer_id')->constrained()->onDelete('cascade');
    $table->tinyInteger('rating')->comment('من 1 إلى 5');
    $table->text('comment')->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_reviews');
    }
};
