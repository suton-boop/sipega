<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyAgenda;
use App\Models\DailyAgendaItem;
use App\Models\Setting;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AgendaController extends Controller
{
    /**
     * Halaman Utama Agenda & Realisasi Kerja (SIPEGA-Agenda)
     */
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today()->format('Y-m-d');
        $myAgendaToday = DailyAgenda::with('items')->where('user_id', $user->id)->where('date', $today)->first();

        // Get upcoming meetings/agenda for suggestions
        $upcomingMeetings = \App\Models\Meeting::where('date', $today)
            ->where(function($query) use ($user) {
                $query->where('target_type', 'All')
                      ->orWhereHas('participants', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->get();

        // Get individual schedules for suggestions
        $mySchedules = \App\Models\EmployeeSchedule::where('user_id', $user->id)
            ->where('date', $today)
            ->get();
        
        return view('agenda.index', compact('myAgendaToday', 'user', 'upcomingMeetings', 'mySchedules'));
    }



    /**
     * Store (Submit) Agenda Harian & Foto Bukti
     */
    public function store(Request $request)
    {


        $user = auth()->user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now('Asia/Makassar');
        $hour = (int) $now->format('H');

        // --- Logic 1: Siklus Harian SIPEGA ---
        // Plan: Usually morning (before 09:00). Late after that. Closed after 17:00.
        $status = 'Submitted';
        if ($hour >= 9) {
            $status = 'Late'; 
        }
        
        $isFlexible = Setting::get('is_realization_open_anytime') === '1';

        // Peringatan: Sistem mengunci input setelah jam 17:00 (Kaltim/WITA)
        if (($hour < 7 || $hour >= 17) && !$isFlexible) {
            return back()->with('error', 'SIPEGA: Pengisian rencana agenda harian hanya tersedia pukul 07:00 - 17:00 WITA.');
        }

        // Cek kalau udah pernah submit hari ini
        $existing = DailyAgenda::where('user_id', $user->id)->where('date', $today)->first();
        if ($existing) {
            return back()->with('error', 'Anda sudah men-submit rencana agenda untuk hari ini!');
        }

        $request->validate([
            'plans' => 'required|array|min:1',
            'plans.*' => 'required|string|min:5'
        ]);

        // Simpan Parent Agenda (Tanpa Lampiran di Pagi Hari)
        $agenda = DailyAgenda::create([
            'user_id' => $user->id,
            'date' => $today,
            'activity_plan' => implode("\n", $request->plans),
            'proof_file_path' => null, 
            'submitted_at' => $now,
            'status' => $status
        ]);

        // Simpan Item Rincian
        foreach ($request->plans as $plan) {
            DailyAgendaItem::create([
                'daily_agenda_id' => $agenda->id,
                'plan_description' => $plan,
                'status' => 'pending'
            ]);
        }

        return back()->with('success', 'Rencana Agenda Pagi SIPEGA berhasil disimpan. Anda memiliki ' . count($request->plans) . ' kegiatan hari ini!');
    }

    /**
     * Siklus Sore (15:00 - 17:00): Update Status Per Item & Lampiran (Opsional)
     */
    public function updateRealization(Request $request, $id)
    {
        $user = auth()->user();
        $now = \Carbon\Carbon::now('Asia/Makassar');
        $hour = (int) $now->format('H');
        
        $isFlexible = Setting::get('is_realization_open_anytime') === '1';

        if (!$isFlexible) {
            if ($hour < 15 || $hour >= 17) {
                return back()->with('error', 'SIPEGA: Realisasi (Evaluasi Sore) hanya dapat dilakukan pada pukul 15:00 - 17:00 WITA.');
            }
        }

        $agenda = DailyAgenda::findOrFail($id);
        
        $request->validate([
            'items' => 'required|array',
            'items.*.status' => 'required|in:completed,changed,progress',
            'items.*.notes' => 'nullable|string',
            'proof_file' => 'nullable|image|max:2048'
        ]);

        // PROSES LAMPIRAN (OPSIONAL DI SORE HARI)
        $dbPath = $agenda->proof_file_path;
        if ($request->hasFile('proof_file')) {
            try {
                $file = $request->file('proof_file');
                $filename = 'realization_' . $user->id . '_' . time() . '.jpg';
                $path = storage_path('app/public/agendas/' . $filename);

                if (!file_exists(storage_path('app/public/agendas'))) {
                    mkdir(storage_path('app/public/agendas'), 0777, true);
                }

                $manager = new ImageManager(new Driver());
                $image = $manager->read($file->getRealPath());
                $image->scaleDown(width: 1080);

                $watermarkText = "SIPEGA REALISASI\nNama: " . $user->name . "\nWaktu: " . $now->format('d/m/Y H:i:s') . " WITA";
                $image->drawRectangle(10, 10, function ($rectangle) {
                    $rectangle->size(400, 70);
                    $rectangle->background('rgba(0,0,0,0.5)');
                });
                $image->text($watermarkText, 20, 30, function($font) {
                    $font->file(3);
                    $font->color('#ffffff');
                    $font->align('left');
                    $font->valign('top');
                    $font->lineHeight(1.5);
                });
                $image->toJpeg(80)->save($path);
                $dbPath = 'agendas/' . $filename;
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal memproses lampiran: ' . $e->getMessage());
            }
        }

        foreach ($request->items as $itemId => $data) {
            $item = DailyAgendaItem::where('id', $itemId)->where('daily_agenda_id', $agenda->id)->first();
            if ($item) {
                $item->update([
                    'status' => $data['status'],
                    'realization_notes' => $data['notes']
                ]);
            }
        }

        $agenda->update([
            'activity_realization' => 'Verified Daily Items',
            'proof_file_path' => $dbPath,
            'realization_submitted_at' => $now
        ]);

        return back()->with('success', 'Evaluasi harian SIPEGA berhasil disimpan. Terima kasih atas laporannya!');
    }

    /**
     * Halaman Monitoring & Penilaian Kerja (Untuk Pimpinan/Admin/Kasubag)
     */
    public function leaderIndex()
    {
        $user = auth()->user();
        // Hanya Pimpinan, Admin, atau Kasubag yang bisa menilai
        if (!in_array($user->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Anda tidak memiliki hak akses Penilaian.');
        }

        // Tampilkan 20 agenda terbaru yang sudah ada realisasinya
        $pendingAgendas = DailyAgenda::with(['user', 'items'])
            ->whereNotNull('realization_submitted_at')
            ->orderBy('realization_submitted_at', 'desc')
            ->paginate(15);

        return view('agenda.leader_index', compact('pendingAgendas'));
    }

    /**
     * Pimpinan Memberikan Penilaian & Masukan
     */
    public function evaluate(Request $request, $id)
    {
        $leader = auth()->user();
        if (!in_array($leader->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            return back()->with('error', 'Akses Ditolak.');
        }

        $request->validate([
            'rating' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string'
        ]);

        $agenda = DailyAgenda::findOrFail($id);
        $agenda->update([
            'leader_rating' => $request->rating,
            'leader_feedback' => $request->feedback,
            'evaluated_at' => now('Asia/Makassar'),
            'evaluated_by' => $leader->id,
            'status' => 'Verified'
        ]);

        // Opsional: Update skor performa user secara kumulatif
        $employee = $agenda->user;
        $avgScore = DailyAgenda::where('user_id', $employee->id)
            ->whereNotNull('leader_rating')
            ->avg('leader_rating');
            
        if ($avgScore) {
            $employee->update([
                'performance_score' => $avgScore,
                'performance_color' => $avgScore >= 80 ? 'Hijau' : ($avgScore >= 60 ? 'Kuning' : 'Merah')
            ]);
        }

        return back()->with('success', 'Penilaian SIPEGA berhasil disimpan. Skor pegawai telah diperbarui otomatis.');
    }

    /**
     * Penilaian Massal oleh Pimpinan
     */
    public function bulkEvaluate(Request $request)
    {
        $leader = auth()->user();
        if (!in_array($leader->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            return back()->with('error', 'Akses Ditolak.');
        }

        $request->validate([
            'agenda_ids' => 'required|array',
            'agenda_ids.*' => 'exists:daily_agendas,id',
            'bulk_rating' => 'required|integer|min:0|max:100'
        ]);

        $evaluatedCount = 0;
        foreach ($request->agenda_ids as $id) {
            $agenda = DailyAgenda::findOrFail($id);
            
            // Hanya update jika belum dinilai
            if (!$agenda->leader_rating) {
                $agenda->update([
                    'leader_rating' => $request->bulk_rating,
                    'leader_feedback' => 'Penilaian Massal (Verified by ' . $leader->role . ')',
                    'evaluated_at' => now('Asia/Makassar'),
                    'evaluated_by' => $leader->id,
                    'status' => 'Verified'
                ]);

                // Update performa user
                $employee = $agenda->user;
                $avgScore = DailyAgenda::where('user_id', $employee->id)
                    ->whereNotNull('leader_rating')
                    ->avg('leader_rating');
                    
                if ($avgScore) {
                    $employee->update([
                        'performance_score' => $avgScore,
                        'performance_color' => $avgScore >= 80 ? 'Hijau' : ($avgScore >= 60 ? 'Kuning' : 'Merah')
                    ]);
                }
                $evaluatedCount++;
            }
        }

        return back()->with('success', $evaluatedCount . ' laporan berhasil dinilai secara massal.');
    }
}
