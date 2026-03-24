<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Agenda Harian
        Schema::create('daily_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->text('activity_plan');
            $table->text('activity_realization')->nullable();
            $table->string('proof_file_path')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['Draft', 'Submitted', 'Late'])->default('Draft');
            $table->timestamps();
        });

        // Tabel Daftar Rapat/Kegiatan
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date');
            $table->time('start_time');
            $table->decimal('gps_lat', 10, 8)->nullable();
            $table->decimal('gps_lng', 11, 8)->nullable();
            $table->string('current_qr_token')->nullable(); // Untuk security dynamic QR
            $table->timestamps();
        });

        // Tabel Absensi/Log Rapat
        Schema::create('meeting_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('check_in_time');
            $table->decimal('check_in_lat', 10, 8)->nullable();
            $table->decimal('check_in_lng', 11, 8)->nullable();
            $table->boolean('is_valid')->default(true); // Verifikasi geofence 50 meter
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_logs');
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('daily_agendas');
    }
};
