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
            $table->string('first_name')->index();
            $table->string('last_name');
            $table->string('phone_number');
              $table->date('date_of_Birth');

            $table->string('address'); 
            $table->enum('gender', ['female', 'male']);
            $table->string('image')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
   $table->string('license_number')->unique(); 
    $table->date('license_expiry_date');       
    $table->string('experience'); 
    $table->enum('training_type', ['normal', 'special_needs']); 

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
