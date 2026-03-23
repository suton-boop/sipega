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
        Schema::create('assignment_letters', function (Blueprint $table) {
            $table->id();
            $table->string('letter_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->boolean('is_private')->default(false);
            $table->enum('type', ['Individu', 'Kolektif'])->default('Individu');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_letters');
    }
};
