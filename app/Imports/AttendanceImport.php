<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceImport implements ToCollection, WithHeadingRow
{
    /**
     * Memproses baris Excel satu persatu.
     * Kolom yg diharapkan: email, tanggal (YYYY-MM-DD), status, tl_menit, psw_menit
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $email = $row['email'] ?? null;
            $date = $row['tanggal'] ?? null;
            $statusExcel = $row['status'] ?? 'Alpa';
            $tl = $row['tl_menit'] ?? 0;
            $psw = $row['psw_menit'] ?? 0;

            if (!$email || !$date) continue; // Skip baris tidak valid

            $user = User::where('email', $email)->first();
            if (!$user) continue;

            // CORE ENGINE: ANTI-TERTINDIH (Tahap 2)
            // Deteksi apakah user ini sedang Dinas Luar (Punya SPPD/Assignment Letter) di tanggal tersebut
            $isDL = DB::table('assignment_letter_user')
                ->join('assignment_letters', 'assignment_letter_user.assignment_letter_id', '=', 'assignment_letters.id')
                ->where('assignment_letter_user.user_id', $user->id)
                ->where('assignment_letters.date', $date)
                ->exists();

            // Jika sedang DL, status selalu DL apa pun hasil absen di luar (Excel)
            $finalStatus = $isDL ? 'DL' : $statusExcel;

            // Jika sedang DL, potongan keterlambatan dianulir otomatis
            if ($isDL) {
                $tl = 0;
                $psw = 0;
            }

            // Simpan / Update (Gunakan updateOrCreate untuk menghindari duplikat)
            AttendanceLog::updateOrCreate(
                ['user_id' => $user->id, 'date' => $date],
                [
                    'status' => $finalStatus,
                    'tl_minutes' => $tl,
                    'psw_minutes' => $psw,
                    'notes' => $isDL ? 'Sistem: Otomatis tersinkronisasi sebagai Dinas Luar (ST).' : 'Sinkronisasi Tabel Excel Mesin.'
                ]
            );
        }
    }
}
