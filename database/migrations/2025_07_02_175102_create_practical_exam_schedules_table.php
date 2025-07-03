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
        Schema::create('practical_exam_schedules', function (Blueprint $table) {
            $table->id();
             $table->foreignId('license_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
             $table->date('exam_date');
             $table->time('exam_time');
             $table->enum('status', [ 'scheduled','absent', 'failed', 'passed'])->default('scheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practical_exam_schedules');
    }
};
