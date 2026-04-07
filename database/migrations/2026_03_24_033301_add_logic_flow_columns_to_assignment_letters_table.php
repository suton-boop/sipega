<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk Sinkronisasi Logic Flowchart SIPEGA
     */
    public function up(): void
    {
        Schema::table('assignment_letters', function (Blueprint $table) {
            // Logic 3: Justifikasi Pimpinan untuk Pegawai Kuning/Merah
            if (!Schema::hasColumn('assignment_letters', 'justification')) {
                $table->text('justification')->nullable()->after('description');
            }

            // Logic 4: Laporan Hasil Dinas Luar (Post-Assignment)
            if (!Schema::hasColumn('assignment_letters', 'report_path')) {
                $table->string('report_path')->nullable()->after('status');
                $table->timestamp('report_submitted_at')->nullable()->after('report_path');
            }
        });

        Schema::table('meetings', function (Blueprint $table) {
            // Logic 2: Pengecekan Partisipan (Pleno atau Terbatas)
            if (!Schema::hasColumn('meetings', 'is_pleno')) {
                $table->boolean('is_pleno')->default(false)->after('title');
            }

            // Geofence radius (default 50m)
            if (!Schema::hasColumn('meetings', 'geofence_radius')) {
                $table->integer('geofence_radius')->default(50)->after('gps_lng');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_letters', function (Blueprint $table) {
            $table->dropColumn(['justification', 'report_path', 'report_submitted_at']);
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['is_pleno', 'geofence_radius']);
        });
    }
};
