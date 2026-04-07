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

        // Mengambil seluruh pegawai
        $pegawai = User::where('role', 'Pegawai')->orderBy('name')->get();

        $fullRecap = $pegawai->map(function ($user) use ($year) {
            // Hitung Dinas Luar Formal (Approved & Type ST/SK)
            $externalCount = $user->letters()
                ->where('status', 'Approved')
                ->whereYear('date_start', $year)
                ->count();

            // Hitung Penugasan Internal
            $internalCount = $user->assignmentLetters()
                ->whereYear('date', $year)
                ->count();

            $total = $externalCount + $internalCount;

            return [
                'user' => $user,
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
