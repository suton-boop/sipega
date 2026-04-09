<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MachineTransactionImport implements ToCollection
{
    /**
     * Memproses file "LAPORAN LOG TRANSAKSI" dari mesin absensi.
     * Format: 
     * Row containing "Nama  :" dan "NIP  :" untuk identifikasi user.
     * Row containing Tanggal (dd/mm/yyyy) dan Jam (hh:mm:ss).
     */
    public function collection(Collection $rows)
    {
        $userData = []; // [user_id => [date => [times]]]
        $currentUser = null;

        foreach ($rows as $row) {
            $rowArray = $row->toArray();
            $lineContent = implode(' ', array_filter($rowArray));

            // 1. Deteksi User Baru (NIP)
            if (str_contains($lineContent, 'NIP :')) {
                // Ekstrak NIP (biasanya setelah titik dua)
                preg_match('/NIP\s*:\s*(\d+)/', $lineContent, $matches);
                if (isset($matches[1])) {
                    $nip = $matches[1];
                    $currentUser = User::where('nip', 'like', $nip . '%')->first();
                }
                continue;
            }

            // 2. Deteksi Data Transaksi (Tanggal & Jam)
            // Cek apakah kolom pertama (atau gabungan) berisi tanggal dd/mm/yyyy atau ddmmyyyy (berdasarkan gambar ******** bisa jadi mask)
            // Gambar kedua menunjukkan format 12/04/2026.
            foreach ($rowArray as $cell) {
                if (is_string($cell) && preg_match('/(\d{2}\/\d{2}\/\d{4})/', $cell, $dateMatches)) {
                    $date = Carbon::createFromFormat('d/m/Y', $dateMatches[1])->format('Y-m-d');
                    
                    // Cari jam di sel sebelahnya atau sel yang sama
                    foreach ($rowArray as $timeCell) {
                        if (is_string($timeCell) && preg_match('/(\d{2}:\d{2}:\d{2})/', $timeCell, $timeMatches)) {
                            if ($currentUser) {
                                $userData[$currentUser->id][$date][] = $timeMatches[1];
                            }
                        }
                    }
                    break; // Sudah ketemu tanggal di baris ini
                }
            }
        }

        // 3. Proses Data Terkumpul
        foreach ($userData as $userId => $dates) {
            foreach ($dates as $date => $times) {
                sort($times);
                $checkIn = $times[0];
                $checkOut = count($times) > 1 ? end($times) : null;

                // Hitung TL & PSW
                $tlMinutes = 0;
                $pswMinutes = 0;

                $dtCheckIn = Carbon::parse($date . ' ' . $checkIn);
                $officeStart = Carbon::parse($date . ' 07:30:00');
                
                if ($dtCheckIn->gt($officeStart)) {
                    $tlMinutes = $dtCheckIn->diffInMinutes($officeStart);
                }

                if ($checkOut) {
                    $dtCheckOut = Carbon::parse($date . ' ' . $checkOut);
                    $dayOfWeek = Carbon::parse($date)->dayOfWeek;
                    
                    // Standar ASN: Jumat pulang 16:30, lainnya 16:00
                    $exitTime = ($dayOfWeek == Carbon::FRIDAY) ? '16:30:00' : '16:00:00';
                    $officeEnd = Carbon::parse($date . ' ' . $exitTime);

                    if ($dtCheckOut->lt($officeEnd)) {
                        $pswMinutes = $officeEnd->diffInMinutes($dtCheckOut);
                    }
                }

                // Cek apakah sedang Dinas Luar (Anti-Tertindih)
                $isDL = DB::table('assignment_letter_user')
                    ->join('assignment_letters', 'assignment_letter_user.assignment_letter_id', '=', 'assignment_letters.id')
                    ->where('assignment_letter_user.user_id', $userId)
                    ->where('assignment_letters.date', $date)
                    ->exists();

                $status = $isDL ? 'DL' : 'Hadir';
                if ($isDL) {
                    $tlMinutes = 0;
                    $pswMinutes = 0;
                }

                AttendanceLog::updateOrCreate(
                    ['user_id' => $userId, 'date' => $date],
                    [
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'tl_minutes' => $tlMinutes,
                        'psw_minutes' => $pswMinutes,
                        'status' => $status,
                        'notes' => $isDL ? 'Sistem: Otomatis sinkron DL.' : 'Sinkronisasi Log Mesin Absensi.',
                        'source' => 'Machine'
                    ]
                );
            }
        }
    }
}
