<?php

namespace App\Console\Commands;

use App\Services\AutoEvaluationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoEvaluateAgendasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sipega:auto-evaluate {date? : Tanggal target format YYYY-MM-DD}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eksekusi otomatis penilaian seluruh pegawai jam 24:00 (Membantu Kasubag saat kesibukan tinggi)';

    /**
     * Execute the console command.
     */
    public function handle(AutoEvaluationService $service)
    {
        $settings = $service->getSettings();

        if (!$settings['enabled']) {
            $this->info('Fitur Auto-Penilaian SIPEGA sedang dinonaktifkan oleh Pimpinan. Eksekusi dilewati.');
            return Command::SUCCESS;
        }

        $targetDate = $this->argument('date') ?: Carbon::today('Asia/Makassar')->toDateString();
        $this->info("Menjalankan Auto-Penilaian SIPEGA untuk tanggal: {$targetDate} dengan nilai default: {$settings['score']}...");

        $result = $service->executeAutoEvaluation($targetDate, 'cron_24h', null);

        $this->info("✓ Sukses! Sebanyak {$result['count']} pegawai berhasil dinilai otomatis dengan skor {$result['score']}.");

        return Command::SUCCESS;
    }
}
