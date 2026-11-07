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
        // 1. MASTER UNIT PELAYANAN (ULT & PERPUSTAKAAN)
        Schema::create('piket_units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // 'ULT', 'PERPUS'
            $table->string('name');            // 'Unit Layanan Terpadu (ULT)', 'Perpustakaan BPMP Kaltim'
            $table->string('location')->nullable(); // e.g. 'Gedung A Lantai 1'
            $table->text('description')->nullable();
            $table->time('start_time')->default('08:00');
            $table->time('end_time')->default('16:00');
            $table->integer('daily_officers_count')->default(2); // Jumlah petugas utama per hari
            $table->integer('daily_backups_count')->default(1);  // Jumlah petugas cadangan per hari
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. DAFTAR PEGAWAI ELIGIBEL PIKET
        Schema::create('piket_eligible_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('piket_unit_id')->constrained('piket_units')->cascadeOnDelete();
            $table->string('assignment_role')->default('utama'); // 'utama', 'cadangan', 'keduanya'
            $table->boolean('is_available')->default(true);       // status kesiapan piket
            $table->string('unavailability_reason')->nullable(); // jika sementara tidak dapat dijadwalkan
            $table->integer('max_shifts_per_month')->default(4); // batas maksimal giliran
            $table->timestamps();

            $table->unique(['user_id', 'piket_unit_id']);
        });

        // 3. MASTER HEADER JADWAL PIKET TAHUNAN / BULANAN
        Schema::create('piket_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piket_unit_id')->constrained('piket_units')->cascadeOnDelete();
            $table->year('year');
            $table->tinyInteger('month')->nullable(); // null jika tahunan penuh, 1-12 jika bulanan
            $table->string('period_type')->default('tahunan'); // 'tahunan', 'bulanan', 'khusus'
            $table->string('title'); // e.g. 'Jadwal Piket Pelayanan ULT Tahun 2026'
            $table->string('status')->default('draft'); // 'draft', 'published', 'revised', 'archived'
            $table->string('revision_number')->default('0'); // 'Rev.0', 'Rev.1', dst
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('published_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. DETAIL PENUGASAN JADWAL HARIAN
        Schema::create('piket_schedule_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piket_schedule_id')->constrained('piket_schedules')->cascadeOnDelete();
            $table->foreignId('piket_unit_id')->constrained('piket_units')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('primary_officer_id')->constrained('users')->cascadeOnDelete(); // Petugas Utama Asli
            $table->foreignId('backup_officer_id')->nullable()->constrained('users')->nullOnDelete(); // Petugas Cadangan
            $table->foreignId('active_officer_id')->constrained('users')->cascadeOnDelete(); // Petugas yang aktif bertugas (bisa pengganti)
            
            $table->string('role_label')->default('Petugas 1'); // e.g. 'Petugas Utama 1', 'Petugas Utama 2'
            $table->string('status')->default('normal'); // 'normal', 'conflict', 'replaced', 'swapped', 'completed', 'absent'
            $table->boolean('has_conflict')->default(false);
            $table->string('conflict_type')->nullable(); // 'surat_tugas', 'cuti', 'tugas_luar', 'rapat', 'izin_kantor'
            $table->text('conflict_description')->nullable();
            $table->string('conflict_reference_number')->nullable(); // No Surat Tugas atau No Permintaan Cuti
            
            $table->boolean('is_replaced')->default(false);
            $table->foreignId('replacement_id')->nullable(); // relasi ke piket_replacements
            
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['piket_unit_id', 'date']);
            $table->index(['active_officer_id', 'date']);
        });

        // 5. DETEKSI BENTURAN / KONFLIK JADWAL
        Schema::create('piket_conflicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piket_schedule_assignment_id')->constrained('piket_schedule_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('conflict_date');
            $table->string('conflict_source'); // 'assignment_letter', 'letter', 'leave_request', 'travel_report', 'meeting', 'mik_permit'
            $table->string('reference_number')->nullable(); // No ST / No Cuti
            $table->string('activity_title'); // Judul kegiatan / Jenis cuti
            $table->date('activity_start_date');
            $table->date('activity_end_date');
            $table->string('status')->default('pending'); // 'pending', 'resolved_backup', 'resolved_available', 'resolved_manual', 'ignored'
            $table->text('resolution_notes')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. RIWAYAT PERGANTIAN PETUGAS (3 LEVEL PRIORITAS)
        Schema::create('piket_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piket_schedule_assignment_id')->constrained('piket_schedule_assignments')->cascadeOnDelete();
            $table->foreignId('original_officer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('replacement_officer_id')->constrained('users')->cascadeOnDelete();
            $table->string('priority_level'); // 'prioritas_1_cadangan', 'prioritas_2_tersedia', 'prioritas_3_manual'
            $table->string('reason');
            $table->string('approval_status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('approver_notes')->nullable();
            $table->timestamps();
        });

        // 7. PENGAJUAN TUKAR JADWAL ANTARPEGAWAI
        Schema::create('piket_swap_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('requester_assignment_id')->constrained('piket_schedule_assignments')->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_assignment_id')->constrained('piket_schedule_assignments')->cascadeOnDelete();
            $table->date('requester_date');
            $table->date('target_date');
            $table->text('reason');
            $table->string('target_user_agreement')->default('pending'); // 'pending', 'agreed', 'declined'
            $table->dateTime('agreed_at')->nullable();
            $table->string('admin_approval_status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // 8. PENERBITAN RESMI (SK / LEMBAR JADWAL RESMI)
        Schema::create('piket_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piket_schedule_id')->constrained('piket_schedules')->cascadeOnDelete();
            $table->foreignId('piket_unit_id')->constrained('piket_units')->cascadeOnDelete();
            $table->string('document_number'); // No SK/Dokumen e.g. '0452/B7.5/KP.01.00/2026'
            $table->year('year');
            $table->string('title');
            $table->date('effective_date');
            $table->string('signatory_name');
            $table->string('signatory_nip');
            $table->string('signatory_position'); // 'Kepala Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur'
            $table->string('pdf_file_path')->nullable();
            $table->string('version_tag')->default('1.0');
            $table->timestamps();
        });

        // 9. MONITORING & PRESENSI PELAKSANAAN PIKET
        Schema::create('piket_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('piket_schedule_assignment_id')->constrained('piket_schedule_assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Petugas yang bertugas riil
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('attendance_status')->default('hadir'); // 'hadir', 'tidak_hadir', 'digantikan', 'izin'
            $table->integer('visitors_served_count')->default(0); // Jumlah tamu/pengunjung yang dilayani
            $table->text('service_notes')->nullable(); // Catatan pelayanan harian
            $table->text('issues_notes')->nullable();  // Kendala / fasilitas rusak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piket_attendances');
        Schema::dropIfExists('piket_publications');
        Schema::dropIfExists('piket_swap_requests');
        Schema::dropIfExists('piket_replacements');
        Schema::dropIfExists('piket_conflicts');
        Schema::dropIfExists('piket_schedule_assignments');
        Schema::dropIfExists('piket_schedules');
        Schema::dropIfExists('piket_eligible_employees');
        Schema::dropIfExists('piket_units');
    }
};
