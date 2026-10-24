<?php

namespace App\Services;

use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AttendanceLogParserService
{
    /**
     * Parse and process attendance logs from uploaded file or raw string content.
     */
    public function parseAndImport(UploadedFile|string|null $source, array $options = []): array
    {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '512M');

        $parsedData = [];

        if ($source instanceof UploadedFile) {
            $parsedData = $this->parseFromFile($source);
        } elseif (is_string($source) && !empty(trim($source))) {
            $parsedData = $this->parseFromString($source);
        }

        if (empty($parsedData)) {
            return [
                'success' => false,
                'message' => 'Tidak ditemukan data transaksi kehadiran yang valid. Pastikan format file atau teks sesuai dengan Laporan Log Transaksi.',
                'total_employees' => 0,
                'matched_employees' => 0,
                'unmapped_employees' => [],
                'new_employees_created' => 0,
                'total_logs_processed' => 0,
                'date_range' => ['min' => null, 'max' => null],
                'employees_summary' => [],
            ];
        }

        return $this->saveToDatabase($parsedData, $options);
    }

    /**
     * Parse from an UploadedFile.
     */
    public function parseFromFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        // Pemerintah / Kemendikdasmen sering kali mendownload file HTML dengan ekstensi .xls
        // Periksa apakah file berformat teks/HTML
        $firstBytes = file_get_contents($path, false, null, 0, 1024);
        $isHtml = (
            stripos($firstBytes, '<html') !== false ||
            stripos($firstBytes, '<table') !== false ||
            stripos($firstBytes, '<!doctype') !== false ||
            in_array($extension, ['html', 'htm'])
        );

        if ($isHtml || in_array($extension, ['html', 'htm', 'txt'])) {
            $content = file_get_contents($path);
            return $this->parseFromString($content);
        }

        // Jika file Excel biner (.xlsx / OLE .xls / .csv)
        if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
            return $this->parseFromSpreadsheet($path);
        }

        return [];
    }

    /**
     * Parse raw string content (HTML or plain text).
     */
    public function parseFromString(string $content): array
    {
        if (stripos($content, '<table') !== false || stripos($content, '<td') !== false) {
            return $this->parseFromHtml($content);
        }

        return $this->parseFromPlainText($content);
    }

    /**
     * Parse HTML content (Kemendikdasmen / Laporan Log Transaksi format).
     */
    public function parseFromHtml(string $html): array
    {
        $results = [];

        // Pecah per tabel 'head' atau per blok halaman
        // Format Laporan Log Transaksi memiliki <table class="head"> untuk setiap pegawai
        $hasHeadClass = preg_match('/<table[^>]*class=["\']?head["\']?[^>]*>/i', $html);

        if ($hasHeadClass) {
            $sections = preg_split('/<table[^>]*class=["\']?head["\']?[^>]*>/i', $html);
            // Bagian pertama biasanya sebelum table head pertama
            for ($i = 1; $i < count($sections); $i++) {
                $section = $sections[$i];
                $parsed = $this->extractEmployeeFromHtmlSection($section);
                if ($parsed && (!empty($parsed['nip']) || !empty($parsed['nama']))) {
                    $results[] = $parsed;
                }
            }
        } else {
            // Fallback jika tidak ada class="head", pecah berdasarkan page-break atau <html>
            $sections = preg_split('/<div[^>]*page-break-after[^>]*>|<\/body>|<html/i', $html);
            foreach ($sections as $section) {
                if (stripos($section, 'Nama') !== false && stripos($section, 'NIP') !== false) {
                    $parsed = $this->extractEmployeeFromHtmlSection($section);
                    if ($parsed && (!empty($parsed['nip']) || !empty($parsed['nama']))) {
                        $results[] = $parsed;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Extract single employee info and log rows from an HTML section.
     */
    protected function extractEmployeeFromHtmlSection(string $section): ?array
    {
        // Bersihkan komentar HTML <!-- ... --> terlebih dahulu
        $cleanSection = preg_replace('/<!--.*?-->/s', '', $section);

        $nama = '';
        if (preg_match('/Nama\s*<\/td>\s*<td[^>]*>\s*:\s*([^<]+)<\/td>/i', $cleanSection, $m)) {
            $nama = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        } elseif (preg_match('/Nama\s*:\s*([^\r\n<]+)/i', strip_tags($cleanSection), $m)) {
            $nama = trim($m[1]);
        }

        $nip = '';
        if (preg_match('/NIP\s*<\/td>\s*<td[^>]*>\s*:\s*([^<]+)<\/td>/i', $cleanSection, $m)) {
            $nip = preg_replace('/[^0-9]/', '', $m[1]);
        } elseif (preg_match('/NIP\s*:\s*([0-9\s]+)/i', strip_tags($cleanSection), $m)) {
            $nip = preg_replace('/[^0-9]/', '', $m[1]);
        }

        $jabatan = '';
        if (preg_match('/Jabatan\s*<\/td>\s*<td[^>]*>\s*:\s*([^<]+)<\/td>/i', $cleanSection, $m)) {
            $jabatan = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        $organisasi = '';
        if (preg_match('/Organisasi\s*<\/td>\s*<td[^>]*>\s*:\s*([^<]+)<\/td>/i', $cleanSection, $m)) {
            $organisasi = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        // Ambil baris log (Tanggal & Jam) secara baris per baris agar fleksibel terhadap berbagai tabel
        $logs = [];
        if (preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $cleanSection, $trMatches)) {
            foreach ($trMatches[1] as $trHtml) {
                if (preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $trHtml, $tdMatches)) {
                    $cells = array_map(function($c) {
                        return trim(strip_tags(html_entity_decode($c, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                    }, $tdMatches[1]);

                    $rowDate = null;
                    $rowTime = null;

                    foreach ($cells as $cell) {
                        if (!$rowDate && preg_match('/(\d{2}[-\/]\d{2}[-\/]\d{4})/', $cell, $dm)) {
                            $rawDate = str_replace('/', '-', $dm[1]);
                            $dParts = explode('-', $rawDate);
                            $rowDate = sprintf('%04d-%02d-%02d', (int)$dParts[2], (int)$dParts[1], (int)$dParts[0]);
                        } elseif (!$rowTime && preg_match('/(\d{2}:\d{2}(?::\d{2})?)/', $cell, $tm)) {
                            $t = $tm[1];
                            if (strlen($t) === 5) {
                                $t .= ':00';
                            }
                            $rowTime = $t;
                        }
                    }

                    if ($rowDate && $rowTime) {
                        $logs[] = [
                            'date' => $rowDate,
                            'time' => $rowTime
                        ];
                    }
                }
            }
        }

        if (empty($nip) && empty($nama)) {
            return null;
        }

        return [
            'nama' => $nama,
            'nip' => $nip,
            'jabatan' => $jabatan,
            'organisasi' => $organisasi,
            'logs' => $logs
        ];
    }

    /**
     * Parse plain text format (e.g. copied text).
     */
    public function parseFromPlainText(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $results = [];

        $currentEmployee = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;

            // Cek jika ada Nama
            if (preg_match('/Nama\s*:\s*(.+)/i', $trimmed, $m)) {
                if ($currentEmployee && (!empty($currentEmployee['nip']) || !empty($currentEmployee['nama']))) {
                    $results[] = $currentEmployee;
                }
                $currentEmployee = [
                    'nama' => trim($m[1]),
                    'nip' => '',
                    'jabatan' => '',
                    'organisasi' => '',
                    'logs' => []
                ];
                continue;
            }

            // Cek jika ada NIP
            if (preg_match('/NIP\s*:\s*([0-9\s]+)/i', $trimmed, $m)) {
                if (!$currentEmployee) {
                    $currentEmployee = [
                        'nama' => '',
                        'nip' => '',
                        'jabatan' => '',
                        'organisasi' => '',
                        'logs' => []
                    ];
                }
                $currentEmployee['nip'] = preg_replace('/[^0-9]/', '', $m[1]);
                continue;
            }

            // Cek jika ada Jabatan
            if (preg_match('/Jabatan\s*:\s*(.+)/i', $trimmed, $m)) {
                if ($currentEmployee) {
                    $currentEmployee['jabatan'] = trim($m[1]);
                }
                continue;
            }

            // Cek baris log tanggal & jam: DD-MM-YYYY HH:MM:SS
            if (preg_match('/(\d{2}[-\/]\d{2}[-\/]\d{4})[\s\t]+(\d{2}:\d{2}(?::\d{2})?)/', $trimmed, $m)) {
                $rawDate = str_replace('/', '-', $m[1]);
                $dParts = explode('-', $rawDate);
                $formattedDate = sprintf('%04d-%02d-%02d', (int)$dParts[2], (int)$dParts[1], (int)$dParts[0]);
                
                $time = trim($m[2]);
                if (strlen($time) === 5) {
                    $time .= ':00';
                }

                if ($currentEmployee) {
                    $currentEmployee['logs'][] = [
                        'date' => $formattedDate,
                        'time' => $time
                    ];
                }
            }
        }

        if ($currentEmployee && (!empty($currentEmployee['nip']) || !empty($currentEmployee['nama']))) {
            $results[] = $currentEmployee;
        }

        return $results;
    }

    /**
     * Parse binary Excel/CSV spreadsheet.
     */
    protected function parseFromSpreadsheet(string $filePath): array
    {
        $results = [];

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $currentEmployee = null;

            foreach ($rows as $row) {
                $lineContent = implode(' ', array_filter(array_map('strval', $row)));

                // Deteksi Nama
                if (preg_match('/Nama\s*:\s*([^;,\t]+)/i', $lineContent, $m)) {
                    if ($currentEmployee && (!empty($currentEmployee['nip']) || !empty($currentEmployee['nama']))) {
                        $results[] = $currentEmployee;
                    }
                    $currentEmployee = [
                        'nama' => trim($m[1]),
                        'nip' => '',
                        'jabatan' => '',
                        'organisasi' => '',
                        'logs' => []
                    ];
                }

                // Deteksi NIP
                if (preg_match('/NIP\s*:\s*([0-9\s]+)/i', $lineContent, $m)) {
                    if (!$currentEmployee) {
                        $currentEmployee = [
                            'nama' => '',
                            'nip' => '',
                            'jabatan' => '',
                            'organisasi' => '',
                            'logs' => []
                        ];
                    }
                    $currentEmployee['nip'] = preg_replace('/[^0-9]/', '', $m[1]);
                }

                // Deteksi Tanggal dan Jam dalam sel baris
                foreach ($row as $cell) {
                    if (is_string($cell) && preg_match('/(\d{2}[-\/]\d{2}[-\/]\d{4})/', $cell, $dateMatches)) {
                        $rawDate = str_replace('/', '-', $dateMatches[1]);
                        $dParts = explode('-', $rawDate);
                        $formattedDate = sprintf('%04d-%02d-%02d', (int)$dParts[2], (int)$dParts[1], (int)$dParts[0]);

                        foreach ($row as $timeCell) {
                            if (is_string($timeCell) && preg_match('/(\d{2}:\d{2}(?::\d{2})?)/', $timeCell, $timeMatches)) {
                                $time = trim($timeMatches[1]);
                                if (strlen($time) === 5) {
                                    $time .= ':00';
                                }
                                if ($currentEmployee) {
                                    $currentEmployee['logs'][] = [
                                        'date' => $formattedDate,
                                        'time' => $time
                                    ];
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
            }

            if ($currentEmployee && (!empty($currentEmployee['nip']) || !empty($currentEmployee['nama']))) {
                $results[] = $currentEmployee;
            }
        } catch (\Exception $e) {
            Log::error('Spreadsheet parse error: ' . $e->getMessage());
        }

        return $results;
    }

    /**
     * Process parsed data and save to database.
     */
    protected function saveToDatabase(array $parsedData, array $options = []): array
    {
        $officeStartTime = $options['office_start_time'] ?? '07:30:00';
        $officeEndWeekday = $options['office_end_weekday'] ?? '16:00:00';
        $officeEndFriday = $options['office_end_friday'] ?? '16:30:00';
        // Default auto-create user adalah TRUE agar pegawai dari portal presensi otomatis terdaftar dan log tidak hilang
        $autoCreateUser = !array_key_exists('auto_create_user', $options) || !empty($options['auto_create_user']);

        $totalEmployees = count($parsedData);
        $matchedEmployees = 0;
        $unmappedEmployees = [];
        $newEmployeesCreated = 0;
        $totalLogsProcessed = 0;
        $allDates = [];
        $employeesSummary = [];

        foreach ($parsedData as $emp) {
            $nip = preg_replace('/[^0-9]/', '', $emp['nip'] ?? '');
            $nama = trim($emp['nama'] ?? '');
            $jabatan = trim($emp['jabatan'] ?? '');

            // 1. Cari Pegawai di Database SIPEGA (Bersihkan spasi/strip pada NIP di database)
            $user = null;
            if (!empty($nip)) {
                $user = User::whereRaw("REPLACE(REPLACE(COALESCE(nip, ''), ' ', ''), '-', '') = ?", [$nip])->first();
                if (!$user) {
                    $user = User::where('nip', 'like', $nip . '%')->first();
                }
            }

            if (!$user && !empty($nama)) {
                // Coba cocokkan nama jika NIP tidak cocok
                $user = User::where('name', $nama)->first();
                if (!$user) {
                    $user = User::where('name', 'like', '%' . $nama . '%')->first();
                }
            }

            // 2. Jika tidak ditemukan, daftarkan otomatis akun pegawai
            if (!$user && !empty($nip) && $autoCreateUser) {
                try {
                    $email = $nip . '@sipega.local';
                    if (User::where('email', $email)->exists()) {
                        $email = $nip . '.' . rand(100, 999) . '@sipega.local';
                    }

                    $user = User::create([
                        'name' => $nama ?: ('Pegawai ' . $nip),
                        'nip' => $nip,
                        'email' => $email,
                        'password' => Hash::make($nip),
                        'position' => $jabatan ?: 'Pegawai',
                        'role' => 'Pegawai',
                        'is_active' => true,
                    ]);
                    $newEmployeesCreated++;
                } catch (\Exception $e) {
                    Log::error("Failed to auto-create user NIP {$nip}: " . $e->getMessage());
                }
            }

            if (!$user) {
                $unmappedEmployees[] = [
                    'nip' => $nip ?: '-',
                    'nama' => $nama ?: 'Nama Tidak Diketahui',
                    'jabatan' => $jabatan ?: '-',
                    'logs_count' => count($emp['logs'] ?? [])
                ];
                continue;
            }

            $matchedEmployees++;

            // Update jabatan jika jabatan di user masih kosong
            if (!empty($jabatan) && empty($user->position)) {
                $user->update(['position' => $jabatan]);
            }
            // Update NIP jika di user masih kosong
            if (!empty($nip) && empty($user->nip)) {
                $user->update(['nip' => $nip]);
            }

            // 3. Kelompokkan log berdasarkan tanggal
            $datesGroup = [];
            foreach ($emp['logs'] as $log) {
                $date = $log['date'];
                $time = $log['time'];
                $datesGroup[$date][] = $time;
                $allDates[] = $date;
            }

            $empSummary = [
                'user_id' => $user->id,
                'name' => $user->name,
                'nip' => $user->nip,
                'position' => $user->position,
                'total_days' => count($datesGroup),
                'total_tl_minutes' => 0,
                'total_psw_minutes' => 0,
            ];

            // 4. Proses setiap hari kehadiran
            foreach ($datesGroup as $date => $times) {
                sort($times);
                $count = count($times);

                // Penentuan Check-in dan Check-out
                if ($count === 1) {
                    $singleTime = $times[0];
                    if ($singleTime < '12:00:00') {
                        $checkIn = $singleTime;
                        $checkOut = null;
                    } else {
                        // Jika scan tunggal di siang/sore hari, anggap check-out
                        $checkIn = null;
                        $checkOut = $singleTime;
                    }
                } else {
                    $checkIn = $times[0];
                    $checkOut = end($times);
                }

                // Hitung Terlambat (TL)
                $tlMinutes = 0;
                if ($checkIn) {
                    $dtCheckIn = Carbon::parse($date . ' ' . $checkIn);
                    $officeStart = Carbon::parse($date . ' ' . $officeStartTime);
                    if ($dtCheckIn->gt($officeStart)) {
                        $tlMinutes = (int)abs($dtCheckIn->diffInMinutes($officeStart));
                    }
                }

                // Hitung Pulang Sebelum Waktu (PSW)
                $pswMinutes = 0;
                if ($checkOut) {
                    $dtCheckOut = Carbon::parse($date . ' ' . $checkOut);
                    $dayOfWeek = Carbon::parse($date)->dayOfWeek;
                    $exitTime = ($dayOfWeek == Carbon::FRIDAY) ? $officeEndFriday : $officeEndWeekday;
                    $officeEnd = Carbon::parse($date . ' ' . $exitTime);

                    if ($dtCheckOut->lt($officeEnd)) {
                        $pswMinutes = (int)abs($officeEnd->diffInMinutes($dtCheckOut));
                    }
                }

                // Cek apakah ada penugasan resmi / Dinas Luar (ST)
                $isDL = DB::table('assignment_letter_user')
                    ->join('assignment_letters', 'assignment_letter_user.assignment_letter_id', '=', 'assignment_letters.id')
                    ->where('assignment_letter_user.user_id', $user->id)
                    ->where('assignment_letters.date', $date)
                    ->exists();

                if (!$isDL) {
                    // Cek tabel letters (Surat Tugas Formal yang Approved)
                    $isDL = $user->letters()
                        ->where('status', 'Approved')
                        ->where('date_start', '<=', $date)
                        ->whereRaw('COALESCE(date_end, date_start) >= ?', [$date])
                        ->exists();
                }

                if ($isDL) {
                    $status = 'DL';
                    $notes = 'Sinkronisasi Log Mesin (Ada Surat Tugas Resmi).';
                    $tlMinutes = 0;
                    $pswMinutes = 0;
                } else {
                    // Logika: Jika jam masuk atau pulang kosong, jangan anggap 0 menit; tandai untuk verifikasi
                    if (empty($checkIn) && !empty($checkOut)) {
                        $status = 'LD'; // Lupa Absen Datang
                        $notes = 'Perlu Verifikasi: Jam masuk kosong (hanya scan pulang).';
                        $tlMinutes = 0;
                    } elseif (!empty($checkIn) && empty($checkOut)) {
                        $status = 'LP'; // Lupa Absen Pulang
                        $notes = 'Perlu Verifikasi: Jam pulang kosong (hanya scan datang).';
                        $pswMinutes = 0;
                    } else {
                        $status = 'Hadir';
                        $notes = 'Sinkronisasi Log Mesin Absensi (' . $count . ' scan).';
                    }
                }

                $empSummary['total_tl_minutes'] += $tlMinutes;
                $empSummary['total_psw_minutes'] += $pswMinutes;

                // Simpan atau Perbarui AttendanceLog
                AttendanceLog::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $date
                    ],
                    [
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'tl_minutes' => $tlMinutes,
                        'psw_minutes' => $pswMinutes,
                        'status' => $status,
                        'notes' => $notes,
                        'source' => 'Mesin'
                    ]
                );

                $totalLogsProcessed++;
            }

            $employeesSummary[] = $empSummary;
        }

        // Jalankan kalkulasi performa agar rapor capaian sinkron
        try {
            Artisan::call('sipega:calculate-performance');
        } catch (\Throwable $e) {
            Log::warning('Artisan sipega:calculate-performance warning: ' . $e->getMessage());
        }

        $minDate = !empty($allDates) ? min($allDates) : null;
        $maxDate = !empty($allDates) ? max($allDates) : null;

        return [
            'success' => true,
            'message' => "Berhasil memproses log kehadiran untuk {$matchedEmployees} pegawai ({$totalLogsProcessed} data rekor kehadiran harian).",
            'total_employees' => $totalEmployees,
            'matched_employees' => $matchedEmployees,
            'new_employees_created' => $newEmployeesCreated,
            'unmapped_employees' => $unmappedEmployees,
            'total_logs_processed' => $totalLogsProcessed,
            'date_range' => [
                'min' => $minDate ? Carbon::parse($minDate)->translatedFormat('d F Y') : null,
                'max' => $maxDate ? Carbon::parse($maxDate)->translatedFormat('d F Y') : null,
                'raw_min' => $minDate,
                'raw_max' => $maxDate,
            ],
            'employees_summary' => $employeesSummary,
        ];
    }
}
