<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AttendanceImport;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\CalendarEvent;
use App\Services\AttendanceLogParserService;
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

        $monthYear = $request->get('month');
        if (!$monthYear) {
            $latestDate = AttendanceLog::where('user_id', $user->id)->max('date');
            $monthYear = $latestDate ? Carbon::parse($latestDate)->format('Y-m') : Carbon::now('Asia/Makassar')->format('Y-m');
        }
        $startOfMonth = Carbon::parse($monthYear . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Get logs for the selected month
        $logs = AttendanceLog::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('date', 'asc')
            ->get();

        // Check assigned letters/ST to mark "Tugas Luar" (Sinkronisasi Penugasan Resmi)
        $activeAssignmentDays = \DB::table('assignment_letter_user')
            ->join('assignment_letters', 'assignment_letter_user.assignment_letter_id', '=', 'assignment_letters.id')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->pluck('date')
            ->toArray();

        // Tambahkan tanggal dari Surat Tugas Formal (Letter) yang berstatus Approved
        $approvedLetters = $user->letters()
            ->where('status', 'Approved')
            ->where('date_start', '<=', $endOfMonth->toDateString())
            ->whereRaw('COALESCE(date_end, date_start) >= ?', [$startOfMonth->toDateString()])
            ->get();

        foreach ($approvedLetters as $al) {
            $cur = Carbon::parse($al->date_start);
            $end = Carbon::parse($al->date_end ?? $al->date_start);
            while ($cur->lte($end)) {
                $activeAssignmentDays[] = $cur->toDateString();
                $cur->addDay();
            }
        }
        $activeAssignmentDays = array_values(array_unique($activeAssignmentDays));

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

            if (in_array($dateStr, $activeAssignmentDays) || ($log && in_array($log->status, ['DL', 'Tugas Luar (ST)']))) {
                $status = 'Tugas Luar (ST)';
                $summary['total_st']++;
            } elseif (!$isWorkingDay) {
                $status = 'Libur/Off';
            } elseif (!$log) {
                $status = 'ALPA';
                $summary['total_alpa']++;
            } else {
                $status = $log->status ?: 'Hadir';
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
     * SIPEGA: Halaman Form Menu Import Log Daftar Hadir
     */
    public function importView(Request $request)
    {
        // Hanya Admin, Kasubag, Pimpinan, dan Operator yang dapat mengakses
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator'])) {
            abort(403, 'Akses tidak diizinkan. Hanya Admin dan Pimpinan yang dapat mengimpor log daftar hadir.');
        }

        // Ambil summary log terbaru untuk statistik tampilan
        $recentLogsCount = AttendanceLog::where('source', 'Mesin')->count();
        $latestLogDate = AttendanceLog::where('source', 'Mesin')->max('date');
        $totalRegisteredEmployees = User::whereNotNull('nip')->where('is_active', true)->count();

        return view('attendance.import_log', compact('recentLogsCount', 'latestLogDate', 'totalRegisteredEmployees'));
    }

    /**
     * SIPEGA: Proses Eksekusi Import Log Daftar Hadir (File atau Tempel HTML/Teks)
     */
    public function processImport(Request $request, AttendanceLogParserService $parser)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator'])) {
            abort(403);
        }

        $request->validate([
            'log_file' => 'nullable|file|mimes:html,htm,xls,xlsx,csv,txt|max:20480',
            'raw_content' => 'nullable|string',
            'office_start_time' => 'nullable|date_format:H:i',
            'office_end_weekday' => 'nullable|date_format:H:i',
            'office_end_friday' => 'nullable|date_format:H:i',
        ], [
            'log_file.mimes' => 'Format file harus berupa .html, .htm, .xls, .xlsx, .csv, atau .txt',
            'log_file.max' => 'Ukuran file maksimal adalah 20MB',
        ]);

        $file = $request->file('log_file') ?? $request->file('excel_file');
        $rawContent = $request->input('raw_content');

        if (!$file && empty(trim((string)$rawContent))) {
            return back()->withErrors(['log_file' => 'Silakan pilih file untuk diunggah ATAU tempelkan teks/HTML data log transaksi.']);
        }

        $options = [
            'office_start_time' => ($request->input('office_start_time') ?: '07:30') . ':00',
            'office_end_weekday' => ($request->input('office_end_weekday') ?: '16:00') . ':00',
            'office_end_friday' => ($request->input('office_end_friday') ?: '16:30') . ':00',
            'auto_create_user' => $request->has('auto_create_user') ? $request->boolean('auto_create_user') : true,
        ];

        try {
            $source = $file ?: $rawContent;
            $result = $parser->parseAndImport($source, $options);

            if (!$result['success']) {
                return back()->withErrors(['log_file' => $result['message']])->withInput();
            }

            return back()->with('import_result', $result)->with('success_import', $result['message']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import Log Transaksi Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withErrors(['log_file' => 'Terjadi kesalahan saat memproses data: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * SIPEGA: Import Excel Absensi Mingguan/Bulanan dari Mesin (Log Transaksi)
     */
    public function importExcel(Request $request, AttendanceLogParserService $parser)
    {
        ini_set('max_execution_time', 600);
        $request->validate(['excel_file' => 'required|file|max:20480']);
        
        try {
            $result = $parser->parseAndImport($request->file('excel_file'), ['auto_create_user' => true]);

            if (!$result['success']) {
                return back()->withErrors(['excel_file' => $result['message']]);
            }

            return back()->with('import_result', $result)->with('success_import', $result['message']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import Error: ' . $e->getMessage());
            return back()->withErrors(['excel_file' => 'Gagal Import: ' . $e->getMessage()]);
        }
    }
}
