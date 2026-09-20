<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\AssignmentLetter;
use App\Models\User;
use Illuminate\Http\Request;

class TravelRecapController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->year ?? date('Y');

        // Mengambil seluruh pegawai (di luar Pimpinan / Sekpri jika diinginkan, atau scopeRealPegawai)
        $pegawai = User::whereIn('role', ['Pegawai', 'Operator'])->orderBy('name')->get();

        $fullRecap = $pegawai->map(function ($user) use ($year) {
            // Hitung Surat Tugas Approved per Kategori
            $dlkCount = $user->letters()
                ->where('status', 'Approved')
                ->where(function($q) {
                    $q->where('category', 'DLK')
                      ->orWhereNull('category'); // Fallback data lama
                })
                ->whereYear('date_start', $year)
                ->count();

            $dlpCount = $user->letters()
                ->where('status', 'Approved')
                ->where('category', 'DLP')
                ->whereYear('date_start', $year)
                ->count();

            $dlnCount = $user->letters()
                ->where('status', 'Approved')
                ->where('category', 'DLN')
                ->whereYear('date_start', $year)
                ->count();

            // Total Surat Tugas Formal Approved
            $externalCount = $dlkCount + $dlpCount + $dlnCount;

            // Hitung Penugasan Internal (AssignmentLetter) jika ada
            $internalCount = $user->assignmentLetters()
                ->whereYear('date', $year)
                ->count();

            $total = $externalCount + $internalCount;

            return [
                'user' => $user,
                'dlk_count' => $dlkCount,
                'dlp_count' => $dlpCount,
                'dln_count' => $dlnCount,
                'external_count' => $externalCount,
                'internal_count' => $internalCount,
                'total_trips' => $total,
                'is_grounded' => $total === 0
            ];
        });

        // Pisahkan data untuk tampilan yang lebih rapi
        $activeRecap = $fullRecap->where('total_trips', '>', 0);
        $zeroRecap = $fullRecap->where('total_trips', '==', 0);

        return view('admin.travel.recap', compact('activeRecap', 'zeroRecap', 'year'));
    }
}
