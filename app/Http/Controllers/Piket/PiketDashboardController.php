<?php

namespace App\Http\Controllers\Piket;

use App\Http\Controllers\Controller;
use App\Models\PiketConflict;
use App\Models\PiketEligibleEmployee;
use App\Models\PiketReplacement;
use App\Models\PiketSchedule;
use App\Models\PiketScheduleAssignment;
use App\Models\PiketSwapRequest;
use App\Models\PiketUnit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PiketDashboardController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $year = (int) $request->input('year', date('Y'));
        $unitId = $request->input('unit_id');

        $units = PiketUnit::where('is_active', true)->get();
        $ult = $units->firstWhere('code', 'ULT');
        $perpus = $units->firstWhere('code', 'PERPUS');

        // Statistik Petugas
        $ultOfficersCount = PiketEligibleEmployee::where('piket_unit_id', $ult?->id)->count();
        $perpusOfficersCount = PiketEligibleEmployee::where('piket_unit_id', $perpus?->id)->count();

        // Base Query Assignments by Year
        $assignmentQuery = PiketScheduleAssignment::whereYear('date', $year);
        if ($unitId) {
            $assignmentQuery->where('piket_unit_id', $unitId);
        }

        $totalAssignments = (clone $assignmentQuery)->count();
        $publishedCount = PiketSchedule::where('year', $year)
            ->when($unitId, fn($q) => $q->where('piket_unit_id', $unitId))
            ->whereIn('status', ['published', 'revised'])
            ->count();

        $activeConflictsCount = (clone $assignmentQuery)->where('has_conflict', true)->where('is_replaced', false)->count();
        $replacedCount = PiketReplacement::whereHas('assignment', function ($q) use ($year, $unitId) {
            $q->whereYear('date', $year)->when($unitId, fn($sq) => $sq->where('piket_unit_id', $unitId));
        })->where('approval_status', 'approved')->count();

        $swapRequestsCount = PiketSwapRequest::whereYear('requester_date', $year)->count();

        // Status Pelayanan Hari Ini
        $today = Carbon::today('Asia/Makassar')->toDateString();
        $todayAssignments = PiketScheduleAssignment::with(['unit', 'primaryOfficer', 'backupOfficer', 'activeOfficer'])
            ->whereDate('date', $today)
            ->get();

        // Aktivitas Terbaru (Pergantian, Konflik, Tukar)
        $recentConflicts = PiketConflict::with(['assignment.unit', 'user'])
            ->latest('id')
            ->take(5)
            ->get();

        $recentReplacements = PiketReplacement::with(['assignment.unit', 'originalOfficer', 'replacementOfficer'])
            ->latest('id')
            ->take(5)
            ->get();

        return view('piket.dashboard', compact(
            'units',
            'year',
            'unitId',
            'ultOfficersCount',
            'perpusOfficersCount',
            'totalAssignments',
            'publishedCount',
            'activeConflictsCount',
            'replacedCount',
            'swapRequestsCount',
            'todayAssignments',
            'recentConflicts',
            'recentReplacements'
        ));
    }
}
