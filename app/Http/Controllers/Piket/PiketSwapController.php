<?php

namespace App\Http\Controllers\Piket;

use App\Http\Controllers\Controller;
use App\Models\PiketScheduleAssignment;
use App\Models\PiketSwapRequest;
use App\Services\PiketConflictDetectionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PiketSwapController extends Controller
{
    protected PiketConflictDetectionService $conflictService;

    public function __construct(PiketConflictDetectionService $conflictService)
    {
        $this->conflictService = $conflictService;
    }

    /**
     * Submenu 8: Pengajuan & Persetujuan Tukar Jadwal
     */
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $user = auth()->user();
        $isAdmin = in_array($user->role, ['Admin', 'Pimpinan', 'Kasubag']);

        $query = PiketSwapRequest::with([
            'requester',
            'targetUser',
            'requesterAssignment.unit',
            'targetAssignment.unit',
            'approver',
        ]);

        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhere('target_user_id', $user->id);
            });
        }

        $swaps = $query->latest('id')->paginate(15)->withQueryString();

        // Cari jadwal milik user yang sedang login untuk modal ajukan tukar
        $myUpcomingAssignments = PiketScheduleAssignment::where('active_officer_id', $user->id)
            ->whereDate('date', '>=', now()->toDateString())
            ->with('unit')
            ->orderBy('date')
            ->get();

        // Cari jadwal pegawai lain di masa depan
        $otherAssignments = PiketScheduleAssignment::where('active_officer_id', '!=', $user->id)
            ->whereDate('date', '>=', now()->toDateString())
            ->with(['unit', 'activeOfficer'])
            ->orderBy('date')
            ->take(50)
            ->get();

        return view('piket.swaps.index', compact('swaps', 'myUpcomingAssignments', 'otherAssignments', 'isAdmin'));
    }

    /**
     * Ajukan Tukar Jadwal
     */
    public function store(Request $request)
    {
        $request->validate([
            'requester_assignment_id' => 'required|exists:piket_schedule_assignments,id',
            'target_assignment_id' => 'required|exists:piket_schedule_assignments,id|different:requester_assignment_id',
            'reason' => 'required|string|max:500',
        ]);

        $myAssign = PiketScheduleAssignment::findOrFail($request->requester_assignment_id);
        $targetAssign = PiketScheduleAssignment::findOrFail($request->target_assignment_id);

        if ($myAssign->active_officer_id !== auth()->id() && !in_array(auth()->user()->role, ['Admin', 'Kasubag'])) {
            return back()->with('error', 'Anda hanya dapat mengajukan tukar untuk jadwal dinas Anda sendiri.');
        }

        // Cek benturan awal
        $check1 = $this->conflictService->checkEmployeeConflict($myAssign->active_officer_id, $targetAssign->date->toDateString());
        if ($check1['has_conflict']) {
            return back()->with('error', "Anda memiliki benturan kegiatan pada tanggal target ({$targetAssign->date->translatedFormat('d M Y')}): {$check1['description']}. Pertukaran tidak dapat diajukan.");
        }

        $check2 = $this->conflictService->checkEmployeeConflict($targetAssign->active_officer_id, $myAssign->date->toDateString());
        if ($check2['has_conflict']) {
            return back()->with('error', "Pegawai target memiliki benturan kegiatan pada tanggal Anda ({$myAssign->date->translatedFormat('d M Y')}): {$check2['description']}.");
        }

        PiketSwapRequest::create([
            'requester_id' => $myAssign->active_officer_id,
            'requester_assignment_id' => $myAssign->id,
            'target_user_id' => $targetAssign->active_officer_id,
            'target_assignment_id' => $targetAssign->id,
            'requester_date' => $myAssign->date->toDateString(),
            'target_date' => $targetAssign->date->toDateString(),
            'reason' => $request->reason,
            'target_user_agreement' => 'agreed', // jika dibuat oleh requester
            'admin_approval_status' => 'pending',
        ]);

        return back()->with('success', 'Pengajuan tukar jadwal berhasil dikirim! Menunggu verifikasi dan persetujuan Admin.');
    }

    /**
     * Persetujuan / Penolakan Tukar Jadwal oleh Admin
     */
    public function processApproval(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $swap = PiketSwapRequest::with(['requesterAssignment', 'targetAssignment'])->findOrFail($id);

        if ($request->decision === 'approved') {
            // Tukar active_officer_id pada kedua assignment
            $reqAssign = $swap->requesterAssignment;
            $tarAssign = $swap->targetAssignment;

            $tempUser = $reqAssign->active_officer_id;
            $reqAssign->update([
                'active_officer_id' => $tarAssign->active_officer_id,
                'status' => 'swapped',
                'notes' => "Tukar jadwal dengan {$swap->targetUser->name} pada tanggal " . $tarAssign->date->translatedFormat('d M Y'),
            ]);

            $tarAssign->update([
                'active_officer_id' => $tempUser,
                'status' => 'swapped',
                'notes' => "Tukar jadwal dengan {$swap->requester->name} pada tanggal " . $reqAssign->date->translatedFormat('d M Y'),
            ]);

            $swap->update([
                'admin_approval_status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now('Asia/Makassar'),
                'admin_notes' => $request->admin_notes,
            ]);

            return back()->with('success', '✓ Pertukaran jadwal disetujui! Petugas pada kedua tanggal jadwal telah ditukar secara otomatis.');
        } else {
            $swap->update([
                'admin_approval_status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now('Asia/Makassar'),
                'admin_notes' => $request->admin_notes ?: 'Pengajuan pertukaran ditolak oleh Admin.',
            ]);

            return back()->with('success', 'Pengajuan tukar jadwal telah ditolak.');
        }
    }
}
