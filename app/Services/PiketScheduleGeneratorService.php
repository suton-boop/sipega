<?php

namespace App\Services;

use App\Models\PiketConflict;
use App\Models\PiketEligibleEmployee;
use App\Models\PiketSchedule;
use App\Models\PiketScheduleAssignment;
use App\Models\PiketUnit;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class PiketScheduleGeneratorService
{
    protected PiketConflictDetectionService $conflictService;

    public function __construct(PiketConflictDetectionService $conflictService)
    {
        $this->conflictService = $conflictService;
    }

    /**
     * Generate Jadwal Piket Tahunan atau Bulanan Secara Otomatis
     */
    public function generate(
        int $unitId,
        int $year,
        ?int $month = null,
        ?string $title = null,
        ?User $creator = null
    ): PiketSchedule {
        $unit = PiketUnit::findOrFail($unitId);

        if ($month) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            $periodType = 'bulanan';
            $defaultTitle = "Jadwal Piket {$unit->name} - " . $startDate->translatedFormat('F Y');
        } else {
            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate = Carbon::create($year, 12, 31)->endOfYear();
            $periodType = 'tahunan';
            $defaultTitle = "Jadwal Piket Tahunan {$unit->name} Tahun {$year}";
        }

        $title = $title ?: $defaultTitle;

        // Ambil daftar pegawai yang eligibel untuk unit ini
        $eligiblePrimaries = PiketEligibleEmployee::where('piket_unit_id', $unitId)
            ->where('is_available', true)
            ->whereIn('assignment_role', ['utama', 'keduanya'])
            ->pluck('user_id')
            ->toArray();

        $eligibleBackups = PiketEligibleEmployee::where('piket_unit_id', $unitId)
            ->where('is_available', true)
            ->whereIn('assignment_role', ['cadangan', 'keduanya'])
            ->pluck('user_id')
            ->toArray();

        // Fallback jika cadangan kosong, gunakan seluruh pegawai eligibel
        if (empty($eligibleBackups)) {
            $eligibleBackups = $eligiblePrimaries;
        }

        if (empty($eligiblePrimaries)) {
            throw new \Exception("Belum ada pegawai yang didaftarkan sebagai petugas eligibel pada unit {$unit->name}. Silakan atur di menu Data Petugas Piket terlebih dahulu.");
        }

        return DB::transaction(function () use (
            $unit,
            $year,
            $month,
            $periodType,
            $title,
            $startDate,
            $endDate,
            $creator,
            $eligiblePrimaries,
            $eligibleBackups
        ) {
            // Buat header PiketSchedule
            $schedule = PiketSchedule::create([
                'piket_unit_id' => $unit->id,
                'year' => $year,
                'month' => $month,
                'period_type' => $periodType,
                'title' => $title,
                'status' => 'draft',
                'revision_number' => 'Rev.0',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'created_by' => $creator ? $creator->id : null,
                'notes' => 'Jadwal disusun otomatis dengan algoritma rotasi adil, eliminasi hari libur, dan deteksi benturan awal.',
            ]);

            // Tracker giliran untuk pemerataan (user_id => total_assigned)
            $primaryShiftCounts = array_fill_keys($eligiblePrimaries, 0);
            $backupShiftCounts = array_fill_keys($eligibleBackups, 0);

            $period = CarbonPeriod::create($startDate, $endDate);
            $dailyCount = $unit->daily_officers_count ?: 2;
            $lastAssignedPrimaries = [];

            foreach ($period as $currentDate) {
                $dateStr = $currentDate->toDateString();

                // 1. Cek Libur Akhir Pekan & Hari Libur Nasional
                $offCheck = $this->conflictService->isHolidayOrWeekend($dateStr);
                if ($offCheck['is_off']) {
                    continue; // Skip tanggal libur
                }

                // 2. Tentukan Petugas Utama Harian sesuai kuota harian
                $assignedToday = [];
                for ($slot = 1; $slot <= $dailyCount; $slot++) {
                    // Cari pegawai dengan jumlah shift paling sedikit yang tidak bertugas kemarin dan tidak bentrok
                    $candidatePool = $eligiblePrimaries;
                    // Sort by shift count ascending
                    usort($candidatePool, function ($a, $b) use ($primaryShiftCounts) {
                        return $primaryShiftCounts[$a] <=> $primaryShiftCounts[$b];
                    });

                    $chosenPrimary = null;
                    $fallbackPrimary = null;

                    foreach ($candidatePool as $candId) {
                        if (in_array($candId, $assignedToday)) continue;

                        $conflictCheck = $this->conflictService->checkEmployeeConflict($candId, $dateStr);
                        if (!$conflictCheck['has_conflict']) {
                            // Prioritaskan yang tidak bertugas kemarin jika memungkinkan
                            if (!in_array($candId, $lastAssignedPrimaries) || count($eligiblePrimaries) <= $dailyCount) {
                                $chosenPrimary = $candId;
                                break;
                            }
                            if (!$fallbackPrimary) {
                                $fallbackPrimary = $candId;
                            }
                        } else {
                            if (!$fallbackPrimary) {
                                $fallbackPrimary = $candId; // Terpaksa jika semua bentrok
                            }
                        }
                    }

                    $selectedPrimary = $chosenPrimary ?: ($fallbackPrimary ?: $candidatePool[0]);
                    $assignedToday[] = $selectedPrimary;
                    $primaryShiftCounts[$selectedPrimary]++;

                    // 3. Tentukan Petugas Cadangan
                    $backupPool = $eligibleBackups;
                    usort($backupPool, function ($a, $b) use ($backupShiftCounts) {
                        return $backupShiftCounts[$a] <=> $backupShiftCounts[$b];
                    });

                    $selectedBackup = null;
                    foreach ($backupPool as $bId) {
                        if ($bId !== $selectedPrimary && !in_array($bId, $assignedToday)) {
                            $selectedBackup = $bId;
                            break;
                        }
                    }
                    if (!$selectedBackup) {
                        $selectedBackup = $backupPool[0];
                    }
                    $backupShiftCounts[$selectedBackup]++;

                    // 4. Periksa apakah petugas utama yang terpilih bentrok
                    $primaryConflict = $this->conflictService->checkEmployeeConflict($selectedPrimary, $dateStr);
                    $hasConflict = $primaryConflict['has_conflict'];
                    $activeOfficer = $selectedPrimary;

                    // Buat Assignment
                    $assignment = PiketScheduleAssignment::create([
                        'piket_schedule_id' => $schedule->id,
                        'piket_unit_id' => $unit->id,
                        'date' => $dateStr,
                        'primary_officer_id' => $selectedPrimary,
                        'backup_officer_id' => $selectedBackup,
                        'active_officer_id' => $activeOfficer,
                        'role_label' => "Petugas {$slot}",
                        'status' => $hasConflict ? 'conflict' : 'normal',
                        'has_conflict' => $hasConflict,
                        'conflict_type' => $hasConflict ? $primaryConflict['conflict_type'] : null,
                        'conflict_description' => $hasConflict ? $primaryConflict['description'] : null,
                        'conflict_reference_number' => $hasConflict ? ($primaryConflict['reference_number'] ?? null) : null,
                    ]);

                    // Catat ke piket_conflicts jika bentrok
                    if ($hasConflict) {
                        PiketConflict::create([
                            'piket_schedule_assignment_id' => $assignment->id,
                            'user_id' => $selectedPrimary,
                            'conflict_date' => $dateStr,
                            'conflict_source' => $primaryConflict['conflict_type'],
                            'reference_number' => $primaryConflict['reference_number'] ?? null,
                            'activity_title' => $primaryConflict['title'] ?? 'Benturan Kegiatan Kedinasan',
                            'activity_start_date' => $primaryConflict['start_date'] ?? $dateStr,
                            'activity_end_date' => $primaryConflict['end_date'] ?? $dateStr,
                            'status' => 'pending',
                        ]);
                    }
                }

                $lastAssignedPrimaries = $assignedToday;
            }

            return $schedule;
        });
    }

    /**
     * Audit ulang seluruh penugasan dalam suatu jadwal untuk mendeteksi konflik terbaru
     */
    public function auditScheduleConflicts(int $scheduleId): array
    {
        $schedule = PiketSchedule::with('assignments.primaryOfficer', 'assignments.backupOfficer')->findOrFail($scheduleId);
        $conflictCount = 0;

        foreach ($schedule->assignments as $assignment) {
            $conflictCheck = $this->conflictService->checkEmployeeConflict(
                $assignment->active_officer_id,
                $assignment->date->toDateString()
            );

            if ($conflictCheck['has_conflict']) {
                $assignment->update([
                    'has_conflict' => true,
                    'status' => $assignment->is_replaced ? 'replaced' : 'conflict',
                    'conflict_type' => $conflictCheck['conflict_type'],
                    'conflict_description' => $conflictCheck['description'],
                    'conflict_reference_number' => $conflictCheck['reference_number'] ?? null,
                ]);

                PiketConflict::firstOrCreate(
                    [
                        'piket_schedule_assignment_id' => $assignment->id,
                        'user_id' => $assignment->active_officer_id,
                        'conflict_date' => $assignment->date->toDateString(),
                    ],
                    [
                        'conflict_source' => $conflictCheck['conflict_type'],
                        'reference_number' => $conflictCheck['reference_number'] ?? null,
                        'activity_title' => $conflictCheck['title'] ?? 'Benturan Penugasan Kedinasan',
                        'activity_start_date' => $conflictCheck['start_date'] ?? $assignment->date->toDateString(),
                        'activity_end_date' => $conflictCheck['end_date'] ?? $assignment->date->toDateString(),
                        'status' => 'pending',
                    ]
                );

                $conflictCount++;
            } else {
                if ($assignment->has_conflict && !$assignment->is_replaced) {
                    $assignment->update([
                        'has_conflict' => false,
                        'status' => 'normal',
                        'conflict_type' => null,
                        'conflict_description' => null,
                    ]);
                }
            }
        }

        return [
            'total_assignments' => $schedule->assignments->count(),
            'conflict_count' => $conflictCount,
        ];
    }
}
