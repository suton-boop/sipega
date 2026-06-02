<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssignmentLetter;
use App\Models\DailyAgenda;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserDocumentController extends Controller
{
    /**
     * Tampilkan Pusat Dokumen Fisik Pegawai
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 1. Ambil riwayat Laporan Harian (Daily Agendas)
        // Batasi 30 hari terakhir untuk mempermudah loading
        $agendas = DailyAgenda::where('user_id', $user->id)
            ->whereNotNull('realization_submitted_at')
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();
            
        // 2. Ambil riwayat Surat Tugas
        $assignments = AssignmentLetter::whereHas('users', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })
        ->orderBy('date', 'desc')
        ->take(50)
        ->get();

        // 3. Info Tukin (Bulan Ini)
        $currentMonth = Carbon::now('Asia/Makassar')->translatedFormat('F Y');

        return view('documents.index', compact('agendas', 'assignments', 'currentMonth', 'user'));
    }
}
