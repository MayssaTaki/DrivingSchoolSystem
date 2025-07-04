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
        Schema::create('car_faults', function (Blueprint $table) {
            $table->id();
             $table->foreignId('car_id')->constrained()->onDelete('cascade');
    $table->foreignId('trainer_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
    
    $table->text('comment');
    $table->enum('status', ['new', 'in_progress', 'resolved'])->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_faults');
    }
};
