<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mik_permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['kantor', 'ruangan'])->default('kantor'); // keluar kantor atau keluar ruangan
            $table->string('employee_name');
            $table->string('employee_nip')->nullable();
            $table->string('employee_position')->nullable();
            $table->string('gugus_mutu')->nullable(); // Gugus Mutu pemohon
            
            $table->date('date'); // Tanggal izin
            $table->time('out_time'); // Jam keluar
            $table->time('return_time')->nullable(); // Jam kembali aktual
            $table->time('estimated_return_time')->nullable(); // Estimasi jam kembali
            
            $table->string('destination')->nullable(); // Tujuan (lokasi luar atau ruangan dalam kantor)
            $table->text('purpose'); // Keperluan izin
            
            // Status izin
            $table->enum('status', ['belum direspon', 'disetujui', 'tidak disetujui', 'dibatalkan'])->default('belum direspon');
            
            // Responder (Pemberi Izin: Ketua Gugus Mutu, Kasubbag. Umum, Kepala Balai)
            $table->string('responder_role')->default('Kasubbag. Umum'); // e.g. Ketua Gugus 1 s.d 5, Kasubbag. Umum, Kepala
            $table->unsignedBigInteger('responder_id')->nullable();
            $table->string('responder_name')->nullable();
            $table->text('responder_notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mik_permits');
    }
};
