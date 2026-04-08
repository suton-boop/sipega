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
use Illuminate\Support\Facades\Route;

// Landing Page: Korporat & Institusional (SIPEGA-Elite)
Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // DASHBOARD Core SIPEGA
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // MODULE GLOBAL SETTINGS
    Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');

    // MODULE USER MANAGEMENT (Admin-Only Dashboard Features)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
    Route::get('/users/template', [UserController::class, 'downloadTemplate'])->name('users.template');
    Route::post('/users/recalculate/performance', [UserController::class, 'recalculatePerformance'])->name('users.recalculate-performance');

    // MODULE RBAC (Matriks Hak Akses / Role-Permission Management)
    Route::get('/rbac', [RolePermissionController::class, 'index'])->name('rbac.index');
    Route::post('/rbac/sync', [RolePermissionController::class, 'sync'])->name('rbac.sync');

    // MODULE REWARD (Wall of Fame / Wall of Shame)
    Route::get('/reward', [RewardController::class, 'index'])->name('reward.index');
    Route::post('/reward/vote', [RewardController::class, 'vote'])->name('reward.vote');

    // MODUL TUKIN: Berdasarkan Permendikbud 14/2022
    Route::prefix('tukin')->name('tukin.')->group(function () {
        Route::get('/', [TukinController::class, 'index'])->name('index');
        Route::get('/classes', [TukinController::class, 'classes'])->name('classes');
        Route::post('/classes', [TukinController::class, 'storeClass'])->name('classes.store');
        
        // Export & Reports
        Route::get('/export/excel', [TukinController::class, 'export'])->name('export');
        Route::get('/export/pdf/slip/{id?}', [TukinController::class, 'downloadSlip'])->name('download_slip');
        Route::get('/export/pdf/recap', [TukinController::class, 'downloadRecap'])->name('download_recap');
        Route::get('/export/pdf/payment-list', [TukinController::class, 'downloadPaymentList'])->name('download_payment_list');
    });

    // SISTEM PRESENSI (Antarmuka Rapat & Kedisiplinan)
    Route::get('/attendance', [MeetingController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [MeetingController::class, 'checkIn'])->name('attendance.check-in');
    
    // REKAP KEHADIRAN PERORANGAN (Sesuai Contoh Excel)
    Route::get('/attendance/recap/{id?}', [AttendanceController::class, 'recap'])->name('attendance.recap');
    Route::post('/attendance/import', [AttendanceController::class, 'importExcel'])->name('attendance.import');
    
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
    
    // MODUL MONITORING (Fokus Pimpinan/Leader Penilaian)
    Route::get('/leader/agenda', [AgendaController::class, 'leaderIndex'])->name('leader.agenda.index');
    Route::post('/leader/agenda/{id}/evaluate', [AgendaController::class, 'evaluate'])->name('leader.agenda.evaluate');
    Route::post('/leader/agenda/bulk-evaluate', [AgendaController::class, 'bulkEvaluate'])->name('leader.agenda.bulk-evaluate');

    // REKAP DINAS LUAR (External & Internal)
    Route::get('/recap/travel', [TravelRecapController::class, 'index'])->name('travel.recap');

    // SURAT-SURAT (SK/ST Admin-Pro)
    Route::resource('letters', LetterController::class);
    Route::get('/letters/{id}/st/pdf', [LetterController::class, 'downloadPdfSt'])->name('letters.pdf_st');
    Route::get('/letters/{id}/sk/pdf', [LetterController::class, 'downloadPdfSk'])->name('letters.pdf_sk');
    Route::post('/letters/{id}/approve', [LetterController::class, 'approve'])->name('letters.approve');
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

    // PROFILE
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
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
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        return "App Key Generated, Migrations & Seeding Done! <a href='/'>Go to Home</a>";
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
