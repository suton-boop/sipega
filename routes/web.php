<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\TukinController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\Admin\LetterController;
use App\Http\Controllers\AssignmentLetterController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TravelRecapController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DutySimulationController;
use App\Http\Controllers\TravelReportController;
use App\Http\Controllers\EmployeeReportController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\MikController;
use Illuminate\Support\Facades\Route;

// Landing Page: Korporat & Institusional (SIPEGA-Elite)
Route::get('/', function () {
    return view('welcome');
});

// ROUTE DEDIKASI UNTUK BERSIHKAN CACHE TAMPILAN & KONFIGURASI DARI BROWSER
Route::get('/clear-cache', function () {
    try {
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
        } catch (\Throwable $e) {}
        
        // Bersihkan manual file views jika ada permission issue di artisan
        $viewFiles = glob(storage_path('framework/views/*.php'));
        if ($viewFiles) {
            foreach ($viewFiles as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }

        // Cek Git Commit Hash yang aktif di server
        $gitHead = 'Unknown';
        if (file_exists(base_path('.git/HEAD'))) {
            $headContent = trim(file_get_contents(base_path('.git/HEAD')));
            if (str_starts_with($headContent, 'ref: ')) {
                $refFile = base_path('.git/' . substr($headContent, 5));
                if (file_exists($refFile)) {
                    $gitHead = substr(trim(file_get_contents($refFile)), 0, 7);
                }
            } else {
                $gitHead = substr($headContent, 0, 7);
            }
        }

        // Reset permission cache Spatie & pastikan seluruh permission sistem terdaftar
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            foreach (array_keys(\App\Http\Controllers\RolePermissionController::PERMISSIONS_METADATA) as $p) {
                \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            }
        } catch (\Throwable $e) {}

        // Otomatis perbaiki link lama di database yang masih menunjuk ke /storage/ agar langsung menggunakan route view-pdf aman (Anti-403)
        try {
            \App\Models\DailyAgendaItem::where('drive_link', 'like', '%/storage/%')->get()->each(function($it) {
                $it->update(['drive_link' => route('agenda.item.view_pdf', $it->id)]);
            });
        } catch (\Throwable $e) {}

        // Cek status & diagnosis Google Drive Service Account
        $driveService = new \App\Services\GoogleDriveService();
        $user = auth()->user() ?: \App\Models\User::where('nip', '198109282002121005')->first();
        $diag = $driveService->diagnoseDrive($user);
        
        $diagHtml = '';
        foreach ($diag['steps'] as $st) {
            $icon = $st['status'] ? '<span style="color:#059669;font-weight:900;">✓</span>' : '<span style="color:#e11d48;font-weight:900;">✕</span>';
            $bgColor = $st['status'] ? '#ecfdf5' : '#fff1f2';
            $borderColor = $st['status'] ? '#a7f3d0' : '#fecdd3';
            $diagHtml .= "
                <div style='display:flex;align-items:flex-start;gap:10px;padding:8px 12px;background:{$bgColor};border:1px solid {$borderColor};border-radius:8px;margin-bottom:6px;font-size:12px;'>
                    <div style='font-size:14px;line-height:1;'>{$icon}</div>
                    <div style='flex:1;'>
                        <div style='font-weight:bold;color:#1e293b;'>{$st['name']}</div>
                        <div style='color:#475569;margin-top:2px;'>{$st['message']}</div>
                    </div>
                </div>
            ";
        }
        
        return response('
            <div style="font-family: sans-serif; text-align: center; padding: 40px 15px; background: #f8fafc; min-height: 100vh;">
                <div style="display: inline-block; background: white; padding: 35px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 600px; text-align: left; width: 100%;">
                    <div style="text-align: center; font-size: 44px; margin-bottom: 5px;">⚡</div>
                    <h2 style="color: #003366; margin-bottom: 5px; font-weight: 900; text-align: center;">Diagnosis SIPEGA & Google Drive</h2>
                    <p style="color: #64748b; font-size: 13px; line-height: 1.5; text-align: center; margin-bottom: 15px;">Hasil pengecekan koneksi live dari server ke Google Drive Anda:</p>
                    
                    <div style="background:#f1f5f9;border-radius:10px;padding:10px 14px;margin-bottom:15px;font-size:11px;color:#334155;line-height:1.6;">
                        <div><strong>📁 Folder Server:</strong> <code>' . base_path() . '</code></div>
                        <div><strong>🔖 Versi Commit:</strong> <span style="background:#003366;color:white;padding:1px 6px;border-radius:4px;font-weight:bold;">' . $gitHead . '</span> ' . ($gitHead === 'a7f7bc3' || $gitHead === '3797068' ? '(Versi Terbaru ✓)' : '<span style="color:#e11d48;font-weight:bold;">(Bukan Versi Terbaru, butuh git pull di folder ini!)</span>') . '</div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        ' . $diagHtml . '
                    </div>

                    <div style="text-align: center; margin-top: 25px; display: flex; gap: 10px; justify-content: center;">
                        <a href="/agenda?refresh=1" style="background: #003366; color: white; text-decoration: none; padding: 12px 24px; border-radius: 12px; font-weight: bold; font-size: 13px;">Buka Halaman Agenda (Paksa Segarkan) →</a>
                        <a href="/settings#drive" style="background: #f1f5f9; color: #334155; text-decoration: none; padding: 12px 20px; border-radius: 12px; font-weight: bold; font-size: 13px;">Smart Pengaturan ⚙</a>
                    </div>
                </div>
            </div>
        ');
    } catch (\Exception $e) {
        return 'Gagal bersihkan cache: ' . $e->getMessage();
    }
});

// ROUTE DIRECT VIEW PDF (ANTI 403 & AKSES PUBLIK E-KINERJA)
Route::get('/agenda/item/{id}/view-pdf', [AgendaController::class, 'viewItemPdf'])->name('agenda.item.view_pdf');
Route::get('/storage/agendas/{filename}', function ($filename) {
    $path = storage_path('app/public/agendas/' . $filename);
    if (!file_exists($path)) {
        abort(404, 'Berkas bukti dukung tidak ditemukan.');
    }
    return response()->file($path, [
        'Content-Type' => mime_content_type($path) ?: 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
    ]);
})->where('filename', '.*');

Route::middleware(['auth', 'verified'])->group(function () {
    // DASHBOARD Core SIPEGA
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // MODULE SMART PENGATURAN & ATURAN REGULASI
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/smartpengaturan', [SettingController::class, 'index'])->name('smartpengaturan');
    Route::get('/smart-pengaturan', [SettingController::class, 'index']);
    Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/drive/credentials', [SettingController::class, 'saveDriveCredentials'])->name('settings.drive.credentials');
    Route::post('/settings/drive/generate-all', [SettingController::class, 'generateAllEmployeeFolders'])->name('settings.drive.generate_all');
    Route::post('/settings/drive/oauth/config', [SettingController::class, 'saveGoogleOAuthConfig'])->name('settings.google.oauth.config');
    Route::get('/settings/drive/oauth/connect', [SettingController::class, 'connectGoogleOAuth'])->name('settings.google.oauth.connect');
    Route::get('/settings/google/oauth/callback', [SettingController::class, 'handleGoogleOAuthCallback'])->name('settings.google.oauth.callback');
    Route::post('/settings/drive/oauth/disconnect', [SettingController::class, 'disconnectGoogleOAuth'])->name('settings.google.oauth.disconnect');
    Route::post('/settings/tukin', [SettingController::class, 'updateTukinRules'])->name('settings.tukin.update');
    Route::post('/settings/tukin/reset', [SettingController::class, 'resetTukinRules'])->name('settings.tukin.reset');
    Route::post('/settings/menus', [SettingController::class, 'updateMenus'])->name('settings.menus.update');

    // MODULE USER MANAGEMENT (Admin-Only Dashboard Features)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
    Route::get('/users/template', [UserController::class, 'downloadTemplate'])->name('users.template');
    Route::post('/users/recalculate/performance', [UserController::class, 'recalculatePerformance'])->name('users.recalculate-performance');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // MODULE RBAC (Matriks Hak Akses / Role-Permission Management)
    Route::get('/rbac', [RolePermissionController::class, 'index'])->name('rbac.index');
    Route::post('/rbac/sync', [RolePermissionController::class, 'sync'])->name('rbac.sync');
    Route::post('/rbac/reset', [RolePermissionController::class, 'resetDefaults'])->name('rbac.reset');

    // MODULE REWARD (Wall of Fame / Wall of Shame)
    Route::get('/reward', [RewardController::class, 'index'])->name('reward.index');
    Route::post('/reward/vote', [RewardController::class, 'vote'])->name('reward.vote');

    // MODUL TUKIN: Berdasarkan Permendikdasmen 14/2025 & Perpres 18/2025
    Route::get('/hitung-tukin', [TukinController::class, 'index'])->name('tukin.calculate');
    Route::get('/tukin/hitung', [TukinController::class, 'index']);
    Route::prefix('tukin')->name('tukin.')->group(function () {
        Route::get('/', [TukinController::class, 'index'])->name('index');
        Route::get('/pegawai', [TukinController::class, 'myTukin'])->name('pegawai');
        Route::get('/saya', [TukinController::class, 'myTukin'])->name('my');
        Route::post('/recalculate', [TukinController::class, 'recalculate'])->name('recalculate');
        Route::post('/import-excel', [TukinController::class, 'importExcel'])->name('import_excel');
        Route::get('/template', [TukinController::class, 'downloadTemplate'])->name('template');
        Route::put('/items/{id}', [TukinController::class, 'updateItem'])->name('update_item');
        Route::delete('/period/{period}', [TukinController::class, 'deletePeriod'])->name('delete_period');
        Route::get('/classes', [TukinController::class, 'classes'])->name('classes');
        Route::post('/classes', [TukinController::class, 'storeClass'])->name('classes.store');
        Route::post('/approve-final', [TukinController::class, 'approveFinal'])->name('approve_final');
        Route::post('/unapprove-final', [TukinController::class, 'unapproveFinal'])->name('unapprove_final');
        
        // Export & Reports
        Route::get('/export/excel', [TukinController::class, 'export'])->name('export');
        Route::get('/export/pdf/slip/{id?}', [TukinController::class, 'downloadSlip'])->name('download_slip');
        Route::get('/export/pdf/recap', [TukinController::class, 'downloadRecap'])->name('download_recap');
        Route::get('/export/pdf/payment-list', [TukinController::class, 'downloadPaymentList'])->name('download_payment_list');
    });

    // SISTEM PRESENSI (Antarmuka Rapat & Kedisiplinan)
    Route::get('/attendance', [MeetingController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [MeetingController::class, 'checkIn'])->name('attendance.check-in');
    
    // REKAP KEHADIRAN PERORANGAN (Sesuai Contoh Excel & Cetak PDF)
    Route::get('/attendance/recap/{id?}', [AttendanceController::class, 'recap'])->name('attendance.recap');
    Route::get('/attendance/recap-pdf/{id?}', [AttendanceController::class, 'downloadPdf'])->name('attendance.download_pdf');
    Route::post('/attendance/import', [AttendanceController::class, 'importExcel'])->name('attendance.import');
    Route::get('/attendance/import-log', [AttendanceController::class, 'importView'])->name('attendance.import.index');
    Route::post('/attendance/import-log', [AttendanceController::class, 'processImport'])->name('attendance.import.process');

    // KELOLA KEHADIRAN & KETERANGAN PEGAWAI (Console Admin)
    Route::get('/admin/attendance/manage', [AttendanceController::class, 'manageIndex'])->name('admin.attendance.manage');
    Route::post('/admin/attendance/update-log', [AttendanceController::class, 'updateLog'])->name('admin.attendance.update_log');
    Route::post('/admin/attendance/recalculate-month', [AttendanceController::class, 'recalculateMonth'])->name('admin.attendance.recalculate_month');
    Route::post('/admin/attendance/recalculate-all', [AttendanceController::class, 'recalculateAllEmployees'])->name('admin.attendance.recalculate_all');
    
    // SISTEM RAPAT & LOKASI (Digital QR & Geo-Fencing)
    Route::prefix('admin')->name('admin.')->group(function() {
        // Resource Meetings (index, store, update, destroy)
        Route::resource('meetings', MeetingController::class);
        
        // Custom Meeting Routes (Sync with View Names)
        Route::get('/meetings/{id}/qr', [MeetingController::class, 'showQr'])->name('meetings.qr');
        Route::get('/meetings/{id}/print-qr', [MeetingController::class, 'printQr'])->name('meetings.print-qr');
        Route::post('/meetings/{id}/minutes', [MeetingController::class, 'updateMinutes'])->name('meetings.minutes');
        
        // Finalized Download Routes (Sesuai View index.blade.php)
        Route::get('/meetings/{id}/download-attendance', [MeetingController::class, 'downloadAttendance'])->name('meetings.download-attendance');
        Route::get('/meetings/{id}/download-minutes', [MeetingController::class, 'downloadMinutes'])->name('meetings.download-minutes');

        // Locations
        Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
    });

    // AGENDA HARIAN & MONITORING PEMBINAAN
    Route::resource('agenda', AgendaController::class);
    Route::post('/agenda/{id}/realization', [AgendaController::class, 'updateRealization'])->name('agenda.realization');
    Route::post('/agenda/quick-add', [AgendaController::class, 'quickAddItem'])->name('agenda.quick_add');
    Route::delete('/agenda/item/{id}', [AgendaController::class, 'deleteItem'])->name('agenda.item.destroy');
    Route::get('/agenda/item/{id}/pdf', [AgendaController::class, 'downloadItemPdf'])->name('agenda.item.pdf');
    Route::post('/agenda/item/{id}/generate-pdf', [AgendaController::class, 'regenerateItemPdf'])->name('agenda.item.generate_pdf');
    Route::post('/agenda/sync-drive-all', [AgendaController::class, 'syncAllToDrive'])->name('agenda.sync_drive_all');
    Route::post('/agenda/rhk', [AgendaController::class, 'storeRhk'])->name('agenda.rhk.store');
    Route::delete('/agenda/rhk/{id}', [AgendaController::class, 'deleteRhk'])->name('agenda.rhk.destroy');
    Route::post('/agenda/drive-folder', [AgendaController::class, 'updateDriveFolder'])->name('agenda.drive_folder.update');
    Route::post('/agenda/generate-my-folder', [AgendaController::class, 'generateMyFolder'])->name('agenda.generate_my_folder');
    Route::get('/drive/my-folder', [AgendaController::class, 'openMyDriveFolder'])->name('drive.my_folder');
    
    // MODUL MONITORING (Fokus Pimpinan/Leader Penilaian)
    Route::get('/leader/agenda', [AgendaController::class, 'leaderIndex'])->name('leader.agenda.index');
    Route::post('/leader/agenda/{id}/evaluate', [AgendaController::class, 'evaluate'])->name('leader.agenda.evaluate');
    Route::post('/leader/agenda/bulk-evaluate', [AgendaController::class, 'bulkEvaluate'])->name('leader.agenda.bulk-evaluate');

    // REKAP DINAS LUAR (External & Internal)
    Route::get('/recap/travel', [TravelRecapController::class, 'index'])->name('travel.recap');

    // RAPORT & CAPAIAN PEGAWAI (Console)
    Route::get('/recap/performance', [EmployeeReportController::class, 'index'])->name('performance.recap');
    Route::post('/recap/performance/recalculate', [EmployeeReportController::class, 'recalculate'])->name('performance.recalculate');
    Route::get('/recap/performance/pdf', [EmployeeReportController::class, 'exportPdf'])->name('performance.pdf');

    // SIMULASI SURAT TUGAS (Console Perencanaan Tugas)
    Route::get('/duty-simulation', [DutySimulationController::class, 'index'])->name('duty-simulation.index');

    // SURAT-SURAT (SK/ST Admin-Pro)
    Route::get('/letters/check-conflicts', [LetterController::class, 'checkConflicts'])->name('letters.check_conflicts');
    Route::resource('letters', LetterController::class);
    Route::get('/letters/{id}/st/pdf', [LetterController::class, 'downloadPdfSt'])->name('letters.pdf_st');
    Route::get('/letters/{id}/sk/pdf', [LetterController::class, 'downloadPdfSk'])->name('letters.pdf_sk');
    Route::post('/letters/{id}/approve', [LetterController::class, 'approve'])->name('letters.approve');
    Route::post('/letters/{id}/unapprove', [LetterController::class, 'unapprove'])->name('letters.unapprove');
    Route::post('/letters/{id}/reject', [LetterController::class, 'reject'])->name('letters.reject');

    // KALENDER KERJA
    Route::get('/calendar', [CalendarController::class, 'index'])->name('admin.calendar.index');
    Route::post('/calendar', [CalendarController::class, 'store'])->name('admin.calendar.store');
    Route::post('/calendar/import', [CalendarController::class, 'import'])->name('admin.calendar.import');

    // SIPEGA-ASSIGN (Manajemen Surat Tugas Pegawai)
    Route::post('/assign/store', [AssignmentLetterController::class, 'store'])->name('assign.store');
    Route::get('/assign/pdf/{id}', [AssignmentLetterController::class, 'generatePdf'])->name('assign.pdf');

    // INDIVIDUAL SCHEDULES (Agenda Kegiatan Individu)
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

    // MODULE BUKTI FISIK & SKP SUPPORT
    Route::get('/evidence', [\App\Http\Controllers\EvidenceController::class, 'index'])->name('evidence.index');
    Route::post('/evidence/{itemId}', [\App\Http\Controllers\EvidenceController::class, 'update'])->name('evidence.update');
    Route::get('/evidence/download/pdf', [\App\Http\Controllers\EvidenceController::class, 'downloadPdf'])->name('evidence.download');

    // PUSAT DOKUMEN FISIK
    Route::get('/my-documents', [\App\Http\Controllers\UserDocumentController::class, 'index'])->name('documents.index');

    // PROFILE
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // QR CODE GENERATOR (Layanan Mandiri)
    Route::get('/qrcode', [\App\Http\Controllers\QRCodeController::class, 'index'])->name('qrcode.index');

    // MODUL LAPORAN PERJALANAN DINAS (Laporan Perjadin / Dinas Luar)
    Route::get('/travel-reports/{id}/pdf', [TravelReportController::class, 'downloadPdf'])->name('travel-reports.pdf');
    Route::get('/travel-reports/{id}/doc', [TravelReportController::class, 'downloadDoc'])->name('travel-reports.doc');
    Route::resource('travel-reports', TravelReportController::class);

    // MODUL LAYANAN CUTI ASN (Sesuai UU No. 20/2023 & PerBKN No. 24/2017 jo. 7/2021 & 7/2022)
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/create', [LeaveController::class, 'create'])->name('create');
        Route::post('/', [LeaveController::class, 'store'])->name('store');
        Route::post('/ai-check', [LeaveController::class, 'aiCheck'])->name('ai_check');
        Route::get('/approvals', [LeaveController::class, 'approvals'])->name('approvals');
        Route::post('/{id}/process-approval', [LeaveController::class, 'processApproval'])->name('process_approval');
        Route::get('/balances', [LeaveController::class, 'manageBalances'])->name('balances');
        Route::put('/balances/{id}', [LeaveController::class, 'updateBalance'])->name('update_balance');
        Route::post('/settings/concurrent', [LeaveController::class, 'updateConcurrentSettings'])->name('update_concurrent_settings');
        Route::get('/{id}', [LeaveController::class, 'show'])->name('show');
        Route::get('/{id}/print', [LeaveController::class, 'print'])->name('print');
        Route::get('/{id}/pdf', [LeaveController::class, 'downloadPdf'])->name('pdf');
    });

    // MODUL MiK (Monitoring Izin Karyawan - Keluar Kantor & Keluar Ruangan)
    // Berorientasi pada ketertiban dan TIDAK mempengaruhi perhitungan Tukin / skor performa
    Route::prefix('mik')->name('mik.')->group(function () {
        Route::get('/', [MikController::class, 'index'])->name('index');
        Route::get('/form-kantor', [MikController::class, 'formKantor'])->name('form_kantor');
        Route::get('/form-ruangan', [MikController::class, 'formRuangan'])->name('form_ruangan');
        Route::post('/', [MikController::class, 'store'])->name('store');
        Route::post('/{id}/return', [MikController::class, 'markReturned'])->name('return');
        Route::post('/{id}/cancel', [MikController::class, 'cancel'])->name('cancel');
        Route::get('/approvals', [MikController::class, 'approvals'])->name('approvals');
        Route::post('/{id}/approve', [MikController::class, 'processApproval'])->name('process_approval');
    });

    // MODUL KHUSUS PIMPINAN: OTOMATISASI PENILAIAN PEGAWAI (Auto-Assess saat Kasubag Sibuk)
    Route::prefix('pimpinan/auto-evaluation')->name('pimpinan.auto_evaluation.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Pimpinan\AutoEvaluationController::class, 'index'])->name('index');
        Route::post('/update', [\App\Http\Controllers\Pimpinan\AutoEvaluationController::class, 'update'])->name('update');
        Route::post('/run-now', [\App\Http\Controllers\Pimpinan\AutoEvaluationController::class, 'runNow'])->name('run_now');
    });

    // ========================================================
    // MODUL MANAJEMEN PIKET TAHUNAN ULT & PERPUSTAKAAN (SIM SPP)
    // ========================================================
    Route::prefix('piket')->name('piket.')->group(function () {
        // 1. Dashboard Piket
        Route::get('/', [\App\Http\Controllers\Piket\PiketDashboardController::class, 'index'])->name('dashboard');

        // 2. Data Petugas Piket
        Route::prefix('officers')->name('officers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Piket\PiketOfficerController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Piket\PiketOfficerController::class, 'store'])->name('store');
            Route::put('/{id}', [\App\Http\Controllers\Piket\PiketOfficerController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Piket\PiketOfficerController::class, 'destroy'])->name('destroy');
        });

        // 3 & 4 & 9 & 12: Pengaturan Jadwal Tahunan, Kalender, Penerbitan, Pengaturan Unit
        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Piket\PiketScheduleController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Piket\PiketScheduleController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Piket\PiketScheduleController::class, 'store'])->name('store');
            Route::get('/calendar', [\App\Http\Controllers\Piket\PiketScheduleController::class, 'calendar'])->name('calendar');
            Route::get('/settings', [\App\Http\Controllers\Piket\PiketScheduleController::class, 'settings'])->name('settings');
            Route::put('/settings/{id}', [\App\Http\Controllers\Piket\PiketScheduleController::class, 'updateUnitSettings'])->name('update_unit_settings');
            Route::get('/{id}', [\App\Http\Controllers\Piket\PiketScheduleController::class, 'show'])->name('show');
            Route::post('/{id}/audit', [\App\Http\Controllers\Piket\PiketScheduleController::class, 'auditConflicts'])->name('audit');
            Route::get('/{id}/publish', [\App\Http\Controllers\Piket\PiketScheduleController::class, 'publishForm'])->name('publish_form');
            Route::post('/{id}/publish', [\App\Http\Controllers\Piket\PiketScheduleController::class, 'publish'])->name('publish');
            Route::get('/{id}/pdf', [\App\Http\Controllers\Piket\PiketScheduleController::class, 'downloadPdf'])->name('pdf');
        });

        // 5 & 6: Penugasan Kedinasan & Deteksi Konflik
        Route::prefix('conflicts')->name('conflicts.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Piket\PiketConflictController::class, 'index'])->name('index');
            Route::get('/dinas', [\App\Http\Controllers\Piket\PiketConflictController::class, 'dinas'])->name('dinas');
        });

        // 7: Penggantian Petugas (3 Tingkat Prioritas)
        Route::prefix('replacements')->name('replacements.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Piket\PiketReplacementController::class, 'index'])->name('index');
            Route::get('/create/{assignmentId}', [\App\Http\Controllers\Piket\PiketReplacementController::class, 'create'])->name('create');
            Route::post('/{assignmentId}', [\App\Http\Controllers\Piket\PiketReplacementController::class, 'store'])->name('store');
        });

        // 8: Pengajuan Tukar Jadwal
        Route::prefix('swaps')->name('swaps.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Piket\PiketSwapController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Piket\PiketSwapController::class, 'store'])->name('store');
            Route::post('/{id}/approval', [\App\Http\Controllers\Piket\PiketSwapController::class, 'processApproval'])->name('approval');
        });

        // 10: Monitoring Pelaksanaan & Presensi
        Route::prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Piket\PiketMonitoringController::class, 'index'])->name('index');
            Route::post('/{assignmentId}', [\App\Http\Controllers\Piket\PiketMonitoringController::class, 'recordAttendance'])->name('record');
        });

        // 11: Laporan & Rekapitulasi
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Piket\PiketReportController::class, 'index'])->name('index');
            Route::get('/pdf', [\App\Http\Controllers\Piket\PiketReportController::class, 'downloadPdf'])->name('pdf');
        });
    });
});

// TEMPORARY FIX ROUTE FOR HOSTING (visit: /fix-app)
Route::get('/fix-app', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('key:generate');
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true, '--seed' => true]);
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
        } catch (\Exception $e) {
            // Silently fail if link exists or restricted
        }
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        return "App Key, Migrations, Route & View Cache Cleared! <a href='/'>Go to Home</a>";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// MANUAL SYMLINK FOR SHARED HOSTING
Route::get('/manual-link', function () {
    $target = storage_path('app/public');
    $link = public_path('storage');
    
    if (file_exists($link)) {
        return "ALREADY EXISTS: Folder 'public/storage' sudah ada. Silakan HAPUS folder tersebut melalui File Manager di hosting Anda terlebih dahulu agar skrip ini bisa membuat link yang baru.";
    }

    try {
        if (symlink($target, $link)) {
            return "SUCCESS: Link storage berhasil dibuat! Silakan cek foto Anda.";
        }
    } catch (\Exception $e) {
        return "ERROR: Gagal membuat link. Penyebab: " . $e->getMessage() . ". Kemungkinan fungsi 'symlink' dilarang oleh hosting Anda.";
    }
    
    return "UNKNOWN ERROR.";
});

// DEBUG DB CONNECTION (visit: /db-test)
Route::get('/db-test', function() {
    try {
        \DB::connection()->getPdo();
        return "Database connection is working!";
    } catch (\Exception $e) {
        return "Could not connect to the database. Error: " . $e->getMessage();
    }
});

require __DIR__.'/auth.php';
