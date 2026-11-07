<?php

namespace Database\Seeders;

use App\Models\PiketEligibleEmployee;
use App\Models\PiketUnit;
use App\Models\User;
use Illuminate\Database\Seeder;

class PiketInitialSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Unit ULT
        $ult = PiketUnit::updateOrCreate(
            ['code' => 'ULT'],
            [
                'name' => 'Unit Layanan Terpadu (ULT)',
                'location' => 'Gedung Pelayanan Terpadu Lantai 1',
                'description' => 'Pusat pelayanan konsultasi pendidikan, persuratan publik, Ijazah, dan informasi umum BPMP Kalimantan Timur.',
                'start_time' => '08:00',
                'end_time' => '16:00',
                'daily_officers_count' => 2,
                'daily_backups_count' => 1,
                'is_active' => true,
            ]
        );

        // 2. Buat Unit Perpustakaan
        $perpus = PiketUnit::updateOrCreate(
            ['code' => 'PERPUS'],
            [
                'name' => 'Perpustakaan BPMP Kaltim',
                'location' => 'Gedung B Lantai 2',
                'description' => 'Pelayanan literasi, referensi buku pendidikan, ruang baca, dan peminjaman buku kedinasan.',
                'start_time' => '08:00',
                'end_time' => '16:00',
                'daily_officers_count' => 1,
                'daily_backups_count' => 1,
                'is_active' => true,
            ]
        );

        // 3. Pasang Pegawai Eligibel Default dari database Pegawai
        $employees = User::realPegawai()->where('is_active', true)->get();
        if ($employees->isNotEmpty()) {
            // Split pegawai: bagian pertama untuk ULT, bagian kedua untuk Perpustakaan
            $half = intdiv($employees->count(), 2);
            $ultEmployees = $employees->slice(0, $half);
            $perpusEmployees = $employees->slice($half);

            foreach ($ultEmployees as $idx => $emp) {
                PiketEligibleEmployee::updateOrCreate(
                    ['user_id' => $emp->id, 'piket_unit_id' => $ult->id],
                    [
                        'assignment_role' => $idx % 3 === 0 ? 'cadangan' : 'utama',
                        'is_available' => true,
                        'max_shifts_per_month' => 4,
                    ]
                );
            }

            foreach ($perpusEmployees as $idx => $emp) {
                PiketEligibleEmployee::updateOrCreate(
                    ['user_id' => $emp->id, 'piket_unit_id' => $perpus->id],
                    [
                        'assignment_role' => $idx % 3 === 0 ? 'cadangan' : 'utama',
                        'is_available' => true,
                        'max_shifts_per_month' => 4,
                    ]
                );
            }
        }
    }
}
