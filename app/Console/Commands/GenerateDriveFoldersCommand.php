<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Console\Command;

class GenerateDriveFoldersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sipega:generate-drive-folders {--force : Paksa generate ulang semua folder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate folder Google Drive untuk seluruh pegawai BPMP Kaltim tanpa batas waktu timeout';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== SIPEGA GOOGLE DRIVE FOLDER GENERATOR ===");

        $driveService = new GoogleDriveService();

        if (!$driveService->isConfigured()) {
            $this->error("Error: Google Drive API belum terkonfigurasi. Pastikan file service-account.json sudah ada.");
            return 1;
        }

        $masterFolder = Setting::get('google_drive_master_folder');
        if (empty($masterFolder)) {
            $this->error("Error: Master Folder belum disetel di Pengaturan Sistem.");
            return 1;
        }

        $cleanMasterId = GoogleDriveService::extractFolderId($masterFolder);
        $this->info("Master Folder ID: " . $cleanMasterId);
        $this->info("Service Account: " . $driveService->getServiceAccountEmail());

        $query = User::realPegawai();
        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('drive_folder_link')->orWhere('drive_folder_link', '');
            });
        }

        $employees = $query->get();
        $total = $employees->count();

        if ($total === 0) {
            $this->info("Semua pegawai sudah memiliki folder Google Drive masing-masing!");
            return 0;
        }

        $this->info("Ditemukan {$total} pegawai yang belum memiliki folder. Memulai pembuatan...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $successCount = 0;
        $failedList = [];

        foreach ($employees as $emp) {
            $folder = $driveService->generateUserFolder($emp, $cleanMasterId);
            if ($folder) {
                $successCount++;
            } else {
                $failedList[] = $emp->name;
            }
            $bar->advance();
            // Jeda 200ms untuk menghormati rate limit Google Drive API
            usleep(200000);
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("SELESAI! Berhasil membuat {$successCount} dari {$total} folder pegawai.");
        if (count($failedList) > 0) {
            $this->warn("Gagal membuat folder untuk: " . implode(', ', $failedList));
        }

        return 0;
    }
}
