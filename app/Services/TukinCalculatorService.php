<?php

namespace App\Services;

use App\Models\User;
use App\Models\JobClass;
use App\Models\AttendanceLog;
use App\Models\CalendarEvent;
use App\Models\TukinCalculation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TukinCalculatorService
{
    /**
     * Tarif resmi nominal Tukin berdasarkan Permendikdasmen No. 14 Tahun 2025 / Perpres No. 18 Tahun 2025
     */
    public const OFFICIAL_RATES = [
        '17' => 33240000,
        '16' => 27577500,
        '15' => 19280000,
        '14' => 17064000,
        '13' => 10936000,
        '12' => 9896000,
        '11' => 8757600,
        '10' => 5979200,
        '9'  => 5079200,
        '8'  => 4595150,
        '7'  => 3915950,
        '6'  => 3510400,
        '5'  => 3134250,
        '4'  => 2985000,
        '3'  => 2898000,
        '2'  => 2708250,
        '1'  => 2531250,
    ];

    /**
     * Dapatkan nominal dasar tukin berdasarkan grade
     */
    public static function getBaseTukinByGrade($grade): float
    {
        $gradeKey = (string)intval($grade);
        if (isset(self::OFFICIAL_RATES[$gradeKey])) {
            return (float)self::OFFICIAL_RATES[$gradeKey];
        }

        $jc = JobClass::where('class_name', $gradeKey)->first();
        if ($jc && $jc->base_amount > 0) {
            return (float)$jc->base_amount;
        }

        return 0.0;
    }

    /**
     * Hitung tukin seluruh pegawai aktif untuk suatu periode (YYYY-MM) dari database SIPEGA
     */
    public function calculatePeriodFromDatabase(string $period, ?int $operatorId = null): array
    {
        $dateObj = Carbon::parse($period . '-01', 'Asia/Makassar');
        $startOfMonth = $dateObj->copy()->startOfMonth();
        $endOfMonth = $dateObj->copy()->endOfMonth();

        $users = User::realPegawai()->with('jobClass')->orderBy('name', 'asc')->get();

        $holidays = CalendarEvent::whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->whereIn('type', ['Holiday', 'Shared Leave'])
            ->pluck('date')
            ->toArray();

        $processedCount = 0;

        foreach ($users as $user) {
            $calc = $this->calculateSingleUserAttendance($user, $startOfMonth, $endOfMonth, $holidays);

            TukinCalculation::updateOrCreate(
                [
                    'period' => $period,
                    'nip' => $user->nip ?? 'NIP-' . $user->id,
                ],
                array_merge($calc, [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'position' => $user->position,
                    'golongan' => $user->golongan,
                    'grade' => (string)($user->grade ?? ($user->jobClass->class_name ?? 6)),
                    'source' => 'system_recalc',
                    'created_by' => $operatorId,
                ])
            );

            $processedCount++;
        }

        return [
            'period' => $period,
            'total_users' => $processedCount,
        ];
    }

    /**
     * Hitung detail presensi dan potongan 1 orang pegawai sesuai Permendikdasmen 14/2025
     */
    public function calculateSingleUserAttendance(User $user, Carbon $startOfMonth, Carbon $endOfMonth, array $holidays): array
    {
        $periodRange = CarbonPeriod::create($startOfMonth, $endOfMonth);
        
        $baseAmount = $user->base_tukin > 0 
            ? (float)$user->base_tukin 
            : self::getBaseTukinByGrade($user->grade ?? ($user->jobClass->class_name ?? 0));

        // Hari penugasan resmi / ST
        $activeAssignmentDays = DB::table('assignment_letter_user')
            ->join('assignment_letters', 'assignment_letter_user.assignment_letter_id', '=', 'assignment_letters.id')
            ->where('assignment_letter_user.user_id', $user->id)
            ->whereBetween('assignment_letters.date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->pluck('assignment_letters.date')
            ->toArray();

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

        $logs = AttendanceLog::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn($l) => Carbon::parse($l->date)->toDateString());

        $counts = [
            'hadir_count' => 0,
            'dl_count' => 0,
            'cuti_count' => 0,
            'sakit_count' => 0,
            'alpa_count' => 0,
            'tl1_count' => 0,
            'tl2_count' => 0,
            'tl3_count' => 0,
            'tl4_count' => 0,
            'tl5_count' => 0,
            'psw1_count' => 0,
            'psw2_count' => 0,
            'psw3_count' => 0,
            'psw4_count' => 0,
            'psw5_count' => 0,
            'tkd_count' => 0,
            'tkp_count' => 0,
            'tbtk_count' => 0,
        ];

        $totalPenaltyPercent = 0.0;
        $totalMinutesViolation = 0;

        foreach ($periodRange as $date) {
            $dateStr = $date->toDateString();
            if ($date->isWeekend() || in_array($dateStr, $holidays)) {
                continue;
            }

            $log = $logs->get($dateStr);
            $isDL = in_array($dateStr, $activeAssignmentDays) || ($log && in_array($log->status, ['DL', 'Tugas Luar (ST)', 'DINAS LUAR']));

            if ($isDL) {
                $counts['dl_count']++;
                continue;
            }

            if ($log) {
                $st = strtoupper(trim($log->status ?? ''));
                if (str_contains($st, 'CUTI')) {
                    $counts['cuti_count']++;
                    continue;
                }
                if (str_contains($st, 'SAKIT')) {
                    $counts['sakit_count']++;
                    continue;
                }
                if (str_contains($st, 'ALPA') || str_contains($st, 'TK')) {
                    $counts['alpa_count']++;
                    $totalPenaltyPercent += 2.5;
                    continue;
                }

                $counts['hadir_count']++;

                $tlMin = $log->tl_minutes ?? 0;
                if ($tlMin > 0) {
                    $totalMinutesViolation += $tlMin;
                    if ($tlMin <= 30) {
                        $counts['tl1_count']++;
                        $totalPenaltyPercent += 0.25;
                    } elseif ($tlMin <= 60) {
                        $counts['tl2_count']++;
                        $totalPenaltyPercent += 0.50;
                    } elseif ($tlMin <= 90) {
                        $counts['tl3_count']++;
                        $totalPenaltyPercent += 0.75;
                    } elseif ($tlMin <= 120) {
                        $counts['tl4_count']++;
                        $totalPenaltyPercent += 1.00;
                    } else {
                        $counts['tl5_count']++;
                        $totalPenaltyPercent += 1.25;
                    }
                } elseif (str_contains($log->notes ?? '', 'TL1')) {
                    $counts['tl1_count']++;
                    $totalPenaltyPercent += 0.25;
                }

                $pswMin = $log->psw_minutes ?? 0;
                if ($pswMin > 0) {
                    $totalMinutesViolation += $pswMin;
                    if ($pswMin <= 30) {
                        $counts['psw1_count']++;
                        $totalPenaltyPercent += 0.25;
                    } elseif ($pswMin <= 60) {
                        $counts['psw2_count']++;
                        $totalPenaltyPercent += 0.50;
                    } elseif ($pswMin <= 90) {
                        $counts['psw3_count']++;
                        $totalPenaltyPercent += 0.75;
                    } elseif ($pswMin <= 120) {
                        $counts['psw4_count']++;
                        $totalPenaltyPercent += 1.00;
                    } else {
                        $counts['psw5_count']++;
                        $totalPenaltyPercent += 1.25;
                    }
                }

                if (empty($log->check_in) && !empty($log->check_out)) {
                    $counts['tkd_count']++;
                    $totalPenaltyPercent += 1.25;
                }
                if (!empty($log->check_in) && empty($log->check_out)) {
                    $counts['tkp_count']++;
                    $totalPenaltyPercent += 1.25;
                }
            } else {
                $counts['alpa_count']++;
                $totalPenaltyPercent += 2.5;
            }
        }

        if ($totalMinutesViolation >= 450) {
            $totalPenaltyPercent += 2.5;
        }

        $perfPredicate = $user->performance_predicate ?? 'Baik';
        $perfPenaltyPercent = 0.0;
        if (in_array(strtolower($perfPredicate), ['butuh perbaikan', 'cukup'])) {
            $perfPenaltyPercent = 20.0;
        } elseif (in_array(strtolower($perfPredicate), ['kurang', 'kurang (misconduct)'])) {
            $perfPenaltyPercent = 40.0;
        } elseif (in_array(strtolower($perfPredicate), ['sangat kurang'])) {
            $perfPenaltyPercent = 60.0;
        }

        $attendanceDeduction = round(($totalPenaltyPercent / 100) * $baseAmount, 2);
        $performanceDeduction = round(($perfPenaltyPercent / 100) * $baseAmount, 2);
        $totalPenaltyPercentAll = $totalPenaltyPercent + $perfPenaltyPercent;
        $totalDeduction = $attendanceDeduction + $performanceDeduction;

        $brutoAfterDeduction = max(0, $baseAmount - $totalDeduction);

        $taxPercent = TukinCalculation::getTaxRateByGolongan($user->golongan);
        $taxAmount = round(($taxPercent / 100) * $baseAmount, 2);
        $nettoAmount = max(0, $brutoAfterDeduction);

        return array_merge($counts, [
            'base_amount' => $baseAmount,
            'attendance_penalty_percent' => $totalPenaltyPercent,
            'attendance_deduction' => $attendanceDeduction,
            'performance_predicate' => $perfPredicate,
            'performance_penalty_percent' => $perfPenaltyPercent,
            'performance_deduction' => $performanceDeduction,
            'total_penalty_percent' => $totalPenaltyPercentAll,
            'total_deduction' => $totalDeduction,
            'bruto_after_deduction' => $brutoAfterDeduction,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'netto_amount' => $nettoAmount,
        ]);
    }

    /**
     * Memproses file Excel/CSV yang diunggah Kepegawaian / Pimpinan.
     */
    public function importFromExcel(UploadedFile|string $file, string $period, ?int $operatorId = null): array
    {
        $rows = [];

        if ($file instanceof UploadedFile) {
            $extension = strtolower($file->getClientOriginalExtension());
            $filePath = $file->getRealPath();
        } else {
            $filePath = $file;
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        }

        // Jika CSV atau file teks
        if (in_array($extension, ['csv', 'txt'])) {
            $content = file_get_contents($filePath);
            $delimiter = ',';
            if (substr_count($content, ';') > substr_count($content, ',')) {
                $delimiter = ';';
            } elseif (substr_count($content, "\t") > substr_count($content, ',')) {
                $delimiter = "\t";
            }

            $lines = preg_split('/\r\n|\r|\n/', trim($content));
            foreach ($lines as $line) {
                if (trim($line) === '') continue;
                $rows[] = str_getcsv($line, $delimiter);
            }
        } else {
            // XLSX atau XLS
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);
        }

        if (empty($rows)) {
            throw new \Exception('File Excel kosong atau tidak dapat dibaca.');
        }

        $formatType = $this->detectFormatType($rows);

        $savedCount = 0;
        $usersByNip = User::all()->keyBy(fn($u) => preg_replace('/[^0-9]/', '', $u->nip ?? ''));
        $usersByName = User::all()->keyBy(fn($u) => strtolower(trim($u->name)));

        if ($formatType === 'log_harian') {
            $savedCount = $this->processLogHarianRows($rows, $period, $usersByNip, $usersByName, $operatorId);
        } elseif ($formatType === 'rekap_pembayaran') {
            $savedCount = $this->processRekapPembayaranRows($rows, $period, $usersByNip, $usersByName, $operatorId);
        } else {
            $savedCount = $this->processRekapitulasiMatriksRows($rows, $period, $usersByNip, $usersByName, $operatorId);
        }

        return [
            'success' => true,
            'format' => $formatType,
            'period' => $period,
            'total_imported' => $savedCount,
        ];
    }

    protected function detectFormatType(array $rows): string
    {
        $topRowsText = '';
        $i = 0;
        foreach ($rows as $row) {
            $topRowsText .= ' ' . implode(' ', array_filter($row));
            if (++$i > 25) break;
        }
        $topRowsText = strtoupper($topRowsText);

        if (str_contains($topRowsText, 'KEDATANGAN') && str_contains($topRowsText, 'KEPULANGAN') && str_contains($topRowsText, 'TANGGAL')) {
            return 'log_harian';
        }

        if (str_contains($topRowsText, 'TUNJ KINERJA PER KELAS JAB') || str_contains($topRowsText, 'FAKTOR PENGURANG') || str_contains($topRowsText, 'JUMLAH NETTO')) {
            return 'rekap_pembayaran';
        }

        return 'rekap_matriks';
    }

    /**
     * Memproses format Log Harian (baris per tanggal)
     */
    protected function processLogHarianRows(array $rows, string $period, $usersByNip, $usersByName, ?int $operatorId): int
    {
        $employeeData = [];
        $currentNip = null;
        $currentName = null;

        foreach ($rows as $row) {
            $rowValues = array_values($row);
            $nipRaw = trim($rowValues[2] ?? $rowValues[1] ?? '');
            $nipClean = preg_replace('/[^0-9]/', '', $nipRaw);

            $nameRaw = trim($rowValues[1] ?? '');
            if (!empty($nameRaw) && !is_numeric($nameRaw) && strlen($nameRaw) > 3 && !str_contains(strtoupper($nameRaw), 'NAMA')) {
                $currentName = $nameRaw;
            }

            if (!empty($nipClean) && strlen($nipClean) >= 8) {
                $currentNip = $nipClean;
            }

            if (!$currentNip) continue;

            if (!isset($employeeData[$currentNip])) {
                $employeeData[$currentNip] = [
                    'nip' => $currentNip,
                    'name' => $currentName ?? 'Pegawai ' . $currentNip,
                    'logs' => [],
                ];
            }

            $date = trim($rowValues[3] ?? '');
            $in = trim($rowValues[4] ?? '');
            $out = trim($rowValues[5] ?? '');
            $ket = trim($rowValues[6] ?? '');

            if (!empty($date) && (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || preg_match('/^\d{2}[-\/]\d{2}[-\/]\d{4}$/', $date))) {
                $employeeData[$currentNip]['logs'][] = [
                    'date' => $date,
                    'in' => $in,
                    'out' => $out,
                    'ket' => $ket,
                ];
            }
        }

        $countSaved = 0;

        foreach ($employeeData as $cleanNip => $data) {
            $user = $usersByNip->get($cleanNip);
            if (!$user && isset($data['name'])) {
                $user = $usersByName->get(strtolower(trim($data['name'])));
            }

            $grade = $user ? ($user->grade ?? ($user->jobClass->class_name ?? 6)) : 6;
            $golongan = $user ? ($user->golongan ?? 'III/a') : 'III/a';
            $position = $user ? ($user->position ?? 'Pegawai') : 'Pegawai';
            $baseAmount = self::getBaseTukinByGrade($grade);

            $counts = [
                'hadir_count' => 0,
                'dl_count' => 0,
                'cuti_count' => 0,
                'sakit_count' => 0,
                'alpa_count' => 0,
                'tl1_count' => 0,
                'tl2_count' => 0,
                'tl3_count' => 0,
                'tl4_count' => 0,
                'tl5_count' => 0,
                'psw1_count' => 0,
                'psw2_count' => 0,
                'psw3_count' => 0,
                'psw4_count' => 0,
                'psw5_count' => 0,
                'tkd_count' => 0,
                'tkp_count' => 0,
                'tbtk_count' => 0,
            ];

            $totalPenaltyPercent = 0.0;

            foreach ($data['logs'] as $log) {
                $ket = strtoupper($log['ket']);
                if (str_contains($ket, 'DINAS LUAR') || str_contains($ket, 'DL')) {
                    $counts['dl_count']++;
                    continue;
                }
                if (str_contains($ket, 'CUTI')) {
                    $counts['cuti_count']++;
                    continue;
                }
                if (str_contains($ket, 'SAKIT')) {
                    $counts['sakit_count']++;
                    continue;
                }
                if (str_contains($ket, 'MENINGGAL') || str_contains($ket, 'PENSIUN') || str_contains($ket, 'CLTN')) {
                    continue;
                }

                $in = $log['in'];
                $out = $log['out'];
                $isNoIn = empty($in) || $in === 'NA' || $in === 'N/A';
                $isNoOut = empty($out) || $out === 'NA' || $out === 'N/A';

                if ($isNoIn && $isNoOut) {
                    if (str_contains($ket, 'ALPA') || str_contains($ket, 'TK') || $ket === '-' || empty($ket)) {
                        $counts['alpa_count']++;
                        $totalPenaltyPercent += 2.5;
                    }
                    continue;
                }

                $counts['hadir_count']++;

                if (str_contains($ket, 'TL1')) {
                    $counts['tl1_count']++;
                    $totalPenaltyPercent += 0.25;
                } elseif (str_contains($ket, 'TL2')) {
                    $counts['tl2_count']++;
                    $totalPenaltyPercent += 0.50;
                } elseif (str_contains($ket, 'TL3')) {
                    $counts['tl3_count']++;
                    $totalPenaltyPercent += 0.75;
                } elseif (str_contains($ket, 'TL4')) {
                    $counts['tl4_count']++;
                    $totalPenaltyPercent += 1.00;
                } elseif (str_contains($ket, 'TL5')) {
                    $counts['tl5_count']++;
                    $totalPenaltyPercent += 1.25;
                }

                if (str_contains($ket, 'TKD') || ($isNoIn && !$isNoOut)) {
                    $counts['tkd_count']++;
                    $totalPenaltyPercent += 1.25;
                }
                if (str_contains($ket, 'TKP') || (!$isNoIn && $isNoOut)) {
                    $counts['tkp_count']++;
                    $totalPenaltyPercent += 1.25;
                }
            }

            $attendanceDeduction = round(($totalPenaltyPercent / 100) * $baseAmount, 2);
            $taxPercent = TukinCalculation::getTaxRateByGolongan($golongan);
            $taxAmount = round(($taxPercent / 100) * $baseAmount, 2);
            $brutoAfter = max(0, $baseAmount - $attendanceDeduction);

            TukinCalculation::updateOrCreate(
                [
                    'period' => $period,
                    'nip' => $user?->nip ?? $data['nip'],
                ],
                array_merge($counts, [
                    'user_id' => $user?->id,
                    'name' => $user?->name ?? $data['name'],
                    'position' => $position,
                    'golongan' => $golongan,
                    'grade' => (string)$grade,
                    'base_amount' => $baseAmount,
                    'attendance_penalty_percent' => $totalPenaltyPercent,
                    'attendance_deduction' => $attendanceDeduction,
                    'performance_predicate' => 'Baik',
                    'performance_penalty_percent' => 0,
                    'performance_deduction' => 0,
                    'total_penalty_percent' => $totalPenaltyPercent,
                    'total_deduction' => $attendanceDeduction,
                    'bruto_after_deduction' => $brutoAfter,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'netto_amount' => $brutoAfter,
                    'source' => 'excel_import_log',
                    'created_by' => $operatorId,
                ])
            );

            $countSaved++;
        }

        return $countSaved;
    }

    /**
     * Memproses format Daftar Pembayaran Tukin
     */
    protected function processRekapPembayaranRows(array $rows, string $period, $usersByNip, $usersByName, ?int $operatorId): int
    {
        $countSaved = 0;
        $headerColMap = [];

        foreach ($rows as $row) {
            $rowValues = array_values($row);
            $rowUpper = array_map(fn($v) => strtoupper(trim((string)$v)), $rowValues);

            if (empty($headerColMap['name']) && empty($headerColMap['nip'])) {
                $tempMap = [];
                foreach ($rowUpper as $colIdx => $text) {
                    if (str_contains($text, 'NAMA PEGAWAI') || $text === 'NAMA') {
                        $tempMap['name'] = $colIdx;
                    } elseif (str_contains($text, 'NIP')) {
                        $tempMap['nip'] = $colIdx;
                    } elseif (str_contains($text, 'TUNJ KINERJA') || str_contains($text, 'TUNJANGAN') || str_contains($text, 'PLAFON')) {
                        $tempMap['base_amount'] = $colIdx;
                    } elseif ($text === 'GRADE' || $text === 'KJ' || $text === 'KELAS JABATAN' || $text === 'KELAS JAB') {
                        $tempMap['grade'] = $colIdx;
                    } elseif (str_contains($text, 'JABATAN') && !str_contains($text, 'KELAS')) {
                        $tempMap['position'] = $colIdx;
                    } elseif (str_contains($text, 'GOL')) {
                        $tempMap['golongan'] = $colIdx;
                    } elseif (str_contains($text, '% PENGURANGAN') || str_contains($text, '% POTONGAN') || str_contains($text, '% TOTAL')) {
                        $tempMap['penalty_percent'] = $colIdx;
                    } elseif (str_contains($text, 'FAKTOR PENGURANG') || $text === 'POTONGAN') {
                        $tempMap['deduction'] = $colIdx;
                    } elseif (str_contains($text, 'JUMLAH NETTO') || $text === 'NETTO') {
                        $tempMap['netto'] = $colIdx;
                    }
                }

                if (isset($tempMap['name']) || isset($tempMap['nip'])) {
                    $headerColMap = $tempMap;
                    continue;
                } else {
                    continue;
                }
            }

            if (empty($headerColMap)) continue;

            $nameRaw = isset($headerColMap['name']) ? trim((string)($rowValues[$headerColMap['name']] ?? '')) : '';
            if (empty($nameRaw) || is_numeric($nameRaw) || strlen($nameRaw) < 3) continue;

            $upperName = strtoupper($nameRaw);
            if (str_contains($upperName, 'JUMLAH') || str_contains($upperName, 'TOTAL') || str_contains($upperName, 'TERBILANG')) {
                break;
            }

            $user = $usersByName->get(strtolower(trim($nameRaw)));

            $gradeRaw = isset($headerColMap['grade']) ? trim((string)($rowValues[$headerColMap['grade']] ?? '')) : '';
            $grade = (!empty($gradeRaw) && is_numeric($gradeRaw)) ? (int)$gradeRaw : ($user?->grade ?? 6);

            $posRaw = isset($headerColMap['position']) ? trim((string)($rowValues[$headerColMap['position']] ?? '')) : '';
            $position = !empty($posRaw) ? $posRaw : ($user?->position ?? 'Pegawai');

            $golRaw = isset($headerColMap['golongan']) ? trim((string)($rowValues[$headerColMap['golongan']] ?? '')) : '';
            $golongan = !empty($golRaw) ? $golRaw : ($user?->golongan ?? 'III/a');

            $baseAmount = self::getBaseTukinByGrade($grade);
            if ($baseAmount == 0 && isset($headerColMap['base_amount'])) {
                $rawBase = preg_replace('/[^0-9]/', '', (string)($rowValues[$headerColMap['base_amount']] ?? ''));
                if (is_numeric($rawBase) && (float)$rawBase >= 1000000) {
                    $baseAmount = (float)$rawBase;
                }
            }

            $penaltyPercent = 0.0;
            if (isset($headerColMap['penalty_percent'])) {
                $rawP = str_replace(',', '.', trim((string)($rowValues[$headerColMap['penalty_percent']] ?? '0')));
                if (is_numeric($rawP)) {
                    $penaltyPercent = (float)$rawP;
                }
            }

            $deduction = round(($penaltyPercent / 100) * $baseAmount, 2);
            $brutoAfter = max(0, $baseAmount - $deduction);
            $taxPercent = TukinCalculation::getTaxRateByGolongan($golongan);
            $taxAmount = round(($taxPercent / 100) * $baseAmount, 2);
            $netto = $brutoAfter;

            $nip = $user ? $user->nip : 'PEG-' . substr(md5($nameRaw), 0, 8);

            TukinCalculation::updateOrCreate(
                [
                    'period' => $period,
                    'nip' => $nip,
                ],
                [
                    'user_id' => $user?->id,
                    'name' => $user?->name ?? $nameRaw,
                    'position' => $position,
                    'golongan' => $golongan,
                    'grade' => (string)$grade,
                    'base_amount' => $baseAmount,
                    'attendance_penalty_percent' => $penaltyPercent,
                    'attendance_deduction' => $deduction,
                    'performance_predicate' => 'Baik',
                    'performance_penalty_percent' => 0,
                    'performance_deduction' => 0,
                    'total_penalty_percent' => $penaltyPercent,
                    'total_deduction' => $deduction,
                    'bruto_after_deduction' => $brutoAfter,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'netto_amount' => $netto,
                    'source' => 'excel_import_recap',
                    'created_by' => $operatorId,
                ]
            );

            $countSaved++;
        }

        return $countSaved;
    }

    /**
     * Memproses format Rekapitulasi Matriks / Template SIPEGA
     */
    protected function processRekapitulasiMatriksRows(array $rows, string $period, $usersByNip, $usersByName, ?int $operatorId): int
    {
        $countSaved = 0;
        $headerColMap = [];

        foreach ($rows as $row) {
            $rowValues = array_values($row);
            $rowUpper = array_map(fn($v) => strtoupper(trim((string)$v)), $rowValues);

            if (empty($headerColMap['name']) && empty($headerColMap['nip'])) {
                $tempMap = [];
                foreach ($rowUpper as $colIdx => $text) {
                    if (str_contains($text, 'NIP')) { $tempMap['nip'] = $colIdx; }
                    if (str_contains($text, 'NAMA')) { $tempMap['name'] = $colIdx; }
                    if (str_contains($text, 'JABATAN') && !str_contains($text, 'KELAS')) { $tempMap['position'] = $colIdx; }
                    if ($text === 'GRADE' || $text === 'KJ' || $text === 'KELAS') { $tempMap['grade'] = $colIdx; }
                    if (str_contains($text, 'GOL')) { $tempMap['golongan'] = $colIdx; }
                    if ($text === 'TL1' || $text === 'TL 1') { $tempMap['tl1'] = $colIdx; }
                    if ($text === 'TL2' || $text === 'TL 2') { $tempMap['tl2'] = $colIdx; }
                    if ($text === 'TL3' || $text === 'TL 3') { $tempMap['tl3'] = $colIdx; }
                    if ($text === 'TL4' || $text === 'TL 4') { $tempMap['tl4'] = $colIdx; }
                    if ($text === 'TL5' || $text === 'TL 5') { $tempMap['tl5'] = $colIdx; }
                    if ($text === 'PSW1' || $text === 'PSW 1') { $tempMap['psw1'] = $colIdx; }
                    if ($text === 'PSW2' || $text === 'PSW 2') { $tempMap['psw2'] = $colIdx; }
                    if ($text === 'PSW3' || $text === 'PSW 3') { $tempMap['psw3'] = $colIdx; }
                    if ($text === 'PSW4' || $text === 'PSW 4') { $tempMap['psw4'] = $colIdx; }
                    if ($text === 'PSW5' || $text === 'PSW 5') { $tempMap['psw5'] = $colIdx; }
                    if ($text === 'TKD') { $tempMap['tkd'] = $colIdx; }
                    if ($text === 'TKP') { $tempMap['tkp'] = $colIdx; }
                    if ($text === 'TK' || $text === 'ALPA') { $tempMap['alpa'] = $colIdx; }
                    if ($text === 'DL' || str_contains($text, 'DINAS LUAR')) { $tempMap['dl'] = $colIdx; }
                    if ($text === 'C' || str_contains($text, 'CUTI')) { $tempMap['cuti'] = $colIdx; }
                    if ($text === 'S' || str_contains($text, 'SAKIT')) { $tempMap['sakit'] = $colIdx; }
                    if (str_contains($text, 'TOTAL NILAI PENGURANG') || str_contains($text, '% TOTAL') || str_contains($text, 'TOTAL PENGURANG') || str_contains($text, 'TOTAL % PENGURANG')) {
                        $tempMap['total_penalty'] = $colIdx;
                    }
                }

                if (isset($tempMap['name']) || isset($tempMap['nip'])) {
                    $headerColMap = $tempMap;
                    continue;
                } else {
                    continue;
                }
            }

            if (empty($headerColMap)) continue;

            $nipRaw = isset($headerColMap['nip']) ? trim((string)($rowValues[$headerColMap['nip']] ?? '')) : '';
            $nameRaw = isset($headerColMap['name']) ? trim((string)($rowValues[$headerColMap['name']] ?? '')) : '';
            $cleanNip = preg_replace('/[^0-9]/', '', $nipRaw);

            if (empty($nameRaw) && empty($cleanNip)) continue;
            if (str_contains(strtoupper($nameRaw), 'JUMLAH') || str_contains(strtoupper($nameRaw), 'TOTAL')) break;

            $user = !empty($cleanNip) ? $usersByNip->get($cleanNip) : null;
            if (!$user && !empty($nameRaw)) {
                $user = $usersByName->get(strtolower(trim($nameRaw)));
            }

            $gradeVal = isset($headerColMap['grade']) ? trim((string)($rowValues[$headerColMap['grade']] ?? '')) : '';
            $grade = !empty($gradeVal) && is_numeric($gradeVal) ? (int)$gradeVal : ($user?->grade ?? 6);

            $golVal = isset($headerColMap['golongan']) ? trim((string)($rowValues[$headerColMap['golongan']] ?? '')) : '';
            $golongan = !empty($golVal) ? $golVal : ($user?->golongan ?? 'III/a');

            $posVal = isset($headerColMap['position']) ? trim((string)($rowValues[$headerColMap['position']] ?? '')) : '';
            $position = !empty($posVal) ? $posVal : ($user?->position ?? 'Pegawai');

            $baseAmount = self::getBaseTukinByGrade($grade);

            $tl1 = isset($headerColMap['tl1']) ? (int)($rowValues[$headerColMap['tl1']] ?? 0) : 0;
            $tl2 = isset($headerColMap['tl2']) ? (int)($rowValues[$headerColMap['tl2']] ?? 0) : 0;
            $tl3 = isset($headerColMap['tl3']) ? (int)($rowValues[$headerColMap['tl3']] ?? 0) : 0;
            $tl4 = isset($headerColMap['tl4']) ? (int)($rowValues[$headerColMap['tl4']] ?? 0) : 0;
            $tl5 = isset($headerColMap['tl5']) ? (int)($rowValues[$headerColMap['tl5']] ?? 0) : 0;

            $psw1 = isset($headerColMap['psw1']) ? (int)($rowValues[$headerColMap['psw1']] ?? 0) : 0;
            $psw2 = isset($headerColMap['psw2']) ? (int)($rowValues[$headerColMap['psw2']] ?? 0) : 0;
            $psw3 = isset($headerColMap['psw3']) ? (int)($rowValues[$headerColMap['psw3']] ?? 0) : 0;
            $psw4 = isset($headerColMap['psw4']) ? (int)($rowValues[$headerColMap['psw4']] ?? 0) : 0;
            $psw5 = isset($headerColMap['psw5']) ? (int)($rowValues[$headerColMap['psw5']] ?? 0) : 0;

            $tkd = isset($headerColMap['tkd']) ? (int)($rowValues[$headerColMap['tkd']] ?? 0) : 0;
            $tkp = isset($headerColMap['tkp']) ? (int)($rowValues[$headerColMap['tkp']] ?? 0) : 0;
            $alpa = isset($headerColMap['alpa']) ? (int)($rowValues[$headerColMap['alpa']] ?? 0) : 0;
            $cuti = isset($headerColMap['cuti']) ? (int)($rowValues[$headerColMap['cuti']] ?? 0) : 0;
            $sakit = isset($headerColMap['sakit']) ? (int)($rowValues[$headerColMap['sakit']] ?? 0) : 0;
            $dl = isset($headerColMap['dl']) ? (int)($rowValues[$headerColMap['dl']] ?? 0) : 0;

            $explicitPenalty = null;
            if (isset($headerColMap['total_penalty'])) {
                $rawP = str_replace(',', '.', trim((string)($rowValues[$headerColMap['total_penalty']] ?? '')));
                if (is_numeric($rawP)) {
                    $explicitPenalty = (float)$rawP;
                }
            }

            if ($explicitPenalty !== null) {
                $totalPenaltyPercent = $explicitPenalty;
            } else {
                $totalPenaltyPercent = ($tl1 * 0.25) + ($tl2 * 0.50) + ($tl3 * 0.75) + ($tl4 * 1.00) + ($tl5 * 1.25)
                                     + ($psw1 * 0.25) + ($psw2 * 0.50) + ($psw3 * 0.75) + ($psw4 * 1.00) + ($psw5 * 1.25)
                                     + ($tkd * 1.25) + ($tkp * 1.25) + ($alpa * 2.5);
            }

            $deduction = round(($totalPenaltyPercent / 100) * $baseAmount, 2);
            $brutoAfter = max(0, $baseAmount - $deduction);
            $taxPercent = TukinCalculation::getTaxRateByGolongan($golongan);
            $taxAmount = round(($taxPercent / 100) * $baseAmount, 2);
            $netto = $brutoAfter;

            TukinCalculation::updateOrCreate(
                [
                    'period' => $period,
                    'nip' => $user?->nip ?? (!empty($cleanNip) ? $cleanNip : 'PEG-' . substr(md5($nameRaw), 0, 8)),
                ],
                [
                    'user_id' => $user?->id,
                    'name' => $user?->name ?? $nameRaw,
                    'position' => $position,
                    'golongan' => $golongan,
                    'grade' => (string)$grade,
                    'base_amount' => $baseAmount,
                    'hadir_count' => 0,
                    'dl_count' => $dl,
                    'cuti_count' => $cuti,
                    'sakit_count' => $sakit,
                    'alpa_count' => $alpa,
                    'tl1_count' => $tl1,
                    'tl2_count' => $tl2,
                    'tl3_count' => $tl3,
                    'tl4_count' => $tl4,
                    'tl5_count' => $tl5,
                    'psw1_count' => $psw1,
                    'psw2_count' => $psw2,
                    'psw3_count' => $psw3,
                    'psw4_count' => $psw4,
                    'psw5_count' => $psw5,
                    'tkd_count' => $tkd,
                    'tkp_count' => $tkp,
                    'tbtk_count' => 0,
                    'attendance_penalty_percent' => $totalPenaltyPercent,
                    'attendance_deduction' => $deduction,
                    'performance_predicate' => 'Baik',
                    'performance_penalty_percent' => 0,
                    'performance_deduction' => 0,
                    'total_penalty_percent' => $totalPenaltyPercent,
                    'total_deduction' => $deduction,
                    'bruto_after_deduction' => $brutoAfter,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'netto_amount' => $netto,
                    'source' => 'excel_import_matrix',
                    'created_by' => $operatorId,
                ]
            );

            $countSaved++;
        }

        return $countSaved;
    }

    /**
     * Generate Template Excel siap pakai untuk kepegawaian
     */
    public function generateTemplateSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Rekap Tukin');

        $sheet->setCellValue('A1', 'TEMPLATE REKAPITULASI PENGHITUNGAN TUNJANGAN KINERJA PEGAWAI');
        $sheet->setCellValue('A2', 'BERDASARKAN PERMENDIKDASMEN NO. 14 TAHUN 2025');
        $sheet->mergeCells('A1:U1');
        $sheet->mergeCells('A2:U2');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'A4' => 'NO',
            'B4' => 'NIP',
            'C4' => 'NAMA PEGAWAI',
            'D4' => 'JABATAN',
            'E4' => 'GOL.',
            'F4' => 'GRADE',
            'G4' => 'TL1',
            'H4' => 'TL2',
            'I4' => 'TL3',
            'J4' => 'TL4',
            'K4' => 'TL5',
            'L4' => 'PSW1',
            'M4' => 'PSW2',
            'N4' => 'PSW3',
            'O4' => 'PSW4',
            'P4' => 'PSW5',
            'Q4' => 'TKD',
            'R4' => 'TKP',
            'S4' => 'ALPA',
            'T4' => 'TOTAL % PENGURANG',
            'U4' => 'KETERANGAN',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A4:U4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(30);

        $users = User::realPegawai()->with('jobClass')->orderBy('name', 'asc')->get();
        $rowNum = 5;

        foreach ($users as $index => $u) {
            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValueExplicit('B' . $rowNum, $u->nip ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowNum, $u->name);
            $sheet->setCellValue('D' . $rowNum, $u->position ?? '-');
            $sheet->setCellValue('E' . $rowNum, $u->golongan ?? '-');
            $sheet->setCellValue('F' . $rowNum, $u->grade ?? ($u->jobClass->class_name ?? 6));
            
            for ($col = 'G'; $col <= 'S'; $col++) {
                $sheet->setCellValue($col . $rowNum, 0);
            }

            $sheet->setCellValue('T' . $rowNum, "=(G{$rowNum}*0.25)+(H{$rowNum}*0.5)+(I{$rowNum}*0.75)+(J{$rowNum}*1)+(K{$rowNum}*1.25)+(L{$rowNum}*0.25)+(M{$rowNum}*0.5)+(N{$rowNum}*0.75)+(O{$rowNum}*1)+(P{$rowNum}*1.25)+(Q{$rowNum}*1.25)+(R{$rowNum}*1.25)+(S{$rowNum}*2.5)");
            $sheet->setCellValue('U' . $rowNum, '-');

            $rowNum++;
        }

        $lastRow = max(5, $rowNum - 1);
        $sheet->getStyle("A5:U{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A5:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B5:B{$lastRow}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->getStyle("B5:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E5:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G5:T{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        foreach (range('A', 'U') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    /**
     * Generate Spreadsheet Hasil Perhitungan Tukin untuk Export Excel
     */
    public function generateCalculationExportSpreadsheet(string $period): Spreadsheet
    {
        $calculations = TukinCalculation::where('period', $period)
            ->orderByRaw('CAST(grade AS UNSIGNED) DESC')
            ->orderBy('name', 'asc')
            ->get();

        $monthName = Carbon::parse($period . '-01')->translatedFormat('F Y');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Pembayaran Tukin');

        $sheet->setCellValue('A1', 'DAFTAR PEMBAYARAN TUNJANGAN KINERJA PEGAWAI BPMP PROVINSI KALIMANTAN TIMUR');
        $sheet->setCellValue('A2', 'UNTUK TUNJANGAN BULAN ' . strtoupper($monthName));
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $cols = [
            'A4' => 'NO',
            'B4' => 'Grade',
            'C4' => 'Nama Pegawai',
            'D4' => 'Jabatan',
            'E4' => 'GOL.',
            'F4' => 'Tunj Kinerja Per Kelas Jab',
            'G4' => '% Pengurangan Kehadiran',
            'H4' => 'Rupiah',
            'I4' => 'Faktor Pengurang',
            'J4' => 'Jumlah Netto',
        ];

        foreach ($cols as $c => $val) {
            $sheet->setCellValue($c, $val);
        }

        $sheet->getStyle('A4:J4')->getFont()->setBold(true);
        $sheet->getStyle('A4:J4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1D5DB');
        $sheet->getStyle('A4:J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A4:J4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $rowNum = 5;
        $totalBase = 0;
        $totalDeduction = 0;
        $totalNetto = 0;

        foreach ($calculations as $idx => $c) {
            $sheet->setCellValue('A' . $rowNum, $idx + 1);
            $sheet->setCellValue('B' . $rowNum, $c->grade);
            $sheet->setCellValue('C' . $rowNum, $c->name);
            $sheet->setCellValue('D' . $rowNum, $c->position ?? '-');
            $sheet->setCellValue('E' . $rowNum, $c->golongan ?? '-');
            $sheet->setCellValue('F' . $rowNum, $c->base_amount);
            $sheet->setCellValue('G' . $rowNum, $c->total_penalty_percent > 0 ? $c->total_penalty_percent : 0);
            $sheet->setCellValue('H' . $rowNum, $c->bruto_after_deduction);
            $sheet->setCellValue('I' . $rowNum, $c->total_deduction);
            $sheet->setCellValue('J' . $rowNum, $c->netto_amount);

            $totalBase += $c->base_amount;
            $totalDeduction += $c->total_deduction;
            $totalNetto += $c->netto_amount;

            $rowNum++;
        }

        $lastRow = max(5, $rowNum - 1);
        $sheet->getStyle("A5:J{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A5:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E5:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G5:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F5:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("H5:J{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');

        $sheet->setCellValue('A' . $rowNum, 'TOTAL');
        $sheet->mergeCells("A{$rowNum}:E{$rowNum}");
        $sheet->setCellValue('F' . $rowNum, $totalBase);
        $sheet->setCellValue('G' . $rowNum, '-');
        $sheet->setCellValue('H' . $rowNum, $totalNetto);
        $sheet->setCellValue('I' . $rowNum, $totalDeduction);
        $sheet->setCellValue('J' . $rowNum, $totalNetto);

        $sheet->getStyle("A{$rowNum}:J{$rowNum}")->getFont()->setBold(true);
        $sheet->getStyle("A{$rowNum}:J{$rowNum}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
        $sheet->getStyle("A{$rowNum}:J{$rowNum}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("F{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("H{$rowNum}:J{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');

        $signRow = $rowNum + 3;
        $sheet->setCellValue("G{$signRow}", 'Samarinda, ' . Carbon::now()->translatedFormat('d F Y'));
        $sheet->setCellValue("G" . ($signRow + 1), 'Kuasa Pengguna Anggaran');
        $sheet->setCellValue("G" . ($signRow + 2), 'BPMP Provinsi Kalimantan Timur');
        $sheet->setCellValue("G" . ($signRow + 6), 'Dr. Jarwoko, M.Pd.');
        $sheet->setCellValue("G" . ($signRow + 7), 'NIP 197002011997021001');

        $sheet->setCellValue("B" . ($signRow + 1), 'Bendahara Pengeluaran');
        $sheet->setCellValue("B" . ($signRow + 2), 'BPMP Provinsi Kalimantan Timur');
        $sheet->setCellValue("B" . ($signRow + 6), 'Sulistyo Rahayu, S.Pd.');
        $sheet->setCellValue("B" . ($signRow + 7), 'NIP 197609292003122001');

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
