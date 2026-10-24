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
                $tlVal = max(0, (int)($log->tl_minutes ?? 0));
                $pswVal = max(0, (int)($log->psw_minutes ?? 0));
                $summary['total_present']++;
                $summary['total_tl_minutes'] += $tlVal;
                $summary['total_psw_minutes'] += $pswVal;
            }

            $recapData[] = [
                'date' => $dateStr,
                'check_in' => $log ? $log->check_in : null,
                'check_out' => $log ? $log->check_out : null,
                'tl_minutes' => $log ? max(0, (int)($log->tl_minutes ?? 0)) : 0,
                'psw_minutes' => $log ? max(0, (int)($log->psw_minutes ?? 0)) : 0,
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
            'office_start_time' => 'nullable|string',
            'office_end_weekday' => 'nullable|string',
            'office_end_friday' => 'nullable|string',
            'is_ramadhan' => 'nullable|boolean',
        ], [
            'log_file.mimes' => 'Format file harus berupa .html, .htm, .xls, .xlsx, .csv, atau .txt',
            'log_file.max' => 'Ukuran file maksimal adalah 20MB',
        ]);

        $file = $request->file('log_file') ?? $request->file('excel_file');
        $rawContent = $request->input('raw_content');

        if (!$file && empty(trim((string)$rawContent))) {
            return back()->withErrors(['log_file' => 'Silakan pilih file untuk diunggah ATAU tempelkan teks/HTML data log transaksi.']);
        }

        $isRamadhan = $request->boolean('is_ramadhan');
        $startTime = $request->input('office_start_time') ?: ($isRamadhan ? '08:00' : '07:30');
        if (strlen($startTime) === 5) $startTime .= ':00';

        $endWeekday = $request->input('office_end_weekday') ?: ($isRamadhan ? '15:00' : '16:00');
        if (strlen($endWeekday) === 5) $endWeekday .= ':00';

        $endFriday = $request->input('office_end_friday') ?: ($isRamadhan ? '15:30' : '16:30');
        if (strlen($endFriday) === 5) $endFriday .= ':00';

        $options = [
            'office_start_time' => $startTime,
            'office_end_weekday' => $endWeekday,
            'office_end_friday' => $endFriday,
            'auto_create_user' => $request->has('auto_create_user') ? $request->boolean('auto_create_user') : true,
            'is_ramadhan' => $isRamadhan,
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
            ->get(['id', 'name', 'nip', 'position', 'gugus_mutu']);

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
            'ld' => 0,
            'lp' => 0,
            'lds' => 0,
            'lps' => 0,
            'alpa' => 0,
            'libur' => 0,
            'total_tl' => 0,
            'total_psw' => 0,
        ];

        if ($selectedUser) {
            // Bersihkan data lama jika ada nilai tl_minutes atau psw_minutes yang negatif
            AttendanceLog::where('user_id', $selectedUser->id)->where('tl_minutes', '<', 0)->update(['tl_minutes' => 0]);
            AttendanceLog::where('user_id', $selectedUser->id)->where('psw_minutes', '<', 0)->update(['psw_minutes' => 0]);

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
                } elseif (in_array($status, ['LD', 'Lupa Absen Datang (LD)'])) {
                    $stats['ld']++;
                } elseif (in_array($status, ['LP', 'Lupa Absen Pulang (LP)'])) {
                    $stats['lp']++;
                } elseif (in_array($status, ['LDS', 'Lupa Absen Datang Bersurat (LDS)'])) {
                    $stats['lds']++;
                } elseif (in_array($status, ['LPS', 'Lupa Absen Pulang Bersurat (LPS)'])) {
                    $stats['lps']++;
                } elseif ($status === 'ALPA') {
                    $stats['alpa']++;
                } elseif ($status === 'Libur') {
                    $stats['libur']++;
                }

                $tlVal = 0;
                $pswVal = 0;

                if ($log) {
                    $tlVal = max(0, (int)($log->tl_minutes ?? 0));
                    $pswVal = max(0, (int)($log->psw_minutes ?? 0));
                    $stats['total_tl'] += $tlVal;
                    $stats['total_psw'] += $pswVal;
                }

                $dailyData[] = [
                    'date' => $dateStr,
                    'day_name' => $date->translatedFormat('l'),
                    'formatted_date' => $date->translatedFormat('d M Y'),
                    'is_working_day' => $isWorkingDay,
                    'is_weekend' => $isWeekend,
                    'is_holiday' => $isHoliday,
                    'day_of_week' => $date->dayOfWeek,
                    'has_st' => $hasST,
                    'log_id' => $log ? $log->id : null,
                    'check_in' => $log ? $log->check_in : null,
                    'check_out' => $log ? $log->check_out : null,
                    'tl_minutes' => $tlVal,
                    'psw_minutes' => $pswVal,
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
        $isRamadhan = $request->boolean('is_ramadhan');

        $officeStartTime = $isRamadhan ? '08:00:00' : '07:30:00';
        $officeEndWeekday = $isRamadhan ? '15:00:00' : '16:00:00';
        $officeEndFriday = $isRamadhan ? '15:30:00' : '16:30:00';

        // Hitung menit keterlambatan (TL) dan pulang sebelum waktunya (PSW)
        if (in_array($status, ['DL', 'Tugas Luar (ST)', 'Cuti', 'Sakit', 'Izin', 'LDS', 'LPS', 'Libur'])) {
            $tlMinutes = 0;
            $pswMinutes = 0;
        } else {
            // Menit Terlambat (TL)
            if ($request->boolean('auto_calculate') || !$request->filled('tl_minutes')) {
                if (!empty($checkIn)) {
                    $checkInFormatted = strlen($checkIn) === 5 ? ($checkIn . ':00') : $checkIn;
                    $dtCheckIn = Carbon::parse($date . ' ' . $checkInFormatted);
                    $dtStart = Carbon::parse($date . ' ' . $officeStartTime);
                    $tlMinutes = $dtCheckIn->gt($dtStart) ? $dtCheckIn->diffInMinutes($dtStart) : 0;
                } else {
                    $tlMinutes = 0;
                }
            } else {
                $tlMinutes = (int)$request->tl_minutes;
            }

            // Menit Pulang Sebelum Waktu (PSW)
            if ($request->boolean('auto_calculate') || !$request->filled('psw_minutes')) {
                if (!empty($checkOut)) {
                    $checkOutFormatted = strlen($checkOut) === 5 ? ($checkOut . ':00') : $checkOut;
                    $dtCheckOut = Carbon::parse($date . ' ' . $checkOutFormatted);
                    $dayOfWeek = Carbon::parse($date)->dayOfWeek;
                    $exitTarget = ($dayOfWeek == Carbon::FRIDAY) ? $officeEndFriday : $officeEndWeekday;
                    $dtEnd = Carbon::parse($date . ' ' . $exitTarget);
                    $pswMinutes = $dtCheckOut->lt($dtEnd) ? $dtEnd->diffInMinutes($dtCheckOut) : 0;
                } else {
                    $pswMinutes = 0;
                }
            } else {
                $pswMinutes = (int)$request->psw_minutes;
            }
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

        return back()->with('success', "Keterangan dan kalkulasi kehadiran untuk {$pegawaiName} pada {$formattedDate} berhasil diperbarui (TL: {$tlMinutes} mnt, PSW: {$pswMinutes} mnt).");
    }

    /**
     * SIPEGA: Hitung Ulang Menit TL dan PSW Satu Bulan untuk Pegawai Terpilih
     */
    public function recalculateMonth(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator'])) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|string',
            'is_ramadhan' => 'nullable|boolean',
        ]);

        $userId = $request->user_id;
        $user = User::findOrFail($userId);
        $month = $request->month;
        $isRamadhan = $request->boolean('is_ramadhan');

        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $officeStartTime = $isRamadhan ? '08:00:00' : '07:30:00';
        $officeEndWeekday = $isRamadhan ? '15:00:00' : '16:00:00';
        $officeEndFriday = $isRamadhan ? '15:30:00' : '16:30:00';

        $logs = AttendanceLog::where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get();

        $countUpdated = 0;
        foreach ($logs as $log) {
            $date = Carbon::parse($log->date)->toDateString();
            $status = $log->status;

            if (in_array($status, ['DL', 'Tugas Luar (ST)', 'Cuti', 'Sakit', 'Izin', 'LDS', 'LPS', 'Libur'])) {
                $log->tl_minutes = 0;
                $log->psw_minutes = 0;
            } else {
                // Hitung TL
                if ($log->check_in) {
                    $checkInFormatted = strlen($log->check_in) === 5 ? ($log->check_in . ':00') : $log->check_in;
                    $dtCheckIn = Carbon::parse($date . ' ' . $checkInFormatted);
                    $dtStart = Carbon::parse($date . ' ' . $officeStartTime);
                    $log->tl_minutes = $dtCheckIn->gt($dtStart) ? $dtCheckIn->diffInMinutes($dtStart) : 0;
                } else {
                    $log->tl_minutes = 0;
                }

                // Hitung PSW
                if ($log->check_out) {
                    $checkOutFormatted = strlen($log->check_out) === 5 ? ($log->check_out . ':00') : $log->check_out;
                    $dtCheckOut = Carbon::parse($date . ' ' . $checkOutFormatted);
                    $dayOfWeek = Carbon::parse($date)->dayOfWeek;
                    $exitTarget = ($dayOfWeek == Carbon::FRIDAY) ? $officeEndFriday : $officeEndWeekday;
                    $dtEnd = Carbon::parse($date . ' ' . $exitTarget);
                    $log->psw_minutes = $dtCheckOut->lt($dtEnd) ? $dtEnd->diffInMinutes($dtCheckOut) : 0;
                } else {
                    $log->psw_minutes = 0;
                }
            }

            $log->save();
            $countUpdated++;
        }

        // Hitung ulang performa harian
        try {
            \Artisan::call('sipega:calculate-performance');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Calculate performance recalculate error: ' . $e->getMessage());
        }

        $polaName = $isRamadhan ? 'Pola Ramadhan (08:00 / 15:00 / 15:30)' : 'Pola Normal (07:30 / 16:00 / 16:30)';
        return back()->with('success', "Menit Keterlambatan (TL) & Pulang Sebelum Waktu (PSW) untuk {$user->name} bulan {$month} berhasil dihitung ulang ({$countUpdated} hari data, {$polaName}).");
    }

    /**
     * SIPEGA: Hitung Ulang Menit TL dan PSW Satu Bulan untuk SEMUA Pegawai (All Pegawai)
     */
    public function recalculateAllEmployees(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator'])) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'month' => 'required|string',
            'is_ramadhan' => 'nullable|boolean',
        ]);

        ini_set('max_execution_time', 600);

        $month = $request->month;
        $isRamadhan = $request->boolean('is_ramadhan');

        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $officeStartTime = $isRamadhan ? '08:00:00' : '07:30:00';
        $officeEndWeekday = $isRamadhan ? '15:00:00' : '16:00:00';
        $officeEndFriday = $isRamadhan ? '15:30:00' : '16:30:00';

        $logs = AttendanceLog::whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get();

        $countUpdated = 0;
        $affectedUsers = [];

        foreach ($logs as $log) {
            $date = Carbon::parse($log->date)->toDateString();
            $status = $log->status;

            if (in_array($status, ['DL', 'Tugas Luar (ST)', 'Cuti', 'Sakit', 'Izin', 'LDS', 'LPS', 'Libur'])) {
                $log->tl_minutes = 0;
                $log->psw_minutes = 0;
            } else {
                // Hitung TL
                if ($log->check_in) {
                    $checkInFormatted = strlen($log->check_in) === 5 ? ($log->check_in . ':00') : $log->check_in;
                    $dtCheckIn = Carbon::parse($date . ' ' . $checkInFormatted);
                    $dtStart = Carbon::parse($date . ' ' . $officeStartTime);
                    $log->tl_minutes = $dtCheckIn->gt($dtStart) ? $dtCheckIn->diffInMinutes($dtStart) : 0;
                } else {
                    $log->tl_minutes = 0;
                }

                // Hitung PSW
                if ($log->check_out) {
                    $checkOutFormatted = strlen($log->check_out) === 5 ? ($log->check_out . ':00') : $log->check_out;
                    $dtCheckOut = Carbon::parse($date . ' ' . $checkOutFormatted);
                    $dayOfWeek = Carbon::parse($date)->dayOfWeek;
                    $exitTarget = ($dayOfWeek == Carbon::FRIDAY) ? $officeEndFriday : $officeEndWeekday;
                    $dtEnd = Carbon::parse($date . ' ' . $exitTarget);
                    $log->psw_minutes = $dtCheckOut->lt($dtEnd) ? $dtEnd->diffInMinutes($dtCheckOut) : 0;
                } else {
                    $log->psw_minutes = 0;
                }
            }

            $log->save();
            $countUpdated++;
            $affectedUsers[$log->user_id] = true;
        }

        // Hitung ulang performa harian agar data Tukin & Raport Pegawai langsung mutakhir
        try {
            \Artisan::call('sipega:calculate-performance');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Calculate performance recalculate all error: ' . $e->getMessage());
        }

        $totalUsers = count($affectedUsers);
        $polaName = $isRamadhan ? 'Pola Ramadhan (08:00 / 15:00 / 15:30)' : 'Pola Normal (07:30 / 16:00 / 16:30)';
        $monthName = Carbon::parse($month . '-01')->translatedFormat('F Y');

        return back()->with('success', "Menit TL & PSW untuk SEMUA Pegawai ({$totalUsers} pegawai, {$countUpdated} hari transaksi) periode {$monthName} berhasil dihitung ulang ({$polaName}).");
    }
}
