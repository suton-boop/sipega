<?php

namespace App\Http\Controllers\Piket;

use App\Http\Controllers\Controller;
use App\Models\AssignmentLetter;
use App\Models\LeaveRequest;
use App\Models\Letter;
use App\Models\PiketConflict;
use App\Models\PiketScheduleAssignment;
use App\Models\PiketUnit;
use App\Services\PiketConflictDetectionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PiketConflictController extends Controller
{
    protected PiketConflictDetectionService $conflictService;

    public function __construct(PiketConflictDetectionService $conflictService)
    {
        $this->conflictService = $conflictService;
    }

    /**
     * Submenu 6: Deteksi Konflik Jadwal
     */
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $status = $request->input('status', 'pending');
        $unitId = $request->input('unit_id');

        $units = PiketUnit::where('is_active', true)->get();

        $query = PiketConflict::with([
            'assignment.unit',
            'assignment.primaryOfficer',
            'assignment.backupOfficer',
            'assignment.activeOfficer',
            'user',
            'resolver',
        ]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($unitId) {
            $query->whereHas('assignment', fn($q) => $q->where('piket_unit_id', $unitId));
        }

        $conflicts = $query->latest('conflict_date')->paginate(15)->withQueryString();

        $pendingCount = PiketConflict::where('status', 'pending')->count();
        $resolvedCount = PiketConflict::where('status', '!=', 'pending')->count();

        return view('piket.conflicts.index', compact('conflicts', 'units', 'status', 'unitId', 'pendingCount', 'resolvedCount'));
    }

    /**
     * Submenu 5: Penugasan Kedinasan (Monitoring Kegiatan Dinas Seluruh Pegawai)
     */
    public function dinas(Request $request)
    {
        Carbon::setLocale('id');
        $startDate = $request->input('start_date', Carbon::today('Asia/Makassar')->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today('Asia/Makassar')->endOfMonth()->toDateString());

        // Ambil Surat Tugas (assignment_letters)
        $assignmentLetters = AssignmentLetter::with('users')
            ->whereDate('date', '<=', $endDate)
            ->where(function ($q) use ($startDate) {
                $q->whereDate('end_date', '>=', $startDate)
                  ->orWhereNull('end_date');
            })
            ->latest('date')
            ->paginate(15, ['*'], 'st_page');

        // Ambil Cuti Pegawai (leave_requests)
        $leaveRequests = LeaveRequest::whereNotIn('status', ['Ditolak', 'Dibatalkan', 'rejected', 'cancelled'])
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->latest('start_date')
            ->paginate(15, ['*'], 'cuti_page');

        return view('piket.conflicts.dinas', compact('assignmentLetters', 'leaveRequests', 'startDate', 'endDate'));
    }
}
