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
$table->foreignId('student_id')->constrained()->onDelete('cascade');
$table->foreignId('trainer_id')->constrained()->onDelete('cascade');
 $table->foreignId('session_id')->constrained('training_sessions')->onDelete('cascade');
    $table->tinyInteger('rating')->comment('من 1 إلى 5');
    $table->text('notes')->nullable();
    $table->unsignedInteger('number_session')->default(0);

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
