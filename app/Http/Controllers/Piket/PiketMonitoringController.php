<?php

namespace App\Http\Controllers\Piket;

use App\Http\Controllers\Controller;
use App\Models\PiketAttendance;
use App\Models\PiketScheduleAssignment;
use App\Models\PiketUnit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PiketMonitoringController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $date = $request->input('date', Carbon::today('Asia/Makassar')->toDateString());
        $unitId = $request->input('unit_id');

        $units = PiketUnit::where('is_active', true)->get();

        $query = PiketScheduleAssignment::with(['unit', 'activeOfficer', 'primaryOfficer', 'backupOfficer', 'attendance'])
            ->whereDate('date', $date);

        if ($unitId) {
            $query->where('piket_unit_id', $unitId);
        }

        $assignments = $query->orderBy('piket_unit_id')->orderBy('role_label')->get();

        // Rekap Bulanan Ringkas
        $month = Carbon::parse($date)->month;
        $year = Carbon::parse($date)->year;

        $monthlyAttendances = PiketAttendance::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->when($unitId, fn($q) => $q->whereHas('assignment', fn($sq) => $sq->where('piket_unit_id', $unitId)))
            ->get();

        $monthlyHadirCount = $monthlyAttendances->where('attendance_status', 'hadir')->count();
        $monthlyVisitorsCount = $monthlyAttendances->sum('visitors_served_count');

        return view('piket.monitoring.index', compact(
            'units',
            'date',
            'unitId',
            'assignments',
            'monthlyHadirCount',
            'monthlyVisitorsCount'
        ));
    }

    public function recordAttendance(Request $request, $assignmentId)
    {
        $request->validate([
            'attendance_status' => 'required|in:hadir,tidak_hadir,digantikan,izin',
            'check_in' => 'nullable',
            'check_out' => 'nullable',
            'visitors_served_count' => 'nullable|integer|min:0',
            'service_notes' => 'nullable|string',
            'issues_notes' => 'nullable|string',
        ]);

        $assignment = PiketScheduleAssignment::findOrFail($assignmentId);

        PiketAttendance::updateOrCreate(
            ['piket_schedule_assignment_id' => $assignment->id],
            [
                'user_id' => $assignment->active_officer_id,
                'date' => $assignment->date->toDateString(),
                'attendance_status' => $request->attendance_status,
                'check_in' => $request->check_in ?: now('Asia/Makassar')->format('H:i'),
                'check_out' => $request->check_out,
                'visitors_served_count' => $request->visitors_served_count ?: 0,
                'service_notes' => $request->service_notes,
                'issues_notes' => $request->issues_notes,
            ]
        );

        $assignment->update([
            'status' => $request->attendance_status === 'hadir' ? 'completed' : ($request->attendance_status === 'tidak_hadir' ? 'absent' : $assignment->status),
        ]);

        return back()->with('success', 'Presensi dan catatan pelayanan piket berhasil disimpan.');
    }
}
