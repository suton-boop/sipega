<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AttendanceImport;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    /**
     * SIPEGA: Rekap Kehadiran Perorangan (Bulan Berjalan)
     */
    public function recap(Request $request, $userId = null)
    {
        $user = $userId ? User::findOrFail($userId) : auth()->user();
        
        // Security: Pegawai hanya boleh lihat data sendiri, Admin boleh lihat semua
        if (auth()->id() !== $user->id && !in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan'])) {
            abort(403);
        }

        $monthYear = $request->get('month', Carbon::now('Asia/Makassar')->format('Y-m'));
        $startOfMonth = Carbon::parse($monthYear . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Get logs for the selected month
        $logs = AttendanceLog::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('date', 'asc')
            ->get();

        // Check assigned letters/ST to mark "Tugas Luar"
        $activeAssignmentDays = \DB::table('assignment_letter_user')
            ->join('assignment_letters', 'assignment_letter_user.assignment_letter_id', '=', 'assignment_letters.id')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->pluck('date')
            ->toArray();

        // Get Holiday/Shared Leave days
        $holidays = CalendarEvent::whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->whereIn('type', ['Holiday', 'Shared Leave'])
            ->pluck('date')
            ->toArray();

        // Build a complete period view (like Excel)
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
        $recapData = [];
        $summary = [
            'total_present' => 0,
            'total_tl_minutes' => 0,
            'total_psw_minutes' => 0,
            'total_alpa' => 0,
            'total_st' => 0
        ];

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $log = $logs->where('date', $dateStr)->first();
            
            $status = 'Hadir';
            $isWorkingDay = !$date->isWeekend() && !in_array($dateStr, $holidays);

            if (in_array($dateStr, $activeAssignmentDays)) {
                $status = 'Tugas Luar (ST)';
                $summary['total_st']++;
            } elseif (!$isWorkingDay) {
                $status = 'Libur/Off';
            } elseif (!$log) {
                $status = 'ALPA';
                $summary['total_alpa']++;
            } else {
                $summary['total_present']++;
                $summary['total_tl_minutes'] += $log->tl_minutes;
                $summary['total_psw_minutes'] += $log->psw_minutes;
            }

            $recapData[] = [
                'date' => $dateStr,
                'check_in' => $log ? $log->check_in : null,
                'check_out' => $log ? $log->check_out : null,
                'tl_minutes' => $log ? $log->tl_minutes : 0,
                'psw_minutes' => $log ? $log->psw_minutes : 0,
                'status' => $status
            ];
        }

        return view('attendance.recap', compact('user', 'recapData', 'summary', 'monthYear'));
    }

    /**
     * SIPEGA: Import Excel Absensi Mingguan Mesin (Admin)
     */
    public function importExcel(Request $request)
    {
        ini_set('max_execution_time', 300);
        $request->validate(['excel_file' => 'required|mimes:xlsx,xls,csv|max:10240']);
        try {
            Excel::import(new AttendanceImport, $request->file('excel_file'));
            return back()->with('success_import', 'Mesin Excel berhasil disinkronisasi ke SIPEGA!');
        } catch (\Exception $e) {
            return back()->withErrors(['excel_file' => 'Import Error: ' . $e->getMessage()]);
        }
    }
}
