<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JobClass;
use App\Models\Setting;
use App\Models\TukinCalculation;
use App\Services\TukinCalculatorService;
use App\Services\TukinService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TukinController extends Controller
{
    protected TukinCalculatorService $calculatorService;

    public function __construct(TukinCalculatorService $calculatorService)
    {
        $this->calculatorService = $calculatorService;
    }

    /**
     * Memeriksa apakah user memiliki hak akses Kepegawaian atau Pimpinan
     */
    public static function isPrivilegedUser(?User $user = null): bool
    {
        $user = $user ?: auth()->user();
        if (!$user) return false;

        $role = strtolower(trim($user->role ?? ''));
        $privilegedRoles = [
            'admin', 'administrator', 'kasubag', 'pimpinan', 
            'sekpri', 'operator', 'kepegawaian', 'superadmin', 
            'bendahara', 'ppk', 'kpa'
        ];

        if (in_array($role, $privilegedRoles)) {
            return true;
        }

        // Cek Spatie Roles jika ada
        if (method_exists($user, 'hasAnyRole')) {
            try {
                if ($user->hasAnyRole(['Admin', 'Pimpinan', 'Kasubag', 'Operator', 'Kepegawaian', 'admin', 'pimpinan', 'kasubag', 'operator'])) {
                    return true;
                }
            } catch (\Throwable $e) {}
        }

        // Cek jika Jabatan mengindikasikan Pimpinan / Pengelola Kepegawaian
        $position = strtolower(trim($user->position ?? ''));
        $privilegedPositions = ['kepala', 'pimpinan', 'kasubag', 'kepegawaian', 'admin', 'bendahara', 'ppk', 'kpa', 'subbagian umum'];
        foreach ($privilegedPositions as $pos) {
            if (str_contains($position, $pos)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Halaman Utama Menu Khusus Penghitungan Tukin Pegawai (Kepegawaian & Pimpinan)
     * atau Slip Tukin Pribadi untuk Pegawai Biasa.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isPrivileged = self::isPrivilegedUser($user);

        // Hanya pegawai biasa yang dibatasi oleh master switch is_tukin_active
        if (!$isPrivileged && Setting::get('is_tukin_active') === '0') {
            return redirect()->route('dashboard')->with('error', 'Modul Tukin sedang dinonaktifkan oleh Administrator.');
        }

        // Jika Pegawai biasa, arahkan ke tampilan slip personal
        if (!$isPrivileged) {
            $month = $request->get('month', Carbon::now('Asia/Makassar')->format('Y-m'));
            $tukin = $user->calculateMonthlyTukin($month);
            return view('tukin.pegawai', compact('user', 'tukin', 'month'));
        }

        // LEVEL KEPEGAWAIAN & PIMPINAN
        // Tentukan periode (Default: cari periode terbaru yang memiliki data, atau bulan ini)
        $latestPeriodInDb = TukinCalculation::max('period');
        $defaultPeriod = $latestPeriodInDb ?: Carbon::now('Asia/Makassar')->format('Y-m');
        $period = $request->get('period', $defaultPeriod);

        // Jika periode belum memiliki data, lakukan auto-calculate otomatis awal agar tabel langsung berisi data lengkap
        $existingCount = TukinCalculation::where('period', $period)->count();
        if ($existingCount === 0) {
            try {
                $this->calculatorService->calculatePeriodFromDatabase($period, auth()->id());
            } catch (\Throwable $e) {
                // Jangan gagalkan load halaman jika ada error saat kalkulasi awal
            }
        }

        // Ambil daftar seluruh periode yang sudah tersimpan di database
        $savedPeriods = TukinCalculation::select('period')
            ->distinct()
            ->orderBy('period', 'desc')
            ->pluck('period')
            ->toArray();

        if (!in_array($period, $savedPeriods) && !empty($period)) {
            array_unshift($savedPeriods, $period);
            $savedPeriods = array_unique($savedPeriods);
        }

        // Ambil data kalkulasi untuk periode terpilih
        $calculations = TukinCalculation::where('period', $period)
            ->orderByRaw('CAST(grade AS UNSIGNED) DESC')
            ->orderBy('name', 'asc')
            ->get();

        // Jika periode belum pernah dihitung/diimpor, berikan opsi kalkulasi dari presensi
        $hasData = $calculations->isNotEmpty();

        // Ringkasan Statistik
        $summary = [
            'total_pegawai' => $calculations->count(),
            'total_base' => $calculations->sum('base_amount'),
            'total_deduction' => $calculations->sum('total_deduction'),
            'total_bruto_after' => $calculations->sum('bruto_after_deduction'),
            'total_tax' => $calculations->sum('tax_amount'),
            'total_netto' => $calculations->sum('netto_amount'),
            'total_potongan_absen' => $calculations->sum('attendance_deduction'),
            'total_potongan_kinerja' => $calculations->sum('performance_deduction'),
            'pegawai_terpotong' => $calculations->where('total_penalty_percent', '>', 0)->count(),
        ];

        $users = User::realPegawai()->with('jobClass')->orderBy('name', 'asc')->get();
        $jobClasses = JobClass::orderByRaw('CAST(class_name AS UNSIGNED) DESC')->get();
        $officialRates = TukinCalculatorService::OFFICIAL_RATES;
        $tukinRules = TukinCalculatorService::getRules();
        $rates = TukinCalculatorService::getRates();

        return view('tukin.index', compact(
            'period',
            'savedPeriods',
            'calculations',
            'hasData',
            'summary',
            'users',
            'jobClasses',
            'officialRates',
            'tukinRules',
            'rates'
        ));
    }

    /**
     * Hitung Ulang Tukin dari Database Presensi SIPEGA (Berdasarkan Permendikdasmen 14/2025)
     */
    public function recalculate(Request $request)
    {
        if (!$this->isPrivilegedUser()) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Kepegawaian dan Pimpinan.');
        }

        $request->validate([
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/',
        ]);

        $period = $request->input('period');

        try {
            $result = $this->calculatorService->calculatePeriodFromDatabase($period, auth()->id());
            return redirect()->route('tukin.index', ['period' => $period])
                ->with('success', "Perhitungan Tukin berhasil dikalkulasi otomatis untuk {$result['total_users']} pegawai pada periode " . Carbon::parse($period . '-01')->translatedFormat('F Y') . " sesuai Permendikdasmen No. 14 Tahun 2025.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan kalkulasi: ' . $e->getMessage());
        }
    }

    /**
     * Import Data Tukin / Kehadiran dari File Excel (.xlsx, .xls, .csv)
     */
    public function importExcel(Request $request)
    {
        if (!$this->isPrivilegedUser()) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Kepegawaian dan Pimpinan.');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480',
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/',
        ], [
            'file.required' => 'Silakan pilih file Excel (.xlsx, .xls, atau .csv) untuk diimpor.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV.',
            'period.required' => 'Periode perhitungan (Tahun-Bulan) harus dipilih.',
        ]);

        $period = $request->input('period');
        $file = $request->file('file');

        try {
            $result = $this->calculatorService->importFromExcel($file, $period, auth()->id());

            $formatLabel = match($result['format']) {
                'log_harian' => 'Log Kehadiran Harian',
                'rekap_pembayaran' => 'Daftar Pembayaran Tukin',
                default => 'Rekapitulasi Matriks Ketidakhadiran'
            };

            return redirect()->route('tukin.index', ['period' => $period])
                ->with('success', "Import Excel Berhasil! Terdeteksi format [{$formatLabel}]. Sebanyak {$result['total_imported']} data pegawai berhasil dihitung dan disinkronkan untuk periode " . Carbon::parse($period . '-01')->translatedFormat('F Y') . ".");
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat memproses file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Download Template Excel Siap Pakai untuk Kepegawaian
     */
    public function downloadTemplate()
    {
        if (!$this->isPrivilegedUser()) {
            abort(403);
        }

        $spreadsheet = $this->calculatorService->generateTemplateSpreadsheet();
        $fileName = 'Template_Rekap_Tukin_Permendikdasmen_14_2025.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export Hasil Perhitungan Tukin ke File Excel Resmi BPMP
     */
    public function export(Request $request)
    {
        if (!$this->isPrivilegedUser()) {
            abort(403);
        }

        $period = $request->get('period', Carbon::now('Asia/Makassar')->format('Y-m'));

        // Jika belum ada data di database untuk periode ini, hitung dulu
        $count = TukinCalculation::where('period', $period)->count();
        if ($count === 0) {
            $this->calculatorService->calculatePeriodFromDatabase($period, auth()->id());
        }

        $spreadsheet = $this->calculatorService->generateCalculationExportSpreadsheet($period);
        $fileName = "Daftar_Pembayaran_Tukin_{$period}.xlsx";

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Update Manual Nilai Potongan Pegawai Tertentu (Dispensasi / Koreksi Khusus)
     */
    public function updateItem(Request $request, $id)
    {
        if (!$this->isPrivilegedUser()) {
            abort(403);
        }

        $calc = TukinCalculation::findOrFail($id);

        $validated = $request->validate([
            'grade' => 'nullable|numeric',
            'attendance_penalty_percent' => 'required|numeric|min:0|max:100',
            'performance_predicate' => 'required|string',
            'notes' => 'nullable|string|max:500',
        ]);

        if (!empty($validated['grade'])) {
            $calc->grade = (string)$validated['grade'];
            $calc->base_amount = TukinCalculatorService::getBaseTukinByGrade($validated['grade']);
        }

        $calc->attendance_penalty_percent = (float)$validated['attendance_penalty_percent'];
        $calc->attendance_deduction = round(($calc->attendance_penalty_percent / 100) * $calc->base_amount, 2);

        $calc->performance_predicate = $validated['performance_predicate'];
        $perfPct = match (strtolower($validated['performance_predicate'])) {
            'butuh perbaikan', 'cukup' => 20.0,
            'kurang', 'kurang (misconduct)' => 40.0,
            'sangat kurang' => 60.0,
            default => 0.0,
        };
        $calc->performance_penalty_percent = $perfPct;
        $calc->performance_deduction = round(($perfPct / 100) * $calc->base_amount, 2);

        $calc->total_penalty_percent = $calc->attendance_penalty_percent + $calc->performance_penalty_percent;
        $calc->total_deduction = $calc->attendance_deduction + $calc->performance_deduction;
        $calc->bruto_after_deduction = max(0, $calc->base_amount - $calc->total_deduction);

        $calc->tax_percent = TukinCalculation::getTaxRateByGolongan($calc->golongan);
        $calc->tax_amount = round(($calc->tax_percent / 100) * $calc->base_amount, 2);
        $calc->netto_amount = max(0, $calc->bruto_after_deduction);

        $calc->notes = $validated['notes'] ?? $calc->notes;
        $calc->source = 'manual_edited';
        $calc->save();

        return back()->with('success', "Data perhitungan Tukin untuk {$calc->name} berhasil diperbarui.");
    }

    /**
     * Hapus Seluruh Data Perhitungan untuk Periode Tertentu
     */
    public function deletePeriod(Request $request, $period)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan'])) {
            abort(403);
        }

        TukinCalculation::where('period', $period)->delete();
        return redirect()->route('tukin.index')
            ->with('success', "Seluruh data perhitungan Tukin periode {$period} berhasil dihapus.");
    }

    /**
     * Download FORMAL Payment List (PDF Landscape)
     */
    public function downloadPaymentList(Request $request)
    {
        if (!$this->isPrivilegedUser()) {
            abort(403);
        }

        $period = $request->get('period', Carbon::now('Asia/Makassar')->format('Y-m'));
        $month = Carbon::parse($period . '-01')->translatedFormat('F Y');

        $calculations = TukinCalculation::where('period', $period)
            ->orderByRaw('CAST(grade AS UNSIGNED) DESC')
            ->orderBy('name', 'asc')
            ->get();

        // Fallback jika belum pernah dihitung di tabel baru
        if ($calculations->isEmpty()) {
            $users = User::realPegawai()->with('jobClass')->orderBy('name', 'asc')->get();
            return Pdf::loadView('pdf.tukin_payment_list', compact('users', 'month'))
                      ->setPaper('a4', 'landscape')
                      ->download("Daftar_Pembayaran_Tukin_{$period}.pdf");
        }

        return Pdf::loadView('pdf.tukin_payment_list_calculated', compact('calculations', 'month', 'period'))
                  ->setPaper('a4', 'landscape')
                  ->download("Daftar_Pembayaran_Tukin_{$period}.pdf");
    }

    /**
     * Download DETAILED Recap (Lampiran Keuangan A3)
     */
    public function downloadRecap(Request $request)
    {
        if (!$this->isPrivilegedUser()) {
            abort(403);
        }

        $period = $request->get('period', Carbon::now('Asia/Makassar')->format('Y-m'));
        $month = Carbon::parse($period . '-01')->translatedFormat('F Y');

        $calculations = TukinCalculation::where('period', $period)
            ->orderByRaw('CAST(grade AS UNSIGNED) DESC')
            ->orderBy('name', 'asc')
            ->get();

        if ($calculations->isEmpty()) {
            $users = User::realPegawai()->with('jobClass')->orderBy('name', 'asc')->get();
            return Pdf::loadView('pdf.tukin_recap', compact('users', 'month'))
                      ->setPaper('a3', 'landscape')
                      ->download("Rekap_Detail_Tukin_Keuangan_{$period}.pdf");
        }

        return Pdf::loadView('pdf.tukin_recap_calculated', compact('calculations', 'month', 'period'))
                  ->setPaper('a3', 'landscape')
                  ->download("Rekap_Detail_Tukin_Keuangan_{$period}.pdf");
    }

    /**
     * Download Individual Tukin Slip (PDF)
     */
    public function downloadSlip(Request $request, $id = null)
    {
        $targetUser = $id ? User::findOrFail($id) : auth()->user();
        if (auth()->id() !== $targetUser->id && !$this->isPrivilegedUser()) {
            abort(403);
        }

        $period = $request->get('period', Carbon::now('Asia/Makassar')->format('Y-m'));
        $monthDate = Carbon::parse($period . '-01');

        // Cek apakah ada data di TukinCalculation
        $calcItem = TukinCalculation::where('period', $period)
            ->where(function($q) use ($targetUser) {
                $q->where('user_id', $targetUser->id)
                  ->orWhere('nip', $targetUser->nip);
            })->first();

        $tukin = (new TukinService())->calculateForUser($targetUser, $monthDate);
        $user = $targetUser;

        return Pdf::loadView('pdf.tukin_slip', compact('user', 'tukin', 'calcItem', 'period'))
                  ->download("Slip_Tukin_{$user->name}_{$period}.pdf");
    }

    /**
     * Manage Job Classes
     */
    public function classes()
    {
        if (!$this->isPrivilegedUser()) {
            abort(403);
        }

        $jobClasses = JobClass::orderByRaw('CAST(class_name AS UNSIGNED) DESC')->get();
        $officialRates = TukinCalculatorService::OFFICIAL_RATES;
        return view('tukin.job_classes', compact('jobClasses', 'officialRates'));
    }

    public function storeClass(Request $request)
    {
        if (!$this->isPrivilegedUser()) {
            abort(403);
        }

        $validated = $request->validate([
            'class_name' => 'required|string',
            'base_amount' => 'required|numeric|min:0'
        ]);

        JobClass::updateOrCreate(
            ['class_name' => $validated['class_name']],
            ['base_amount' => $validated['base_amount']]
        );

        return back()->with('success', 'Kelas Jabatan dan besaran Tukin berhasil diperbarui.');
    }
}
