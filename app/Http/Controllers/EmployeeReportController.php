<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PerformanceService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeReportController extends Controller
{
    /**
     * Tampilkan Halaman Rekap & Raport Capaian Pegawai
     */
    public function index(Request $request)
    {
        // Akses khusus manajemen (Admin, Pimpinan, Kasubag) atau pegawai dapat melihat transparansi
        $user = auth()->user();
        if (!in_array($user->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator'])) {
            abort(403, 'Akses terbatas untuk pimpinan dan manajemen.');
        }

        $query = User::realPegawai();

        // 1. Filter Warna Capaian
        if ($request->filled('color')) {
            $query->where('performance_color', $request->color);
        }

        // 2. Filter Gugus Mutu
        if ($request->filled('gugus_mutu')) {
            $query->where('gugus_mutu', $request->gugus_mutu);
        }

        // 3. Filter Pencarian Nama / NIP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        // 4. Urutan (Sort)
        $sort = $request->input('sort', 'highest');
        if ($sort === 'lowest') {
            $query->orderBy('performance_score', 'asc');
        } elseif ($sort === 'name') {
            $query->orderBy('name', 'asc');
        } else {
            // Default: Tertinggi (Ranking 1)
            $query->orderBy('performance_score', 'desc')->orderBy('name', 'asc');
        }

        // Statistik Global (Sebelum difilter)
        $allReal = User::realPegawai()->get();
        $totalPegawai = $allReal->count();

        $countBiru = $allReal->where('performance_color', 'Biru')->count();
        $countHijau = $allReal->where('performance_color', 'Hijau')->count();
        $countKuning = $allReal->where('performance_color', 'Kuning')->count();
        $countMerah = $allReal->where('performance_color', 'Merah')->count();

        $avgScore = $totalPegawai > 0 ? round($allReal->avg('performance_score'), 1) : 0;

        $stats = [
            'total' => $totalPegawai,
            'avg_score' => $avgScore,
            'biru' => [
                'count' => $countBiru,
                'pct' => $totalPegawai > 0 ? round(($countBiru / $totalPegawai) * 100, 1) : 0,
            ],
            'hijau' => [
                'count' => $countHijau,
                'pct' => $totalPegawai > 0 ? round(($countHijau / $totalPegawai) * 100, 1) : 0,
            ],
            'kuning' => [
                'count' => $countKuning,
                'pct' => $totalPegawai > 0 ? round(($countKuning / $totalPegawai) * 100, 1) : 0,
            ],
            'merah' => [
                'count' => $countMerah,
                'pct' => $totalPegawai > 0 ? round(($countMerah / $totalPegawai) * 100, 1) : 0,
            ],
        ];

        $employees = $query->paginate(25)->withQueryString();

        // Ambil daftar unik Gugus Mutu untuk filter dropdown
        $gugusMutuList = User::realPegawai()->whereNotNull('gugus_mutu')->distinct()->pluck('gugus_mutu')->sort();

        return view('admin.performance.recap', compact('employees', 'stats', 'gugusMutuList'));
    }

    /**
     * Hitung Ulang Skor Capaian Seluruh Pegawai secara Real-Time
     */
    public function recalculate(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan'])) {
            abort(403);
        }

        $service = new PerformanceService();
        $users = User::realPegawai()->get();

        foreach ($users as $user) {
            $service->updateScore($user);
        }

        return redirect()->route('performance.recap')
            ->with('success', 'Skor capaian dan kategori warna seluruh pegawai berhasil dikalkulasi ulang.');
    }

    /**
     * Cetak Rekap Raport Capaian Pegawai dalam format PDF
     */
    public function exportPdf(Request $request)
    {
        $query = User::realPegawai();

        if ($request->filled('color')) {
            $query->where('performance_color', $request->color);
        }
        if ($request->filled('gugus_mutu')) {
            $query->where('gugus_mutu', $request->gugus_mutu);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('performance_score', 'desc')->orderBy('name', 'asc')->get();

        $stats = [
            'total' => $employees->count(),
            'avg_score' => $employees->count() > 0 ? round($employees->avg('performance_score'), 1) : 0,
            'biru' => $employees->where('performance_color', 'Biru')->count(),
            'hijau' => $employees->where('performance_color', 'Hijau')->count(),
            'kuning' => $employees->where('performance_color', 'Kuning')->count(),
            'merah' => $employees->where('performance_color', 'Merah')->count(),
        ];

        $pdf = Pdf::loadView('pdf.performance_recap', compact('employees', 'stats'));
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'dpi' => 150,
        ]);

        $filename = 'Raport_Capaian_Pegawai_' . date('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
}
