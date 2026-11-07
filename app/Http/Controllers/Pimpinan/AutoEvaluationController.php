<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\AutoEvaluationLog;
use App\Models\DailyAgenda;
use App\Models\User;
use App\Services\AutoEvaluationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AutoEvaluationController extends Controller
{
    protected AutoEvaluationService $autoEvaluationService;

    public function __construct(AutoEvaluationService $autoEvaluationService)
    {
        $this->autoEvaluationService = $autoEvaluationService;
    }

    /**
     * Pastikan Pengguna adalah Level Pimpinan (atau Super-Admin)
     */
    protected function authorizePimpinan(): void
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['Pimpinan', 'Admin'])) {
            abort(403, 'Akses Ditolak: Menu Otomatisasi Penilaian ini hanya dikhususkan bagi level Pimpinan.');
        }
    }

    /**
     * Halaman Dashboard & Konfigurasi Penilaian Otomatis (Level Pimpinan)
     */
    public function index(Request $request)
    {
        $this->authorizePimpinan();
        Carbon::setLocale('id');

        $settings = $this->autoEvaluationService->getSettings();
        $today = Carbon::today('Asia/Makassar')->toDateString();

        // Statistik Real-time Hari Ini
        $totalEmployees = User::realPegawai()->where('is_active', true)->count();
        $evaluatedToday = DailyAgenda::whereDate('date', $today)->whereNotNull('leader_rating')->count();
        $unratedToday = DailyAgenda::whereDate('date', $today)->whereNull('leader_rating')->count();
        $noAgendaToday = max(0, $totalEmployees - ($evaluatedToday + $unratedToday));

        $stats = [
            'total_employees' => $totalEmployees,
            'evaluated_today' => $evaluatedToday,
            'unrated_today' => $unratedToday,
            'no_agenda_today' => $noAgendaToday,
            'today_formatted' => Carbon::parse($today)->translatedFormat('l, d F Y'),
        ];

        // Riwayat Eksekusi
        $logs = AutoEvaluationLog::orderBy('executed_at', 'desc')->paginate(10);

        return view('pimpinan.auto_evaluation.index', compact('settings', 'stats', 'logs'));
    }

    /**
     * Simpan Perubahan Pengaturan Auto-Penilaian
     */
    public function update(Request $request)
    {
        $this->authorizePimpinan();

        $request->validate([
            'score' => 'required|integer|min:50|max:100',
            'time' => 'required|string',
            'scope' => 'required|in:all_unrated,unrated_submitted,all_employees',
            'feedback' => 'required|string|max:1000',
        ]);

        $this->autoEvaluationService->updateSettings([
            'enabled' => $request->has('enabled'),
            'score' => (int) $request->score,
            'time' => $request->time,
            'scope' => $request->scope,
            'feedback' => $request->feedback,
            'auto_realization' => $request->has('auto_realization'),
        ], auth()->user());

        $statusText = $request->has('enabled') ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';
        return back()->with('success', "Konfigurasi berhasil disimpan! Status Auto-Penilaian saat ini: {$statusText}.");
    }

    /**
     * Eksekusi Penilaian Otomatis Sekarang (On-Demand / Manual Trigger saat Kasubag Sibuk)
     */
    public function runNow(Request $request)
    {
        $this->authorizePimpinan();

        $targetDate = $request->input('target_date', Carbon::today('Asia/Makassar')->toDateString());

        $result = $this->autoEvaluationService->executeAutoEvaluation(
            $targetDate,
            'manual_instant',
            auth()->user()
        );

        return back()->with('success', "✓ Berhasil! Sebanyak {$result['count']} pegawai berhasil dinilai otomatis dengan skor {$result['score']} pada tanggal {$result['date']}.");
    }
}
