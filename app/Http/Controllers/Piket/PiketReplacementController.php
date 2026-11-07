<?php

namespace App\Http\Controllers\Piket;

use App\Http\Controllers\Controller;
use App\Models\PiketConflict;
use App\Models\PiketReplacement;
use App\Models\PiketScheduleAssignment;
use App\Models\User;
use App\Services\PiketConflictDetectionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PiketReplacementController extends Controller
{
    protected PiketConflictDetectionService $conflictService;

    public function __construct(PiketConflictDetectionService $conflictService)
    {
        $this->conflictService = $conflictService;
    }

    /**
     * Submenu 7: Riwayat & Pengelolaan Penggantian Petugas
     */
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $priority = $request->input('priority_level');
        $search = $request->input('search');

        $query = PiketReplacement::with([
            'assignment.unit',
            'assignment.schedule',
            'originalOfficer',
            'replacementOfficer',
            'approver',
        ]);

        if ($priority) {
            $query->where('priority_level', $priority);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('originalOfficer', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('replacementOfficer', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $replacements = $query->latest('id')->paginate(15)->withQueryString();

        return view('piket.replacements.index', compact('replacements', 'priority', 'search'));
    }

    /**
     * Halaman / Form Pilih Pengganti dengan 3 Tingkat Prioritas
     */
    public function create($assignmentId)
    {
        Carbon::setLocale('id');
        $assignment = PiketScheduleAssignment::with(['unit', 'primaryOfficer', 'backupOfficer', 'activeOfficer', 'conflicts'])
            ->findOrFail($assignmentId);

        $recommendations = $this->conflictService->getReplacementRecommendations($assignment);

        return view('piket.replacements.create', compact('assignment', 'recommendations'));
    }

    /**
     * Simpan & Eksekusi Pergantian Petugas Piket
     */
    public function store(Request $request, $assignmentId)
    {
        $request->validate([
            'replacement_officer_id' => 'required|exists:users,id',
            'priority_level' => 'required|in:prioritas_1_cadangan,prioritas_2_tersedia,prioritas_3_manual',
            'reason' => 'required|string|max:500',
        ]);

        $assignment = PiketScheduleAssignment::findOrFail($assignmentId);
        $user = auth()->user();

        // Cek kembali benturan calon pengganti pada tanggal tersebut
        $check = $this->conflictService->checkEmployeeConflict(
            (int) $request->replacement_officer_id,
            $assignment->date->toDateString()
        );

        if ($check['has_conflict']) {
            return back()->with('error', "Pegawai pengganti yang dipilih memiliki benturan penugasan: {$check['description']}. Silakan pilih pegawai lain yang tersedia.")->withInput();
        }

        $originalOfficerId = $assignment->active_officer_id;
        $replacementOfficer = User::findOrFail($request->replacement_officer_id);

        $replacement = PiketReplacement::create([
            'piket_schedule_assignment_id' => $assignment->id,
            'original_officer_id' => $originalOfficerId,
            'replacement_officer_id' => $replacementOfficer->id,
            'priority_level' => $request->priority_level,
            'reason' => $request->reason,
            'approval_status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now('Asia/Makassar'),
            'approver_notes' => 'Disetujui langsung oleh ' . $user->name . ' (' . $user->role . ')',
        ]);

        // Perbarui assignment aktif
        $assignment->update([
            'active_officer_id' => $replacementOfficer->id,
            'is_replaced' => true,
            'status' => 'replaced',
            'replacement_id' => $replacement->id,
            'notes' => "Digantikan oleh {$replacementOfficer->name} ({$request->priority_level}) karena: {$request->reason}",
        ]);

        // Selesaikan konflik terkait jika ada
        PiketConflict::where('piket_schedule_assignment_id', $assignment->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved_' . str_replace('prioritas_', '', $request->priority_level),
                'resolution_notes' => "Telah digantikan oleh {$replacementOfficer->name} melalui mekanisme {$request->priority_level}.",
                'resolved_at' => now('Asia/Makassar'),
                'resolved_by' => $user->id,
            ]);

        return redirect()->route('piket.schedules.show', $assignment->piket_schedule_id)
            ->with('success', "✓ Petugas pengganti berhasil ditetapkan! {$replacementOfficer->name} sekarang resmi bertugas menggantikan {$assignment->primaryOfficer->name} pada tanggal " . $assignment->date->translatedFormat('d M Y') . ".");
    }
}
