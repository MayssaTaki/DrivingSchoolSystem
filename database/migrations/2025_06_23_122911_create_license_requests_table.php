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
        Schema::create('license_requests', function (Blueprint $table) {
            $table->id();
               $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('license_id')->constrained()->onDelete('cascade');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->enum('type', ['new', 'renewal', 'replacement'])->default('new');
           $table->date('issued_at')->nullable();  
           $table->date('expires_at')->nullable();
           $table->json('document_files')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_requests');
    }
};
