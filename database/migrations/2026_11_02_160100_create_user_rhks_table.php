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
        Schema::create('user_rhks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('rhk_desc');
            $table->string('classification', 50)->default('Utama'); // Utama / Tambahan
            $table->string('target_quarter', 20)->default('Semua'); // Q1, Q2, Q3, Q4, Semua
            $table->unsignedSmallInteger('year')->default(2026);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rhks');
    }
};
