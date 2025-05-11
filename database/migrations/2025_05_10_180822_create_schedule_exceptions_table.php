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
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
                    $table->foreignId('trainer_id')->constrained()->onDelete('cascade');
            $table->date('exception_date');
            $table->boolean('is_available')->default(false);
            $table->time('available_start_time')->nullable();
            $table->time('available_end_time')->nullable();
            $table->text('reason')->nullable();
            
            $table->unique(['trainer_id', 'exception_date']);
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};
