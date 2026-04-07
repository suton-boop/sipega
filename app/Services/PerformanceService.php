<?php

namespace App\Services;

use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\DailyAgenda;
use App\Models\Letter;
use Carbon\Carbon;

class PerformanceService
{
    /**
     * Calculate performance score for a specific date (default today)
     */
    public function calculateForUser(User $user, $date = null)
    {
        $date = $date ?: Carbon::today('Asia/Makassar')->format('Y-m-d');
        $checkDate = Carbon::parse($date)->startOfDay();

        // 1. ABSENSI (40%)
        $attendanceScore = 40;
        $log = AttendanceLog::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        if ($log && $log->check_in) {
            $checkIn = Carbon::parse($log->check_in);
            $officeStart = Carbon::parse($date . ' 07:30:00');
            
            if ($checkIn->gt($officeStart)) {
                $minutesLate = $checkIn->diffInMinutes($officeStart);
                // Deduction: 1 point per minute late, max 40
                $attendanceScore = max(0, 40 - $minutesLate);
            }
        } elseif ($checkDate->isPast() && !$checkDate->isToday()) {
            // If it was a workday in the past and no log, score is 0
            $attendanceScore = 0;
        }

        // 2. AGENDA (30%)
        $agendaScore = 0;
        $agenda = DailyAgenda::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        if ($agenda && $agenda->submitted_at && $agenda->realization_submitted_at) {
            $agendaScore = 30;
        }

        // 3. RAPAT / SURAT TUGAS (30%)
        $taskScore = 30; // Default: Tugas Rutin
        
        $activeST = $user->letters()
            ->where('type', 'ST')
            ->where('status', 'Approved')
            ->whereDate('date_start', '<=', $date)
            ->whereDate('date_end', '>=', $date)
            ->first();

        if ($activeST) {
            // If has ST, MUST have report
            if (!empty($activeST->pivot->report_text) && !empty($activeST->pivot->report_photo_1)) {
                $taskScore = 30;
            } else {
                $taskScore = 0;
            }
        }

        $totalScore = $attendanceScore + $agendaScore + $taskScore;

        // Determine Color
        $color = 'Merah';
        if ($totalScore > 90) {
            $color = 'Biru';
        } elseif ($totalScore >= 76) {
            $color = 'Hijau';
        } elseif ($totalScore >= 61) {
            $color = 'Kuning';
        }

        return [
            'score' => $totalScore,
            'color' => $color,
            'breakdown' => [
                'attendance' => $attendanceScore,
                'agenda' => $agendaScore,
                'task' => $taskScore
            ]
        ];
    }

    /**
     * Update user score in database
     */
    public function updateScore(User $user)
    {
        $result = $this->calculateForUser($user, Carbon::today('Asia/Makassar')->format('Y-m-d'));
        
        $user->update([
            'performance_score' => $result['score'],
            'performance_color' => $result['color']
        ]);

        return $result;
    }
}
