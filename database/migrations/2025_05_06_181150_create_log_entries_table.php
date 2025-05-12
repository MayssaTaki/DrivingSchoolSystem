<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogEntriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('log_entries', function (Blueprint $table) {
            $table->id();
            $table->string('level'); 
            $table->string('message');
            $table->text('context')->nullable(); 
            $table->string('channel')->nullable()->index(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_entries');
    }
}
