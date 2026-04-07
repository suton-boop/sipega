<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Rincian Kegiatan Harian SIPEGA (1 Hari Bisa Banyak Kegiatan)
     */
    public function up(): void
    {
        Schema::create('daily_agenda_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_agenda_id')->constrained()->onDelete('cascade');
            $table->text('plan_description');
            $table->enum('status', ['pending', 'completed', 'changed', 'progress'])->default('pending');
            $table->text('realization_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_agenda_items');
    }
};
