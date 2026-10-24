<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyAgenda;
use App\Models\AssignmentLetter;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard Role-Based (Tahap 4)
     */
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today()->format('Y-m-d');

        // Global Data SIPEGA: Modul Wall of Fame & Wall of Shame (Khusus Pegawai Riil, Administrator Tidak Masuk Penilaian)
        $top5Highest = User::realPegawai()->whereNotNull('performance_score')->orderBy('performance_score', 'desc')->take(5)->get();
        $top5Lowest = User::realPegawai()->whereNotNull('performance_score')->orderBy('performance_score', 'asc')->take(5)->get();

        // Upcoming Meetings for the Logged in user
        $upcomingMeetings = \App\Models\Meeting::where('date', '>=', $today)
            ->where(function($query) use ($user) {
                $query->where('target_type', 'all')
                      ->orWhereHas('participants', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->take(5)
            ->get();
            
        // Personal Agenda
        $todayAgenda = DailyAgenda::with('items')->where('user_id', $user->id)->where('date', $today)->first();

        if ($user->role === 'Admin' || $user->role === 'Kasubag' || $user->role === 'Operator') {
            // Admin/Operator/Kasubag: Fokus Statistik Pegawai & Validasi Absen Lupa
            $totalUsers = User::realPegawai()->count();
            $allPegawai = User::realPegawai()->orderBy('name')->get();
            $recentLetters = AssignmentLetter::with('users')->latest()->take(5)->get(); // For recent archives
            $pendingEvalCount = DailyAgenda::whereNotNull('realization_submitted_at')
                ->whereNull('leader_rating')
                ->whereHas('user', function($q) { $q->realPegawai(); })
                ->count();
            return view('dashboard.admin', compact('top5Highest', 'top5Lowest', 'totalUsers', 'today', 'allPegawai', 'recentLetters', 'pendingEvalCount', 'upcomingMeetings', 'todayAgenda'));
            
        } elseif ($user->role === 'Pimpinan') {
            // Pimpinan: Fokus 'Performance Heatmap' & Real-time Monitoring
            $heatmap = User::realPegawai()->selectRaw("performance_color, count(*) as total")
                        ->whereNotNull('performance_color')
                        ->groupBy('performance_color')
                        ->pluck('total', 'performance_color')->toArray();

            $submittedToday = DailyAgenda::where('date', $today)
                ->whereHas('user', function($q) { $q->realPegawai(); })
                ->pluck('user_id')->toArray();
            $allUsers = User::realPegawai()->orderBy('name')->get();
            
            $privateAssignments = AssignmentLetter::where('is_private', true)->latest()->get();
            $totalUsers = $allUsers->count();
            $pendingEvalCount = DailyAgenda::whereNotNull('realization_submitted_at')
                ->whereNull('leader_rating')
                ->whereHas('user', function($q) { $q->realPegawai(); })
                ->count();
            
            return view('dashboard.pimpinan', compact('top5Highest', 'top5Lowest', 'heatmap', 'submittedToday', 'allUsers', 'privateAssignments', 'totalUsers', 'today', 'pendingEvalCount', 'upcomingMeetings', 'todayAgenda'));

        } else {
            // Pegawai: Fokus Overview & Performa
            $myAssignmentsCount = \DB::table('assignment_letter_user')->where('user_id', $user->id)->count();

            // Log Kehadiran Pribadi Pegawai (Sesuai Log Masing-Masing)
            $todayLog = AttendanceLog::where('user_id', $user->id)->where('date', $today)->first();

            $latestLogDate = AttendanceLog::where('user_id', $user->id)->max('date');
            $targetMonth = $latestLogDate ? Carbon::parse($latestLogDate)->format('Y-m') : Carbon::now('Asia/Makassar')->format('Y-m');
            $startOfMonth = Carbon::parse($targetMonth . '-01')->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            $myMonthlyLogs = AttendanceLog::where('user_id', $user->id)
                ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->orderBy('date', 'desc')
                ->get();

            $myAttendanceSummary = [
                'month_label' => Carbon::parse($targetMonth . '-01')->translatedFormat('F Y'),
                'target_month' => $targetMonth,
                'total_present' => $myMonthlyLogs->filter(function($l) { return $l->check_in || $l->check_out || $l->status === 'DL'; })->count(),
                'total_tl_minutes' => $myMonthlyLogs->sum('tl_minutes'),
                'total_psw_minutes' => $myMonthlyLogs->sum('psw_minutes'),
                'total_dl' => $myMonthlyLogs->where('status', 'DL')->count(),
                'recent_logs' => $myMonthlyLogs->take(10),
            ];

            return view('dashboard.pegawai', compact(
                'user',
                'top5Highest',
                'todayAgenda',
                'myAssignmentsCount',
                'upcomingMeetings',
                'todayLog',
                'myAttendanceSummary'
            ));
        }
    }
}
