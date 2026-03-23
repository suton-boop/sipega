<?php

namespace App\Services;

use App\Models\AttendanceLog;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AttendanceImportService
{
    /**
     * Proses import data absen dari Excel dengan Logika "Anti-Tertindis" SIPEGA.
     *
     * @param Collection $rows Data excel (misal lewat Maatwebsite Excel)
     * @return array Hasil import
     */
    public function import(Collection $rows)
    {
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $userEmail = $row[0]; // Diasumsikan email berada pada kolom A
            $date = Carbon::parse($row[1])->format('Y-m-d');
            $checkIn = $row[2] ?? null;
            $checkOut = $row[3] ?? null;

            if (!$userEmail || !$date) continue;

            $user = \App\Models\User::where('email', $userEmail)->first();
            if (!$user) continue;

            // Tahap 2: Logika Anti-Tertindis Dinas Luar (Prioritas Tertinggi)
            $isOnDuty = \Illuminate\Support\Facades\DB::table('assignment_letter_user')
                ->join('assignment_letters', 'assignment_letter_user.assignment_letter_id', '=', 'assignment_letters.id')
                ->where('assignment_letter_user.user_id', $user->id)
                ->where('assignment_letters.date', $date)
                ->exists();

            if ($isOnDuty) {
                // Jika sedang Dinas Luar, JANGAN tindih data menjadi kosong/telat.
                // Paksa source menjadi ST (Surat Tugas) jika log belum pernah terikat
                AttendanceLog::firstOrCreate(
                    ['user_id' => $user->id, 'date' => $date],
                    ['source' => 'ST']
                );
                $skipped++;
                continue;
            }

            // Jika tidak sedang DL, masukkan atau tindih dari data Mesin/Excel
            AttendanceLog::updateOrCreate(
                ['user_id' => $user->id, 'date' => $date],
                ['check_in' => $checkIn, 'check_out' => $checkOut, 'source' => 'Mesin']
            );
            $imported++;
        }

        return [
            'imported' => $imported,
            'skipped_due_to_duty' => $skipped
        ];
    }
}
