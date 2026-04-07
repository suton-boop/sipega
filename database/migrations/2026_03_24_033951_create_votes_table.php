<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel Voting SIPEGA-Reward (Peer-to-Peer Appreciation)
     */
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_id')->constrained('users')->cascadeOnDelete();
            $table->string('month_year'); // Format: MM-YYYY untuk index pencarian cepat
            $table->text('comment')->nullable();
            $table->timestamps();

            // Batasi 1 orang hanya boleh Vote 1 orang lain per bulan (Anti-Spam)
            $table->unique(['voter_id', 'month_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
