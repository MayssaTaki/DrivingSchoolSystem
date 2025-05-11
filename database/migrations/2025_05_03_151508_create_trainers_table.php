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
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->string('license_number')->unique(); 
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('specialization', ['regular', 'special_needs']); 
            $table->text('experience'); 
            $table->string('phone_number');
            $table->string('address'); 
            $table->enum('gender', ['female', 'male']);
            $table->string('image')->nullable();
            $table->date('license_expiry_date'); 
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};
