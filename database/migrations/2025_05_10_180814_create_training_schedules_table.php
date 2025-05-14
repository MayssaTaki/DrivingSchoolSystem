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
        Schema::create('training_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', [
                'monday', 
                'tuesday', 
                'wednesday', 
                'thursday', 
                'sunday', 
                'saturday', 
                
            ])->index();
            $table->time('start_time')->index();
            $table->time('end_time');
            $table->boolean('is_recurring')->default(true);
            $table->date('valid_from')->nullable()->index();
            $table->date('valid_to')->nullable()->index();
            $table->enum('status', ['active', 'inactive'])->default('inactive');            
            $table->unique(['trainer_id', 'day_of_week', 'start_time'], 'trainer_time_slot_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_schedules');
    }
};
