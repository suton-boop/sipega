<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyAgenda;
use App\Models\AssignmentLetter;
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

        // Global Data SIPEGA: Modul Wall of Fame & Wall of Shame
        $top5Highest = User::orderBy('performance_score', 'desc')->take(5)->get();
        $top5Lowest = User::orderBy('performance_score', 'asc')->take(5)->get();

        if ($user->role === 'Admin' || $user->role === 'Kasubag') {
            // Admin: Fokus Statistik 65 Orang & Validasi Absen Lupa
            $totalUsers = User::count();
            return view('dashboard.admin', compact('top5Highest', 'top5Lowest', 'totalUsers', 'today'));
            
        } elseif ($user->role === 'Pimpinan') {
            // Pimpinan: Fokus 'Dinas Khusus' Privat, Wall of Fame, Monitor Performa
            $privateAssignments = AssignmentLetter::where('is_private', true)->latest()->get();
            return view('dashboard.pimpinan', compact('top5Highest', 'top5Lowest', 'privateAssignments'));

        } else {
            // Pegawai: Fokus Profil Pribadi, Warna Performa, Agenda Harian
            $myAgendaToday = DailyAgenda::where('user_id', $user->id)->where('date', $today)->first();
            return view('dashboard.pegawai', compact('myAgendaToday', 'user', 'top5Highest'));
        }
    }
}
