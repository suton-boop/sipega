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

        // Key by date string Y-m-d to ensure 100% accurate matching with $dateStr (avoid Carbon object vs string mismatch)
        $logsByDate = $logs->keyBy(function ($item) {
            return Carbon::parse($item->date)->toDateString();
        });

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
            $log = $logsByDate->get($dateStr);
            
            $status = 'Hadir';
            $isWorkingDay = !$date->isWeekend() && !in_array($dateStr, $holidays);

            if (in_array($dateStr, $activeAssignmentDays) || ($log && in_array($log->status, ['DL', 'Tugas Luar (ST)']))) {
                $status = 'Tugas Luar (ST)';
                $summary['total_st']++;
            } elseif (!$isWorkingDay) {
                $status = 'Libur/Off';
            } elseif (!$log || (empty($log->check_in) && empty($log->check_out))) {
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

        $allPegawai = in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator'])
            ? User::where('is_active', true)->orderBy('name')->get()
            : collect([$user]);

        return view('attendance.recap', compact('user', 'recapData', 'summary', 'monthYear', 'allPegawai'));
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

    /**
     * SIPEGA: Menu Admin Kelola Kehadiran & Pemberian/Edit Keterangan Pegawai
     */
    public function manageIndex(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator'])) {
            abort(403, 'Akses ditolak. Menu ini khusus Admin dan Manajemen.');
        }

        // Ambil list semua pegawai aktif untuk dropdown filter
        $allPegawai = User::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'nip', 'position', 'unit_kerja']);

        // Filter bulan & pegawai
        $monthYear = $request->get('month');
        if (!$monthYear) {
            $latestLogDate = AttendanceLog::max('date');
            $monthYear = $latestLogDate ? Carbon::parse($latestLogDate)->format('Y-m') : Carbon::now('Asia/Makassar')->format('Y-m');
        }

        $userId = $request->get('user_id');
        $selectedUser = $userId ? User::find($userId) : null;

        $dailyData = [];
        $stats = [
            'hadir' => 0,
            'dl' => 0,
            'cuti' => 0,
            'sakit' => 0,
            'izin' => 0,
            'alpa' => 0,
            'libur' => 0,
            'total_tl' => 0,
            'total_psw' => 0,
        ];

        if ($selectedUser) {
            $startOfMonth = Carbon::parse($monthYear . '-01')->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            // Logs pegawai terpilih pada bulan yang ditentukan
            $logs = AttendanceLog::where('user_id', $selectedUser->id)
                ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->orderBy('date', 'asc')
                ->get();

            $logsByDate = $logs->keyBy(function ($item) {
                return Carbon::parse($item->date)->toDateString();
            });

            // Sinkronisasi hari penugasan dinas luar dari ST
            $activeAssignmentDays = \DB::table('assignment_letter_user')
                ->join('assignment_letters', 'assignment_letter_user.assignment_letter_id', '=', 'assignment_letters.id')
                ->where('user_id', $selectedUser->id)
                ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->pluck('date')
                ->toArray();

            $approvedLetters = $selectedUser->letters()
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

            // Hari libur nasional / cuti bersama
            $holidays = CalendarEvent::whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->whereIn('type', ['Holiday', 'Shared Leave'])
                ->pluck('date')
                ->toArray();

            $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

            foreach ($period as $date) {
                $dateStr = $date->toDateString();
                $log = $logsByDate->get($dateStr);
                $isWeekend = $date->isWeekend();
                $isHoliday = in_array($dateStr, $holidays);
                $isWorkingDay = !$isWeekend && !$isHoliday;
                $hasST = in_array($dateStr, $activeAssignmentDays);

                $status = 'Hadir';
                if ($log && !empty($log->status)) {
                    $status = $log->status;
                } elseif ($hasST) {
                    $status = 'DL';
                } elseif (!$isWorkingDay) {
                    $status = 'Libur';
                } elseif (!$log || (empty($log->check_in) && empty($log->check_out))) {
                    $status = ($isWorkingDay && $date->lte(Carbon::today('Asia/Makassar'))) ? 'ALPA' : ($isWorkingDay ? 'Belum Ada Log' : 'Libur');
                }

                // Hitung statistik
                if ($status === 'Hadir') {
                    $stats['hadir']++;
                } elseif (in_array($status, ['DL', 'Tugas Luar (ST)'])) {
                    $stats['dl']++;
                } elseif ($status === 'Cuti') {
                    $stats['cuti']++;
                } elseif ($status === 'Sakit') {
                    $stats['sakit']++;
                } elseif ($status === 'Izin') {
                    $stats['izin']++;
                } elseif ($status === 'ALPA') {
                    $stats['alpa']++;
                } elseif ($status === 'Libur') {
                    $stats['libur']++;
                }

                if ($log) {
                    $stats['total_tl'] += (int)$log->tl_minutes;
                    $stats['total_psw'] += (int)$log->psw_minutes;
                }

                $dailyData[] = [
                    'date' => $dateStr,
                    'day_name' => $date->translatedFormat('l'),
                    'formatted_date' => $date->translatedFormat('d M Y'),
                    'is_working_day' => $isWorkingDay,
                    'is_weekend' => $isWeekend,
                    'is_holiday' => $isHoliday,
                    'has_st' => $hasST,
                    'log_id' => $log ? $log->id : null,
                    'check_in' => $log ? $log->check_in : null,
                    'check_out' => $log ? $log->check_out : null,
                    'tl_minutes' => $log ? $log->tl_minutes : 0,
                    'psw_minutes' => $log ? $log->psw_minutes : 0,
                    'status' => $status,
                    'notes' => $log ? $log->notes : null,
                    'source' => $log ? ($log->source ?: 'Mesin') : ($hasST ? 'ST' : '-'),
                ];
            }
        }

        return view('admin.attendance.manage', compact(
            'allPegawai',
            'monthYear',
            'selectedUser',
            'dailyData',
            'stats'
        ));
    }

    /**
     * SIPEGA: Update / Beri Keterangan & Status Kehadiran Pegawai
     */
    public function updateLog(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator'])) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|string',
            'notes' => 'nullable|string|max:500',
            'check_in' => 'nullable|string',
            'check_out' => 'nullable|string',
            'tl_minutes' => 'nullable|integer|min:0',
            'psw_minutes' => 'nullable|integer|min:0',
        ]);

        $userId = $request->user_id;
        $date = Carbon::parse($request->date)->toDateString();
        $status = $request->status;
        $notes = $request->notes;
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;
        $tlMinutes = $request->filled('tl_minutes') ? (int)$request->tl_minutes : 0;
        $pswMinutes = $request->filled('psw_minutes') ? (int)$request->psw_minutes : 0;

        // Jika status DL, Cuti, Sakit, atau Izin dan user tidak mengisi khusus tl/psw, reset ke 0 agar tidak merugikan Tukin
        if (in_array($status, ['DL', 'Tugas Luar (ST)', 'Cuti', 'Sakit', 'Izin']) && !$request->filled('tl_minutes')) {
            $tlMinutes = 0;
            $pswMinutes = 0;
        }

        $log = AttendanceLog::firstOrNew([
            'user_id' => $userId,
            'date' => $date,
        ]);

        $log->status = $status;
        $log->notes = $notes;
        if ($request->filled('check_in')) {
            $log->check_in = $checkIn;
        }
        if ($request->filled('check_out')) {
            $log->check_out = $checkOut;
        }
        $log->tl_minutes = $tlMinutes;
        $log->psw_minutes = $pswMinutes;
        $log->source = 'Manual';
        $log->save();

        // Hitung ulang performa harian agar data Tukin & Raport Pegawai langsung mutakhir
        try {
            \Artisan::call('sipega:calculate-performance');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Calculate performance sync error: ' . $e->getMessage());
        }

        $pegawaiName = User::find($userId)?->name ?? 'Pegawai';
        $formattedDate = Carbon::parse($date)->translatedFormat('d M Y');

        return back()->with('success', "Keterangan kehadiran untuk {$pegawaiName} pada {$formattedDate} berhasil diperbarui.");
    }
}
