<?php

namespace App\Http\Controllers\Piket;

use App\Http\Controllers\Controller;
use App\Models\PiketAttendance;
use App\Models\PiketConflict;
use App\Models\PiketReplacement;
use App\Models\PiketScheduleAssignment;
use App\Models\PiketUnit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PiketReportController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $reportType = $request->input('report_type', 'per_pegawai'); // 'per_pegawai', 'per_unit', 'penggantian', 'konflik', 'kehadiran'
        $year = (int) $request->input('year', date('Y'));
        $month = $request->input('month');
        $unitId = $request->input('unit_id');
        $userId = $request->input('user_id');

        $units = PiketUnit::where('is_active', true)->get();
        $employees = User::realPegawai()->where('is_active', true)->orderBy('name')->get();

        $data = [];

        if ($reportType === 'per_pegawai') {
            // Rekap giliran & kehadiran per pegawai
            $data = User::realPegawai()
                ->where('is_active', true)
                ->when($userId, fn($q) => $q->where('id', $userId))
                ->get()
                ->map(function ($emp) use ($year, $month, $unitId) {
                    $assignQuery = PiketScheduleAssignment::where('active_officer_id', $emp->id)
                        ->whereYear('date', $year);

                    if ($month) $assignQuery->whereMonth('date', $month);
                    if ($unitId) $assignQuery->where('piket_unit_id', $unitId);

                    $totalShifts = $assignQuery->count();
                    $ultShifts = (clone $assignQuery)->whereHas('unit', fn($u) => $u->where('code', 'ULT'))->count();
                    $perpusShifts = (clone $assignQuery)->whereHas('unit', fn($u) => $u->where('code', 'PERPUS'))->count();
                    $replacedCount = (clone $assignQuery)->where('is_replaced', true)->count();

                    return [
                        'user' => $emp,
                        'total_shifts' => $totalShifts,
                        'ult_shifts' => $ultShifts,
                        'perpus_shifts' => $perpusShifts,
                        'replaced_count' => $replacedCount,
                    ];
                });
        } elseif ($reportType === 'per_unit') {
            // Rekap pelaksanaan per unit pelayanan
            $data = $units->map(function ($unit) use ($year, $month) {
                $q = PiketScheduleAssignment::where('piket_unit_id', $unit->id)->whereYear('date', $year);
                if ($month) $q->whereMonth('date', $month);

                $totalDays = (clone $q)->distinct('date')->count();
                $totalShifts = (clone $q)->count();
                $conflictCount = (clone $q)->where('has_conflict', true)->count();
                $replacedCount = (clone $q)->where('is_replaced', true)->count();

                $attendances = PiketAttendance::whereHas('assignment', fn($sq) => $sq->where('piket_unit_id', $unit->id)->whereYear('date', $year))
                    ->when($month, fn($sq) => $sq->whereMonth('date', $month))
                    ->get();

                return [
                    'unit' => $unit,
                    'total_days' => $totalDays,
                    'total_shifts' => $totalShifts,
                    'conflict_count' => $conflictCount,
                    'replaced_count' => $replacedCount,
                    'visitors_served' => $attendances->sum('visitors_served_count'),
                ];
            });
        } elseif ($reportType === 'penggantian') {
            $data = PiketReplacement::with(['assignment.unit', 'originalOfficer', 'replacementOfficer', 'approver'])
                ->whereHas('assignment', function ($q) use ($year, $month, $unitId) {
                    $q->whereYear('date', $year);
                    if ($month) $q->whereMonth('date', $month);
                    if ($unitId) $q->where('piket_unit_id', $unitId);
                })
                ->latest('id')
                ->get();
        } elseif ($reportType === 'konflik') {
            $data = PiketConflict::with(['assignment.unit', 'user', 'resolver'])
                ->whereYear('conflict_date', $year)
                ->when($month, fn($q) => $q->whereMonth('conflict_date', $month))
                ->when($unitId, fn($q) => $q->whereHas('assignment', fn($sq) => $sq->where('piket_unit_id', $unitId)))
                ->latest('conflict_date')
                ->get();
        }

        return view('piket.reports.index', compact(
            'units',
            'employees',
            'reportType',
            'year',
            'month',
            'unitId',
            'userId',
            'data'
        ));
    }

    public function downloadPdf(Request $request)
    {
        Carbon::setLocale('id');
        $reportType = $request->input('report_type', 'per_pegawai');
        $year = (int) $request->input('year', date('Y'));
        $month = $request->input('month');
        $unitId = $request->input('unit_id');

        $unit = $unitId ? PiketUnit::find($unitId) : null;
        $title = "Laporan Rekapitulasi Piket Tahun {$year}";

        // Simple PDF report render
        $pdf = Pdf::loadView('piket.pdf.rekap_laporan', compact('reportType', 'year', 'month', 'unit', 'title'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("Rekap_Piket_{$reportType}_{$year}.pdf");
    }
}
