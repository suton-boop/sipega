<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\DailyAgenda;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalculateDailyPerformanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sipega:calculate-performance {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kalkulasi otomatis skor harian dan pembaruan kode warna performa 65 Pegawai BPMP';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Secara otomatis mengambil tanggal kemarin jika tidak dilempar argumen
        $calculationDate = $this->argument('date') ?? Carbon::yesterday()->format('Y-m-d');
        $this->info("Memulai kalkulasi performa (Tahap 5) untuk tanggal: {$calculationDate}...");

        $users = User::all();

        foreach ($users as $user) {
            $dailyScore = 100.00; // Skor awalan penuh, dipotong jika ada pelanggaran

            // A. Logika Potongan Absensi Biasa (TL & PSW)
            $attendance = AttendanceLog::where('user_id', $user->id)
                ->where('date', $calculationDate)
                ->first();

            if (!$attendance) {
                // Jika hari kerja (Senin-Jumat) dan tanpa Surat Tugas, potong berat! (Alpha)
                $dailyScore -= 50; 
                $this->warn("Pegawai {$user->name} Alpha (Tidak Absen).");
            } else {
                if ($attendance->tl_minutes > 0) {
                    // Potongan Tukin TL standar (Contoh simulasi bobot 0.5 per menit keterlambatan)
                    $dailyScore -= ($attendance->tl_minutes * 0.5);
                }
                if ($attendance->psw_minutes > 0) {
                    // Potongan Tukin PSW
                    $dailyScore -= ($attendance->psw_minutes * 0.5);
                }
            }

            // B. Logika Skor Agenda Harian (Deadline Jam 17:00 / 16:00 Puasa)
            $agenda = DailyAgenda::where('user_id', $user->id)
                ->where('date', $calculationDate)
                ->first();

            if (!$agenda) {
                $dailyScore -= 10; // Tidak input agenda harian
            } elseif ($agenda->status === 'Late') {
                $dailyScore -= 5; // Input telat melebihi deadline harian
            }

            // C. Logika Kehadiran Rapat (Bobot Terbesar - 70%)
            $meetingsToday = DB::table('meeting_logs')
                ->join('meetings', 'meeting_logs.meeting_id', '=', 'meetings.id')
                ->where('meetings.date', $calculationDate)
                ->where('meeting_logs.user_id', $user->id)
                ->count();
            
            $totalMeetingsToday = DB::table('meetings')->where('date', $calculationDate)->count();
            
            if ($totalMeetingsToday > 0 && $meetingsToday === 0) {
                // Ada rapat hari itu tapi pegawai tidak ikut / tidak scan QR/GPS valid
                $dailyScore -= 40; 
            }

            // Mencegah nilai negatif
            if ($dailyScore < 0) {
                $dailyScore = 0;
            }

            // D. Algoritma Kalkulasi Agregat/Akumulatif (Misal: 30% nilai historis + 70% nilai hari ini)
            $accumulatedScore = ($user->performance_score * 0.3) + ($dailyScore * 0.7);
            
            // Limitasi maksimum
            if ($accumulatedScore > 100) $accumulatedScore = 100.00;

            // E. Threshold Warna SIPEGA (Tahap 5)
            $color = 'Merah';
            if ($accumulatedScore >= 91) {
                $color = 'Biru';
            } elseif ($accumulatedScore >= 76) {
                $color = 'Hijau';
            } elseif ($accumulatedScore >= 61) {
                $color = 'Kuning';
            }

            // Update ke Profil Pegawai
            $user->update([
                'performance_score' => $accumulatedScore,
                'performance_color' => $color
            ]);

            $this->info("Pegawai {$user->name} mendapat akumulasi skor: {$accumulatedScore} ({$color})");
        }

        $this->info('Kalkulasi Selesai. Dashboard Top 5 siap dimuat.');
    }
}
