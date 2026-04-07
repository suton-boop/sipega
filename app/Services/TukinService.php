<?php

namespace App\Services;

use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\CalendarEvent;
use App\Models\Letter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class TukinService
{
    /**
     * Calculate Tukin based on Permendikbud No. 14 Year 2022
     * Includes detailed tier tracking for Finance reporting.
     */
    public function calculateForUser(User $user, $monthYear = null)
    {
        $now = $monthYear ? Carbon::parse($monthYear) : Carbon::now('Asia/Makassar');
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        
        // 1. Base Amount from Job Class
        $baseTukin = $user->jobClass ? $user->jobClass->base_amount : 0;

        // 2. Preparation
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
        $totalPenaltyPercent = 0;
        $dailyDetails = [];

        $summary = [
            'total_present' => 0,
            'total_alpa' => 0,
            'total_st' => 0,
            'total_cuti' => 0, 
            'total_sakit' => 0,
            'total_ijin' => 0,
            'tl_tiers' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            'psw_tiers' => [1 => 0, 2 => 0, 3 => 0, 4 => 0]
        ];

        // Fetch Assignment Letters (Single Day)
        $activeAssignmentDays = DB::table('assignment_letter_user')
            ->join('assignment_letters', 'assignment_letter_user.assignment_letter_id', '=', 'assignment_letters.id')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->pluck('date')
            ->toArray();

        // Fetch Official Admin Letters (Date Ranges + Approved)
        $approvedLetters = DB::table('letter_user')
            ->join('letters', 'letter_user.letter_id', '=', 'letters.id')
            ->where('letter_user.user_id', $user->id)
            ->where('status', 'Approved')
            ->where(function($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('date_start', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                  ->orWhereBetween('date_end', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            })
            ->select('date_start', 'date_end')
            ->get();

        $holidays = CalendarEvent::whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->whereIn('type', ['Holiday', 'Shared Leave'])
            ->pluck('date')
            ->toArray();

        // 3. Daily Calculation
        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            
            // ASN 5 Days Work Week: Skip Weekend & Holidays
            if ($date->isWeekend() || in_array($dateStr, $holidays)) continue;

            $dayLog = AttendanceLog::where('user_id', $user->id)->where('date', $dateStr)->first();
            $dailyPenalty = 0;
            $type = 'Hadir';

            if ($dayLog) {
                $tlPenalty = $this->getTieredPenalty($dayLog->tl_minutes ?? 0);
                $pswPenalty = $this->getTieredPenalty($dayLog->psw_minutes ?? 0);
                
                if ($dayLog->tl_minutes > 0) {
                    $tier = $this->getTierIndex($dayLog->tl_minutes);
                    if ($tier > 0) $summary['tl_tiers'][$tier]++;
                }
                if ($dayLog->psw_minutes > 0) {
                    $tier = $this->getTierIndex($dayLog->psw_minutes);
                    if ($tier > 0) $summary['psw_tiers'][$tier]++;
                }

                $dailyPenalty = $tlPenalty + $pswPenalty;
                if ($dailyPenalty > 2.5) $dailyPenalty = 2.5; // Cap per Permendikbud
                
                $type = $dailyPenalty > 0 ? 'TL/PSW' : 'Hadir';
                $summary['total_present']++;
            } else {
                // Check if user is on ST (AssignmentLetter) or SK/ST (Letter)
                $isOnAssignment = in_array($dateStr, $activeAssignmentDays);
                if (!$isOnAssignment) {
                    foreach ($approvedLetters as $l) {
                        if ($date->between($l->date_start, $l->date_end)) {
                            $isOnAssignment = true;
                            break;
                        }
                    }
                }

                if ($isOnAssignment) {
                    $type = 'Tugas Luar (ST)';
                    $summary['total_st']++;
                    $dailyPenalty = 0;
                } else {
                    $type = 'ALPA';
                    $summary['total_alpa']++;
                    $dailyPenalty = 5.0; // Automatic 5% for Alpa
                }
            }

            if ($dailyPenalty > 0) {
                $dailyDetails[] = [
                    'date' => $dateStr,
                    'type' => $type,
                    'penalty_percent' => $dailyPenalty,
                    'amount' => ($dailyPenalty / 100) * $baseTukin
                ];
                $totalPenaltyPercent += $dailyPenalty;
            }
        }

        // 4. Performance Deduction
        $perfDeduction = 0;
        $predicate = $user->performance_predicate ?? 'Baik';
        switch ($predicate) {
            case 'Cukup': $perfDeduction = 20; break;
            case 'Kurang': $perfDeduction = 40; break;
            case 'Sangat Kurang': $perfDeduction = 60; break;
        }

        $attendancePenaltyAmount = ($totalPenaltyPercent / 100) * $baseTukin;
        $performancePenaltyAmount = ($perfDeduction / 100) * $baseTukin;
        $totalPenaltyAmount = $attendancePenaltyAmount + $performancePenaltyAmount;

        return [
            'month' => $now->translatedFormat('F Y'),
            'job_class' => $user->jobClass ? $user->jobClass->class_name : 'No Class',
            'base_tukin' => $baseTukin,
            'attendance_penalty_percent' => $totalPenaltyPercent,
            'performance_predicate' => $predicate,
            'performance_penalty_percent' => $perfDeduction,
            'total_penalty_percentage' => $totalPenaltyPercent + $perfDeduction,
            'attendance_penalty_amount' => $attendancePenaltyAmount,
            'performance_penalty_amount' => $performancePenaltyAmount,
            'total_penalty_amount' => $totalPenaltyAmount,
            'net_tukin' => max(0, $baseTukin - $totalPenaltyAmount),
            'attendance_penalty_minutes' => AttendanceLog::where('user_id', $user->id)
                ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->selectRaw('SUM(COALESCE(tl_minutes, 0) + COALESCE(psw_minutes, 0)) as total')
                ->first()->total ?? 0,
            'data' => [
                'summary' => $summary,
                'details' => $dailyDetails
            ]
        ];
    }

    private function getTieredPenalty($minutes)
    {
        if ($minutes <= 0) return 0;
        if ($minutes <= 30) return 0.25;
        if ($minutes <= 60) return 0.50;
        if ($minutes <= 90) return 0.75;
        return 1.25;
    }

    private function getTierIndex($minutes)
    {
        if ($minutes >= 1 && $minutes <= 30) return 1;
        if ($minutes >= 31 && $minutes <= 60) return 2;
        if ($minutes >= 61 && $minutes <= 90) return 3;
        if ($minutes > 90) return 4;
        return 0;
    }
}
