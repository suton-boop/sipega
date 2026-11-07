<?php

namespace App\Services;

use App\Models\User;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Setting;
use Carbon\Carbon;

class LeaveService
{
    /**
     * Verifikasi otomatis kepatuhan regulasi cuti ASN (PNS & PPPK)
     */
    public function verifyLeaveEligibility(User $user, array $params, $uploadedFile = null): array
    {
        Carbon::setLocale('id');

        $statusKepegawaian = strtoupper($params['employee_status'] ?? $user->effective_status_kepegawaian);
        $jabatan = $params['employee_position'] ?? $user->position ?? '-';
        $leaveType = $params['leave_type'] ?? 'cuti_tahunan';
        $duration = (int) ($params['duration_amount'] ?? 1);
        $durationUnit = $params['duration_unit'] ?? 'hari';
        $startDate = !empty($params['start_date']) ? Carbon::parse($params['start_date']) : now();
        $reason = $params['reason'] ?? '';
        $attachmentType = $params['attachment_type'] ?? '';
        $hasAttachment = !empty($uploadedFile) || !empty($params['has_attachment']) || !empty($params['attachment_path']);

        $masaKerjaYears = $user->getMasaKerjaYears($startDate);
        $masaKerjaText = $user->getMasaKerjaText($startDate);
        if (!empty($params['work_duration'])) {
            $masaKerjaText = $params['work_duration'];
        }

        $currentYear = (int) $startDate->year;
        $balance = LeaveBalance::getOrCreateForUser($user, $currentYear);
        
        $totalCutiTahunanTersedia = $balance->remaining_n2 + $balance->remaining_n1 + $balance->remaining_quota;

        $isGuru = str_contains(strtolower($jabatan), 'guru');

        $status = 'Memenuhi Syarat';
        $errors = [];
        $warnings = [];
        $requiredDocs = [];

        // 1. ATURAN HUKUM PPPK (PerBKN No. 7 Tahun 2022)
        if ($statusKepegawaian === 'PPPK') {
            if ($leaveType === 'cuti_besar') {
                $status = 'Ditolak';
                $errors[] = 'PPPK TIDAK BERHAK atas Cuti Besar berdasarkan ketentuan Peraturan BKN No. 7 Tahun 2022 Pasal 3. Cuti Besar hanya diperuntukkan bagi Pegawai Negeri Sipil (PNS).';
            }

            if ($leaveType === 'cuti_cltn') {
                $status = 'Ditolak';
                $errors[] = 'PPPK TIDAK BERHAK atas Cuti di Luar Tanggungan Negara (CLTN) berdasarkan Peraturan BKN No. 7 Tahun 2022 Pasal 4 jo. UU No. 20 Tahun 2023.';
            }

            if ($leaveType === 'cuti_tahunan') {
                if ($masaKerjaYears < 1.0) {
                    if ($duration > 6) {
                        $status = 'Ditolak';
                        $errors[] = 'PPPK dengan masa kerja kurang dari 1 tahun hanya dapat diberikan cuti tahunan maksimal 6 hari kerja untuk alasan mendesak/khusus sesuai PerBKN No. 7 Tahun 2022.';
                    } else {
                        $warnings[] = 'PPPK dengan masa kerja < 1 tahun mengajukan Cuti Tahunan mendesak (maksimal 6 hari kerja disetujui PPK).';
                    }
                }
            }

            if ($leaveType === 'cuti_sakit') {
                $durasiHari = $durationUnit === 'bulan' ? $duration * 30 : $duration;
                if ($durasiHari > 30) {
                    $status = 'Ditolak';
                    $errors[] = 'Berdasarkan PerBKN No. 7 Tahun 2022, hak Cuti Sakit bagi PPPK diberikan paling lama 30 (tiga puluh) hari kerja dalam 1 (satu) tahun masa perjanjian kerja.';
                }
            }
        }

        // 2. ATURAN HUKUM PNS (PP No. 11/2017 jo. PP No. 17/2020 & PerBKN No. 24/2017 jo. PerBKN No. 7/2021)
        if ($statusKepegawaian === 'PNS') {
            if ($leaveType === 'cuti_besar') {
                if ($masaKerjaYears < 5.0) {
                    $status = 'Ditolak';
                    $errors[] = 'Cuti Besar mensyaratkan masa kerja sekurang-kurangnya 5 (lima) tahun secara terus menerus (Pasal 315 PP No. 11/2017). Masa kerja saat ini baru ' . $masaKerjaText . '.';
                }
                $durasiBulan = $durationUnit === 'hari' ? ceil($duration / 30) : $duration;
                if ($durasiBulan > 3) {
                    $status = 'Ditolak';
                    $errors[] = 'Cuti Besar diberikan paling lama 3 (tiga) bulan sesuai ketentuan Pasal 316 PP No. 11/2017.';
                }
            }

            if ($leaveType === 'cuti_cltn') {
                if ($masaKerjaYears < 3.0) {
                    $status = 'Ditolak';
                    $errors[] = 'Cuti di Luar Tanggungan Negara (CLTN) hanya dapat diberikan kepada PNS yang telah bekerja sekurang-kurangnya 3 (tiga) tahun secara terus menerus (Pasal 334 PP No. 11/2017).';
                }
            }

            if ($leaveType === 'cuti_tahunan' && $masaKerjaYears < 1.0) {
                $status = 'Ditolak';
                $errors[] = 'PNS yang belum memiliki masa kerja sekurang-kurangnya 1 (satu) tahun secara terus menerus belum memiliki hak Cuti Tahunan (Pasal 310 PP 11/2017).';
            }
        }

        // 3. ATURAN JABATAN GURU
        if ($isGuru && $leaveType === 'cuti_tahunan') {
            if (!stripos($reason, 'mendesak') && !stripos($reason, 'dinas') && !stripos($reason, 'khusus')) {
                $warnings[] = 'Sesuai Perka BKN No. 24/2017 Romawi III.A.13, Pegawai dengan jabatan Guru yang mendapat libur semester sekolah disamakan dengan pegawai yang telah mengambil Cuti Tahunan, kecuali atas persetujuan PPK untuk tugas kedinasan mendesak.';
            }
        }

        // 4. VALIDASI KUOTA CUTI TAHUNAN
        if ($leaveType === 'cuti_tahunan') {
            if ($duration > $totalCutiTahunanTersedia) {
                $status = 'Ditolak';
                $errors[] = "Jumlah pengajuan cuti tahunan ({$duration} hari) melebihi sisa kuota yang tersedia ({$totalCutiTahunanTersedia} hari kerja: N={$balance->remaining_quota}, N-1={$balance->remaining_n1}, N-2={$balance->remaining_n2}).";
            }
        }

        // 5. VALIDASI KELENGKAPAN DOKUMEN PENDUKUNG WAJIB
        if ($leaveType === 'cuti_sakit') {
            $isKeguguran = stripos($reason, 'keguguran') !== false;
            if ($isKeguguran) {
                $requiredDocs[] = 'Surat Keterangan Dokter Spesialis Kandungan / Bidan (Maksimal 1.5 bulan / 45 hari)';
                if ($duration > 45 && $durationUnit === 'hari') {
                    $status = 'Ditolak';
                    $errors[] = 'Cuti sakit karena gugur kandungan diberikan paling lama 1,5 (satu setengah) bulan (Pasal 322 PP No. 11/2017).';
                }
            } elseif ($duration > 14) {
                $requiredDocs[] = 'Surat Keterangan Tim Penguji Kesehatan / Dokter Rumah Sakit Pemerintah (Wajib untuk Cuti Sakit > 14 hari)';
            } else {
                $requiredDocs[] = 'Surat Keterangan Sakit dari Dokter Umum / Klinik / Puskesmas';
            }

            if (!$hasAttachment && $status !== 'Ditolak') {
                $status = 'Perlu Dokumen Tambahan';
            }
        }

        if ($leaveType === 'cuti_melahirkan') {
            $requiredDocs[] = 'Surat Keterangan Dokter / Bidan / Hari Perkiraan Lahir (HPL)';
            if ($duration > 3 && $durationUnit === 'bulan') {
                $status = 'Ditolak';
                $errors[] = 'Cuti Melahirkan diberikan untuk kelahiran anak pertama s.d ketiga paling lama 3 (tiga) bulan.';
            }
            if (!$hasAttachment && $status !== 'Ditolak') {
                $status = 'Perlu Dokumen Tambahan';
            }
        }

        if ($leaveType === 'cuti_alasan_penting') {
            $requiredDocs[] = 'Surat Kematian / Surat Keterangan Rawat Inap Keluarga Inti / Akta Nikah / Bukti Musibah Bencana';
            if (!$hasAttachment && $status !== 'Ditolak') {
                $status = 'Perlu Dokumen Tambahan';
            }
        }

        if ($leaveType === 'cuti_cltn') {
            $requiredDocs[] = 'Surat Keterangan Pendukung Alasan Pribadi & Surat Pernyataan Bersedia Melepas Jabatan';
            if (!$hasAttachment && $status !== 'Ditolak') {
                $status = 'Perlu Dokumen Tambahan';
            }
        }

        // 6. AUDIT KUOTA BERSAMAAN (CONCURRENT LEAVE CAPACITY: 5% s.d 10%)
        // Dua Skenario: Kuota Lembaga (Satker) vs Kuota Gugus Mutu (Unit)
        $endDate = !empty($params['end_date']) ? Carbon::parse($params['end_date']) : $startDate;
        $excludeId = isset($params['exclude_leave_id']) ? (int) $params['exclude_leave_id'] : null;
        $concurrentAudit = $this->checkConcurrentLeaveCapacity($user, $startDate, $endDate, $excludeId);

        if ($concurrentAudit['active_scenario_exceeded']) {
            $activeName = $concurrentAudit['active_scenario'] === 'gugus_mutu' ? 'Gugus Mutu' : 'Lembaga (Satker)';
            $warnMsg = "PERHATIAN KUOTA BERSAMAAN: Ambang batas kuota cuti bersamaan {$concurrentAudit['percentage']}% berdasar {$activeName} terlampaui pada rentang tanggal tersebut. " . $concurrentAudit['active_warning_message'];
            $warnings[] = $warnMsg;

            if ($concurrentAudit['is_strict']) {
                $status = 'Ditolak';
                $errors[] = "Pengajuan otomatis ditolak: " . $warnMsg;
            } elseif ($status !== 'Ditolak') {
                $warnings[] = "Rekomendasi Asisten AI untuk Atasan Langsung/PPK: Pertimbangkan penetapan opsi 'PERUBAHAN' (pergeseran tanggal) atau 'DITANGGUHKAN' guna menjaga kelancaran tugas kedinasan.";
            }
        }

        // 7. RINGKASAN VALIDASI
        $summaryParts = [];
        $summaryParts[] = "Pegawai {$user->name} (NIP. {$user->nip}) berstatus {$statusKepegawaian} dengan jabatan {$jabatan}.";
        $summaryParts[] = "Masa kerja terverifikasi: {$masaKerjaText} (" . number_format($masaKerjaYears, 1) . " tahun).";
        if ($leaveType === 'cuti_tahunan') {
            $summaryParts[] = "Sisa Kuota Cuti Tahunan: Tahun N (" . ($currentYear) . ") = {$balance->remaining_quota} hari, N-1 (" . ($currentYear-1) . ") = {$balance->remaining_n1} hari, N-2 (" . ($currentYear-2) . ") = {$balance->remaining_n2} hari (Total Tersedia: {$totalCutiTahunanTersedia} hari kerja).";
        }
        $summaryParts[] = "Kapasitas Bersamaan ({$concurrentAudit['percentage']}%): Kuota Lembaga = {$concurrentAudit['institution']['current_count']}/{$concurrentAudit['institution']['max_quota']} pegawai; Kuota Gugus Mutu = {$concurrentAudit['gugus_mutu']['current_count']}/{$concurrentAudit['gugus_mutu']['max_quota']} pegawai.";

        $summary = implode(' ', $summaryParts);

        // 8. ALUR PERSETUJUAN
        $approvalFlow = [
            'step_1' => [
                'role' => 'Atasan Langsung',
                'description' => 'Pemeriksaan kepatuhan tugas dan pertimbangan pemberian cuti (Disetujui / Perubahan / Ditangguhkan / Tidak Disetujui)',
            ],
            'step_2' => [
                'role' => 'Tim Kepegawaian Satker (Subbagian Umum BPMP Kalimantan Timur)',
                'description' => 'Verifikasi keabsahan dokumen pendukung fisik/digital dan rekonsiliasi kuota sisa cuti N, N-1, N-2',
            ],
            'step_3' => [
                'role' => 'Pejabat Pembina Kepegawaian (PPK / Kepala BPMP Kalimantan Timur)',
                'description' => 'Penetapan Surat Keputusan / Persetujuan resmi Permintaan dan Pemberian Cuti ASN',
            ],
        ];

        // 9. DRAF SURAT PERMINTAAN CUTI RESMI (PerBKN No. 24 Tahun 2017)
        $draftLetter = $this->generateDraftLetter($user, $params, $masaKerjaText, $balance, $currentYear);

        return [
            'status' => $status,
            'summary' => $summary,
            'errors' => $errors,
            'warnings' => $warnings,
            'required_docs' => $requiredDocs,
            'has_attachment' => $hasAttachment,
            'concurrent_audit' => $concurrentAudit,
            'balance' => [
                'year_n' => $currentYear,
                'quota_n' => $balance->remaining_quota,
                'year_n1' => $currentYear - 1,
                'quota_n1' => $balance->remaining_n1,
                'year_n2' => $currentYear - 2,
                'quota_n2' => $balance->remaining_n2,
                'total_available' => $totalCutiTahunanTersedia,
            ],
            'masa_kerja_text' => $masaKerjaText,
            'approval_flow' => '(1) Atasan Langsung -> (2) Tim Kepegawaian Satker -> (3) Pejabat Pembina Kepegawaian (PPK).',
            'approval_flow_detail' => $approvalFlow,
            'draft_letter' => $draftLetter,
            'json_outcome' => [
                'Status Pengajuan' => $status,
                'Ringkasan Validasi Masa Kerja & Kuota' => $summary,
                'Audit Kuota Bersamaan (5%-10%)' => [
                    'Skenario Aktif' => $concurrentAudit['active_scenario'] === 'gugus_mutu' ? 'Berdasarkan Gugus Mutu' : 'Berdasarkan Kuota Lembaga (Satker)',
                    'Skenario 1 - Kuota Lembaga' => "{$concurrentAudit['institution']['current_count']} dari maks {$concurrentAudit['institution']['max_quota']} pegawai ({$concurrentAudit['percentage']}%)",
                    'Skenario 2 - Kuota Gugus Mutu' => "{$concurrentAudit['gugus_mutu']['current_count']} dari maks {$concurrentAudit['gugus_mutu']['max_quota']} pegawai di {$concurrentAudit['gugus_mutu']['name']}",
                    'Ambang Batas Terlampaui' => $concurrentAudit['active_scenario_exceeded'] ? 'Ya (Rekomendasi Perubahan / Penangguhan)' : 'Tidak (Aman)'
                ],
                'Alur Rekomendasi Persetujuan' => '(1) Atasan Langsung -> (2) Tim Kepegawaian Satker -> (3) Pejabat Pembina Kepegawaian (PPK).',
                'Catatan Regulasi' => array_merge($errors, $warnings),
                'Dokumen Wajib' => $requiredDocs,
            ]
        ];
    }

    /**
     * Audit Kuota Cuti Bersamaan (Concurrent Leave Capacity - 5% s.d 10%)
     * Mendukung 2 Skenario:
     * 1. Berdasarkan Kuota Lembaga (Satker BPMP Kaltim)
     * 2. Berdasarkan Kuota Gugus Mutu (Unit Kerja Pemohon)
     */
    public function checkConcurrentLeaveCapacity(User $user, Carbon $startDate, Carbon $endDate, ?int $excludeLeaveId = null): array
    {
        $percentage = (float) Setting::get('leave_concurrent_percentage', 10);
        $activeScenario = Setting::get('leave_concurrent_scenario', 'lembaga'); // 'lembaga' atau 'gugus_mutu'
        $isStrict = (bool) Setting::get('leave_concurrent_strict', 0);

        // Ambil semua permohonan cuti aktif yang bertabrakan rentang tanggalnya
        $overlappingQuery = LeaveRequest::whereNotIn('status', ['Ditolak', 'Tidak Disetujui'])
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('end_date', '>=', $startDate->toDateString())
            ->with('user');

        if ($excludeLeaveId) {
            $overlappingQuery->where('id', '!=', $excludeLeaveId);
        }

        // Jangan hitung pemohon sendiri jika mengecek ulang
        $overlappingQuery->where('user_id', '!=', $user->id);

        $overlappingLeaves = $overlappingQuery->get();

        // -------------------------------------------------------------
        // SKENARIO 1: BERDASARKAN KUOTA LEMBAGA (SATKER KESELURUHAN)
        // -------------------------------------------------------------
        $totalInstitutionEmployees = User::where('is_active', 1)->count() ?: 1;
        // Kuota batas orang (misal 20 pegawai x 10% = 2 orang, atau 20 x 5% = 1 orang)
        $maxInstitutionQuota = max(1, (int) ceil($totalInstitutionEmployees * ($percentage / 100)));
        $currentInstitutionCount = $overlappingLeaves->count();
        // Bila pemohon ini mengajukan cuti, apakah akan melebihi kuota?
        $institutionExceeded = ($currentInstitutionCount + 1) > $maxInstitutionQuota;

        $institutionPeers = $overlappingLeaves->map(fn($l) => [
            'name' => $l->employee_name,
            'position' => $l->employee_position,
            'gugus_mutu' => $l->user?->gugus_mutu ?? '-',
            'period' => $l->start_date->translatedFormat('d M') . ' s.d ' . $l->end_date->translatedFormat('d M Y'),
            'type' => $l->leave_type_label,
            'status' => $l->status,
        ])->values()->all();

        // -------------------------------------------------------------
        // SKENARIO 2: BERDASARKAN KUOTA GUGUS MUTU (UNIT KERJA PEGAWAI)
        // -------------------------------------------------------------
        $userGm = $user->gugus_mutu ?: 'Non-Gugus / Struktural';
        $gmQuery = User::where('is_active', 1);
        if (!empty($user->gugus_mutu)) {
            $gmQuery->where('gugus_mutu', $user->gugus_mutu);
        } else {
            $gmQuery->where(function($q) {
                $q->whereNull('gugus_mutu')->orWhere('gugus_mutu', '');
            });
        }
        $totalGmEmployees = $gmQuery->count() ?: 1;

        // Kuota batas orang di Gugus Mutu (minimal 1 agar tetap bisa bergantian)
        $maxGmQuota = max(1, (int) ceil($totalGmEmployees * ($percentage / 100)));

        $gmOverlappingLeaves = $overlappingLeaves->filter(function($l) use ($user) {
            $peerGm = $l->user?->gugus_mutu ?? '';
            return $peerGm === ($user->gugus_mutu ?? '');
        });
        $currentGmCount = $gmOverlappingLeaves->count();
        $gmExceeded = ($currentGmCount + 1) > $maxGmQuota;

        $gmPeers = $gmOverlappingLeaves->map(fn($l) => [
            'name' => $l->employee_name,
            'position' => $l->employee_position,
            'period' => $l->start_date->translatedFormat('d M') . ' s.d ' . $l->end_date->translatedFormat('d M Y'),
            'type' => $l->leave_type_label,
            'status' => $l->status,
        ])->values()->all();

        // -------------------------------------------------------------
        // EVALUASI SKENARIO AKTIF
        // -------------------------------------------------------------
        $activeScenarioExceeded = false;
        $activeWarningMsg = '';

        if ($activeScenario === 'gugus_mutu') {
            $activeScenarioExceeded = $gmExceeded;
            if ($gmExceeded) {
                $peerNames = $gmOverlappingLeaves->pluck('employee_name')->join(', ');
                $activeWarningMsg = "Pada rentang tanggal ini di {$userGm}, sudah ada {$currentGmCount} pegawai yang mengajukan/menjalankan cuti ({$peerNames}). Batas kuota gugus mutu adalah {$maxGmQuota} pegawai ({$percentage}% dari {$totalGmEmployees} pegawai).";
            }
        } else {
            // Default Lembaga
            $activeScenarioExceeded = $institutionExceeded;
            if ($institutionExceeded) {
                $peerNames = $overlappingLeaves->pluck('employee_name')->join(', ');
                $activeWarningMsg = "Pada rentang tanggal ini di BPMP Kaltim, sudah ada {$currentInstitutionCount} pegawai yang mengajukan/menjalankan cuti ({$peerNames}). Batas kuota lembaga adalah {$maxInstitutionQuota} pegawai ({$percentage}% dari {$totalInstitutionEmployees} pegawai).";
            }
        }

        return [
            'percentage' => $percentage,
            'active_scenario' => $activeScenario,
            'is_strict' => $isStrict,
            'active_scenario_exceeded' => $activeScenarioExceeded,
            'active_warning_message' => $activeWarningMsg,
            'institution' => [
                'total_employees' => $totalInstitutionEmployees,
                'max_quota' => $maxInstitutionQuota,
                'current_count' => $currentInstitutionCount,
                'remaining_slots' => max(0, $maxInstitutionQuota - $currentInstitutionCount),
                'is_exceeded' => $institutionExceeded,
                'peers' => $institutionPeers,
            ],
            'gugus_mutu' => [
                'name' => $userGm,
                'total_employees' => $totalGmEmployees,
                'max_quota' => $maxGmQuota,
                'current_count' => $currentGmCount,
                'remaining_slots' => max(0, $maxGmQuota - $currentGmCount),
                'is_exceeded' => $gmExceeded,
                'peers' => $gmPeers,
            ],
        ];
    }

    /**
     * Draf Surat Permintaan Cuti sesuai Lampiran PerBKN No. 24 Tahun 2017
     */
    public function generateDraftLetter(User $user, array $params, string $masaKerjaText, LeaveBalance $balance, int $currentYear): string
    {
        $startDate = !empty($params['start_date']) ? Carbon::parse($params['start_date']) : now();
        $endDate = !empty($params['end_date']) ? Carbon::parse($params['end_date']) : $startDate;
        $tglPermohonan = !empty($params['request_date']) ? Carbon::parse($params['request_date'])->translatedFormat('d F Y') : now()->translatedFormat('d F Y');
        $durasi = $params['duration_amount'] ?? 1;
        $satuan = $params['duration_unit'] ?? 'hari';
        $alasan = $params['reason'] ?? 'Kepentingan Keluarga';
        $alamat = $params['leave_address'] ?? 'Samarinda';
        $telp = $params['phone_number'] ?? $user->phone ?? '08xxxxxxxxxx';
        $leaveType = $params['leave_type'] ?? 'cuti_tahunan';
        $leaveTypeLabel = LeaveRequest::LEAVE_TYPES[$leaveType] ?? 'Cuti Tahunan';

        $draft = "Kepada\n";
        $draft .= "Yth. Kepala BPMP Kalimantan Timur\n";
        $draft .= "di Samarinda\n\n";
        $draft .= "FORMULIR PERMINTAAN DAN PEMBERIAN CUTI\n";
        $draft .= "Nomor: BKN/CUTI/" . date('Ymd') . "/" . str_pad($user->id, 4, '0', STR_PAD_LEFT) . "\n\n";
        $draft .= "I. DATA PEGAWAI\n";
        $draft .= "Nama        : " . $user->name . "\n";
        $draft .= "NIP         : " . ($user->nip ?? '-') . "\n";
        $draft .= "Jabatan     : " . ($user->position ?? '-') . "\n";
        $draft .= "Masa Kerja  : " . $masaKerjaText . "\n";
        $draft .= "Unit Kerja  : " . ($user->unit_kerja ?? 'BPMP Kalimantan Timur') . "\n\n";
        $draft .= "II. JENIS CUTI YANG DIAMBIL\n";
        $draft .= "[ " . ($leaveType === 'cuti_tahunan' ? 'V' : ' ') . " ] 1. Cuti Tahunan\n";
        $draft .= "[ " . ($leaveType === 'cuti_besar' ? 'V' : ' ') . " ] 2. Cuti Besar\n";
        $draft .= "[ " . ($leaveType === 'cuti_sakit' ? 'V' : ' ') . " ] 3. Cuti Sakit\n";
        $draft .= "[ " . ($leaveType === 'cuti_melahirkan' ? 'V' : ' ') . " ] 4. Cuti Melahirkan\n";
        $draft .= "[ " . ($leaveType === 'cuti_alasan_penting' ? 'V' : ' ') . " ] 5. Cuti Karena Alasan Penting\n";
        $draft .= "[ " . ($leaveType === 'cuti_cltn' ? 'V' : ' ') . " ] 6. Cuti di Luar Tanggungan Negara\n\n";
        $draft .= "III. ALASAN CUTI\n";
        $draft .= $alasan . "\n\n";
        $draft .= "IV. LAMANYA CUTI\n";
        $draft .= "Selama      : " . $durasi . " (" . $satuan . ")\n";
        $draft .= "Mulai Tgl   : " . $startDate->translatedFormat('d F Y') . " s.d " . $endDate->translatedFormat('d F Y') . "\n\n";
        $draft .= "V. CATATAN CUTI\n";
        $draft .= "1. CUTI TAHUNAN\n";
        $draft .= "   Tahun " . ($currentYear - 2) . " (N-2) : " . $balance->remaining_n2 . " hari\n";
        $draft .= "   Tahun " . ($currentYear - 1) . " (N-1) : " . $balance->remaining_n1 . " hari\n";
        $draft .= "   Tahun " . ($currentYear) . " (N)   : " . $balance->remaining_quota . " hari\n";
        $draft .= "2. CUTI BESAR                  : -\n";
        $draft .= "3. CUTI SAKIT                  : -\n";
        $draft .= "4. CUTI MELAHIRKAN             : -\n";
        $draft .= "5. CUTI KARENA ALASAN PENTING  : -\n";
        $draft .= "6. CLTN                        : -\n\n";
        $draft .= "VI. ALAMAT SELAMA MENJALANKAN CUTI\n";
        $draft .= "Alamat      : " . $alamat . "\n";
        $draft .= "Telepon     : " . $telp . "\n\n";
        $draft .= "Samarinda, " . $tglPermohonan . "\n";
        $draft .= "Hormat saya,\n\n\n";
        $draft .= "( " . $user->name . " )\n";
        $draft .= "NIP. " . ($user->nip ?? '-');

        return $draft;
    }

    /**
     * Potong kuota cuti setelah pengajuan disetujui penuh oleh PPK
     */
    public function deductBalanceOnApproval(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->leave_type === 'cuti_tahunan') {
            $year = Carbon::parse($leaveRequest->start_date)->year;
            $balance = LeaveBalance::getOrCreateForUser($leaveRequest->user, $year);

            $needed = $leaveRequest->duration_amount;

            // Prioritas pemotongan: N-2 -> N-1 -> N
            if ($balance->remaining_n2 > 0 && $needed > 0) {
                $deduct = min($needed, $balance->remaining_n2);
                $balance->remaining_n2 -= $deduct;
                $needed -= $deduct;
            }

            if ($balance->remaining_n1 > 0 && $needed > 0) {
                $deduct = min($needed, $balance->remaining_n1);
                $balance->remaining_n1 -= $deduct;
                $needed -= $deduct;
            }

            if ($needed > 0) {
                $balance->used_quota += $needed;
                $balance->remaining_quota = max(0, $balance->initial_quota - $balance->used_quota);
            }

            $balance->save();
        } elseif ($leaveRequest->leave_type === 'cuti_besar') {
            $year = Carbon::parse($leaveRequest->start_date)->year;
            $balance = LeaveBalance::getOrCreateForUser($leaveRequest->user, $year);
            $balance->cuti_besar_taken += $leaveRequest->duration_amount;
            // Ketentuan: mengambil cuti besar meniadakan hak cuti tahunan pada tahun berjalan
            $balance->remaining_quota = 0;
            $balance->save();
        } elseif ($leaveRequest->leave_type === 'cuti_sakit') {
            $year = Carbon::parse($leaveRequest->start_date)->year;
            $balance = LeaveBalance::getOrCreateForUser($leaveRequest->user, $year);
            $balance->cuti_sakit_taken += $leaveRequest->duration_amount;
            $balance->save();
        } elseif ($leaveRequest->leave_type === 'cuti_melahirkan') {
            $year = Carbon::parse($leaveRequest->start_date)->year;
            $balance = LeaveBalance::getOrCreateForUser($leaveRequest->user, $year);
            $balance->cuti_melahirkan_taken += $leaveRequest->duration_amount;
            $balance->save();
        } elseif ($leaveRequest->leave_type === 'cuti_alasan_penting') {
            $year = Carbon::parse($leaveRequest->start_date)->year;
            $balance = LeaveBalance::getOrCreateForUser($leaveRequest->user, $year);
            $balance->cuti_alasan_penting_taken += $leaveRequest->duration_amount;
            $balance->save();
        } elseif ($leaveRequest->leave_type === 'cuti_cltn') {
            $year = Carbon::parse($leaveRequest->start_date)->year;
            $balance = LeaveBalance::getOrCreateForUser($leaveRequest->user, $year);
            $balance->cltn_taken += $leaveRequest->duration_amount;
            $balance->save();
        }
    }
}
