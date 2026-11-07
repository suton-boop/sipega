<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom kepegawaian pendukung cuti pada tabel users jika belum ada
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'status_kepegawaian')) {
                $table->string('status_kepegawaian')->default('PNS')->after('position'); // PNS / PPPK / Non-ASN
            }
            if (!Schema::hasColumn('users', 'unit_kerja')) {
                $table->string('unit_kerja')->default('BPMP Kalimantan Timur')->after('status_kepegawaian');
            }
            if (!Schema::hasColumn('users', 'tmt_cpns_pppk')) {
                $table->date('tmt_cpns_pppk')->nullable()->after('unit_kerja');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'supervisor_id')) {
                $table->unsignedBigInteger('supervisor_id')->nullable()->after('role');
            }
        });

        // 2. Tabel Kuota / Saldo Cuti Pegawai (Per Tahun: N, N-1, N-2)
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('year'); // contoh 2026
            $table->integer('initial_quota')->default(12); // Hak dasar cuti tahun berjalan
            $table->integer('used_quota')->default(0);    // Cuti tahunan yang sudah terpakai
            $table->integer('remaining_quota')->default(12); // Sisa kuota N
            $table->integer('remaining_n1')->default(0);   // Sisa cuti N-1 (tahun sebelumnya)
            $table->integer('remaining_n2')->default(0);   // Sisa cuti N-2 (2 tahun sebelumnya)
            $table->integer('cuti_besar_taken')->default(0); // dalam hari / bulan
            $table->integer('cuti_sakit_taken')->default(0);
            $table->integer('cuti_melahirkan_taken')->default(0);
            $table->integer('cuti_alasan_penting_taken')->default(0);
            $table->integer('cltn_taken')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'year']);
        });

        // 3. Tabel Permohonan Cuti ASN (Format BKN PerBKN 24/2017 & 7/2021)
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('request_number')->nullable(); // Nomor Registrasi Permohonan
            $table->date('request_date'); // Tanggal Pengajuan (Samarinda, ...)

            // I. Data Pegawai (Snapshot saat pengajuan)
            $table->string('employee_name');
            $table->string('employee_nip');
            $table->string('employee_position');
            $table->string('employee_status')->default('PNS'); // PNS / PPPK
            $table->string('work_duration')->nullable(); // Contoh: "22 tahun 9 Bulan"
            $table->string('work_unit')->default('BPMP Kalimantan Timur');

            // II. Jenis Cuti yang Diambil
            // cuti_tahunan, cuti_besar, cuti_sakit, cuti_melahirkan, cuti_alasan_penting, cuti_cltn
            $table->string('leave_type'); 

            // III. Alasan Cuti
            $table->text('reason');

            // IV. Lamanya Cuti
            $table->integer('duration_amount'); // misal: 1
            $table->string('duration_unit')->default('hari'); // hari / bulan / tahun
            $table->date('start_date');
            $table->date('end_date');

            // V. Catatan Cuti (Snapshot kuota saat pengajuan untuk lembar print)
            $table->integer('quota_n2')->default(0);
            $table->string('year_n2')->nullable();
            $table->integer('quota_n1')->default(0);
            $table->string('year_n1')->nullable();
            $table->integer('quota_n')->default(0);
            $table->string('year_n')->nullable();
            $table->string('note_cuti_besar')->nullable();
            $table->string('note_cuti_sakit')->nullable();
            $table->string('note_cuti_melahirkan')->nullable();
            $table->string('note_cuti_alasan_penting')->nullable();
            $table->string('note_cltn')->nullable();

            // VI. Alamat Selama Menjalankan Cuti
            $table->text('leave_address');
            $table->string('phone_number');
            $table->string('signature_applicant')->nullable(); // Bukti TTD Pemohon

            // Berkas Bukti Pendukung Wajib
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_type')->nullable(); // Surat Dokter / HPL / Kematian / dll

            // VII. Pertimbangan Atasan Langsung
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_nip')->nullable();
            $table->string('supervisor_position')->nullable();
            $table->enum('supervisor_decision', ['pending', 'DISETUJUI', 'PERUBAHAN', 'DITANGGUHKAN', 'TIDAK DISETUJUI'])->default('pending');
            $table->text('supervisor_notes')->nullable();
            $table->timestamp('supervisor_action_at')->nullable();

            // Verifikasi Subbag Umum / Tim Kepegawaian Satker
            $table->unsignedBigInteger('verifier_id')->nullable();
            $table->string('verifier_name')->nullable();
            $table->text('verifier_notes')->nullable();
            $table->timestamp('verified_at')->nullable();

            // VIII. Keputusan Pejabat yang Berwenang Memberikan Cuti (PPK / Kepala)
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->string('approver_name')->nullable();
            $table->string('approver_nip')->nullable();
            $table->string('approver_position')->nullable();
            $table->enum('approver_decision', ['pending', 'DISETUJUI', 'PERUBAHAN', 'DITANGGUHKAN', 'TIDAK DISETUJUI'])->default('pending');
            $table->text('approver_notes')->nullable();
            $table->timestamp('approver_action_at')->nullable();

            // Status Keseluruhan Alur
            // Draft, Diajukan, Disetujui Atasan, Terverifikasi, Disetujui, Perubahan, Ditangguhkan, Ditolak
            $table->string('status')->default('Diajukan'); 

            // Validasi Otomatis AI SIPEGA
            $table->string('ai_validation_status')->default('Memenuhi Syarat'); // Memenuhi Syarat / Ditolak / Perlu Dokumen Tambahan
            $table->text('ai_validation_summary')->nullable();
            $table->json('ai_validation_details')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_balances');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status_kepegawaian', 'unit_kerja', 'tmt_cpns_pppk', 'phone', 'supervisor_id']);
        });
    }
};
