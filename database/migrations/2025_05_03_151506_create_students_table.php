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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name')->index();
            $table->string('last_name');
            $table->string('phone_number');
            $table->string('address');
            $table->enum('gender', ['female', 'male']);
            $table->date('date_of_Birth');
            $table->string('image')->nullable();
            $table->string('nationality')->default('syrian');
            $table->boolean('is_military')->default(false);
            $table->boolean('left_hand_disabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
