<?php

namespace App\Services;

use App\Models\AssignmentLetter;
use App\Models\CalendarEvent;
use App\Models\LeaveRequest;
use App\Models\Letter;
use App\Models\MikPermit;
use App\Models\PiketEligibleEmployee;
use App\Models\PiketScheduleAssignment;
use App\Models\PiketUnit;
use App\Models\TravelReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PiketConflictDetectionService
{
    /**
     * Periksa apakah suatu tanggal adalah hari libur resmi / cuti bersama / akhir pekan
     */
    public function isHolidayOrWeekend($date): array
    {
        $d = Carbon::parse($date);
        
        // Cek akhir pekan (Sabtu = 6, Minggu = 0)
        if ($d->isWeekend()) {
            return [
                'is_off' => true,
                'type' => 'weekend',
                'description' => 'Hari Libur Akhir Pekan (' . $d->translatedFormat('l') . ')',
            ];
        }

        // Cek Kalender Hari Libur Nasional & Cuti Bersama SIPEGA
        $cal = CalendarEvent::whereDate('date', $d->toDateString())->first();
        if ($cal) {
            return [
                'is_off' => true,
                'type' => 'holiday',
                'description' => $cal->description ?: 'Hari Libur Nasional / Cuti Bersama',
            ];
        }

        return ['is_off' => false, 'type' => null, 'description' => null];
    }

    /**
     * Periksa apakah seorang pegawai memiliki benturan kegiatan pada tanggal tertentu
     */
    public function checkEmployeeConflict(int $userId, $date): array
    {
        $targetDate = Carbon::parse($date)->toDateString();

        // 1. Cek Surat Tugas via assignment_letters & assignment_letter_user
        $st = AssignmentLetter::whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        })
        ->whereDate('date', '<=', $targetDate)
        ->where(function ($q) use ($targetDate) {
            $q->whereDate('end_date', '>=', $targetDate)
              ->orWhereNull('end_date');
        })
        ->first();

        if ($st) {
            return [
                'has_conflict' => true,
                'conflict_type' => 'surat_tugas',
                'reference_number' => $st->letter_number,
                'title' => $st->title ?: 'Surat Tugas Kedinasan',
                'start_date' => Carbon::parse($st->date)->toDateString(),
                'end_date' => $st->end_date ? Carbon::parse($st->end_date)->toDateString() : Carbon::parse($st->date)->toDateString(),
                'description' => "Penugasan Kedinasan: {$st->title} (No. ST: {$st->letter_number})",
            ];
        }

        // 2. Cek Surat Tugas via letters & letter_user
        $letter = Letter::whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        })
        ->whereDate('date_start', '<=', $targetDate)
        ->where(function ($q) use ($targetDate) {
            $q->whereDate('date_end', '>=', $targetDate)
              ->orWhereNull('date_end');
        })
        ->first();

        if ($letter) {
            return [
                'has_conflict' => true,
                'conflict_type' => 'surat_tugas',
                'reference_number' => $letter->number,
                'title' => $letter->title ?: 'Surat Tugas / Undangan Kedinasan',
                'start_date' => Carbon::parse($letter->date_start)->toDateString(),
                'end_date' => $letter->date_end ? Carbon::parse($letter->date_end)->toDateString() : Carbon::parse($letter->date_start)->toDateString(),
                'description' => "Surat Tugas Resmi: {$letter->title} (No: {$letter->number})",
            ];
        }

        // 3. Cek Cuti Pegawai (leave_requests)
        $leave = LeaveRequest::where('user_id', $userId)
            ->whereNotIn('status', ['Ditolak', 'Dibatalkan', 'rejected', 'cancelled'])
            ->whereDate('start_date', '<=', $targetDate)
            ->whereDate('end_date', '>=', $targetDate)
            ->first();

        if ($leave) {
            return [
                'has_conflict' => true,
                'conflict_type' => 'cuti',
                'reference_number' => $leave->request_number,
                'title' => 'Cuti: ' . $leave->leave_type,
                'start_date' => Carbon::parse($leave->start_date)->toDateString(),
                'end_date' => Carbon::parse($leave->end_date)->toDateString(),
                'description' => "Pegawai sedang dalam masa cuti ({$leave->leave_type})",
            ];
        }

        // 4. Cek Izin Keluar Kantor (MiK)
        $mik = MikPermit::where('user_id', $userId)
            ->where('type', 'kantor')
            ->where('status', 'disetujui')
            ->whereDate('date', $targetDate)
            ->first();

        if ($mik) {
            return [
                'has_conflict' => true,
                'conflict_type' => 'izin_kantor',
                'reference_number' => 'MiK-' . $mik->id,
                'title' => 'Izin Keluar Kantor',
                'start_date' => $targetDate,
                'end_date' => $targetDate,
                'description' => "Izin dinas/keluar kantor: {$mik->purpose}",
            ];
        }

        return ['has_conflict' => false];
    }

    /**
     * Dapatkan Rekomendasi 3 Tingkat Pengganti untuk suatu penugasan piket yang bentrok
     */
    public function getReplacementRecommendations(PiketScheduleAssignment $assignment): array
    {
        $date = $assignment->date->toDateString();
        $unitId = $assignment->piket_unit_id;

        // PRIORITAS 1: Petugas Cadangan Terdaftar pada Jadwal
        $p1 = null;
        if ($assignment->backup_officer_id) {
            $conflictCheck = $this->checkEmployeeConflict($assignment->backup_officer_id, $date);
            $backupOfficer = User::find($assignment->backup_officer_id);
            if ($backupOfficer) {
                $p1 = [
                    'officer' => $backupOfficer,
                    'is_available' => !$conflictCheck['has_conflict'],
                    'conflict' => $conflictCheck['has_conflict'] ? $conflictCheck : null,
                    'priority' => 'prioritas_1_cadangan',
                    'label' => 'Prioritas 1: Petugas Cadangan Resmi',
                ];
            }
        }

        // PRIORITAS 2: Petugas Tersedia dari Daftar Eligibel Unit Pelayanan
        // Syarat: Eligibel di unit tsb, aktif, tidak sedang piket di tanggal itu, tidak bentrok ST/cuti
        $activeOfficerIdsOnDate = PiketScheduleAssignment::whereDate('date', $date)
            ->pluck('active_officer_id')
            ->toArray();

        $eligibleOfficers = PiketEligibleEmployee::where('piket_unit_id', $unitId)
            ->where('is_available', true)
            ->whereNotIn('user_id', array_merge($activeOfficerIdsOnDate, [$assignment->primary_officer_id]))
            ->with('user')
            ->get();

        $p2List = [];
        $startOfMonth = Carbon::parse($date)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::parse($date)->endOfMonth()->toDateString();

        foreach ($eligibleOfficers as $el) {
            if (!$el->user) continue;

            $conflictCheck = $this->checkEmployeeConflict($el->user_id, $date);
            if (!$conflictCheck['has_conflict']) {
                // Hitung jumlah giliran piket bulan ini untuk pemerataan
                $shiftCount = PiketScheduleAssignment::where('active_officer_id', $el->user_id)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->count();

                $p2List[] = [
                    'officer' => $el->user,
                    'shift_count_this_month' => $shiftCount,
                    'max_shifts' => $el->max_shifts_per_month,
                    'priority' => 'prioritas_2_tersedia',
                    'label' => 'Prioritas 2: Petugas Tersedia (Rotasi Adil)',
                ];
            }
        }

        // Urutkan P2 berdasarkan giliran paling sedikit (pemerataan beban)
        usort($p2List, function ($a, $b) {
            return $a['shift_count_this_month'] <=> $b['shift_count_this_month'];
        });

        // PRIORITAS 3: Seluruh Pegawai BPMP yang Bebas Benturan
        $allRealEmployees = User::realPegawai()
            ->where('is_active', true)
            ->whereNotIn('id', array_merge($activeOfficerIdsOnDate, [$assignment->primary_officer_id]))
            ->get();

        $p3List = [];
        foreach ($allRealEmployees as $emp) {
            $conflictCheck = $this->checkEmployeeConflict($emp->id, $date);
            if (!$conflictCheck['has_conflict']) {
                $p3List[] = [
                    'officer' => $emp,
                    'priority' => 'prioritas_3_manual',
                    'label' => 'Prioritas 3: Penunjukan Bebas Pegawai BPMP',
                ];
            }
        }

        return [
            'prioritas_1' => $p1,
            'prioritas_2' => array_slice($p2List, 0, 8),
            'prioritas_3' => array_slice($p3List, 0, 15),
        ];
    }
}
