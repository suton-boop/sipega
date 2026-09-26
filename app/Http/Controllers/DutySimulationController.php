<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Letter;
use Carbon\Carbon;

class DutySimulationController extends Controller
{
    /**
     * Tampilkan antarmuka Simulasi Penugasan Surat Tugas
     */
    public function index(Request $request)
    {
        // Otorisasi: Khusus Admin, Pimpinan, dan Kasubag
        if (!in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            return abort(403, 'Akses Ditolak. Halaman simulasi hanya untuk manajemen.');
        }

        Carbon::setLocale('id');

        // 1. Parameter Filter
        $dateStart = $request->filled('date_start') ? $request->date_start : Carbon::today()->format('Y-m-d');
        $dateEnd = $request->filled('date_end') ? $request->date_end : $dateStart;
        if ($dateEnd < $dateStart) {
            $dateEnd = $dateStart;
        }

        $selectedJabatan = $request->get('jabatan', 'all');
        $selectedGugus = $request->get('gugus_mutu', 'all');
        $selectedStatus = $request->get('status', 'all'); // 'all', 'available', 'busy'
        $search = trim($request->get('search', ''));
        $year = Carbon::parse($dateStart)->year;

        // 2. Ambil Master Pilihan Jabatan & Gugus Mutu
        $availablePositions = User::realPegawai()
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->distinct()
            ->orderBy('position')
            ->pluck('position')
            ->toArray();

        $defaultGugus = [
            'GM 1 - PAUD & Kesetaraan',
            'GM 2 - Sekolah Dasar (SD)',
            'GM 3 - SMP',
            'GM 4 - SMA, SMK & SLB',
            'GM 5 - Tata Usaha & Kemitraan'
        ];

        $dbGugus = User::realPegawai()
            ->whereNotNull('gugus_mutu')
            ->where('gugus_mutu', '!=', '')
            ->distinct()
            ->orderBy('gugus_mutu')
            ->pluck('gugus_mutu')
            ->toArray();

        $availableGugusMutu = array_values(array_unique(array_merge($defaultGugus, $dbGugus)));

        // 3. Query Pegawai Riil dengan Filter Awal
        $userQuery = User::realPegawai()->where('is_active', true);

        if (!empty($search)) {
            $userQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($selectedJabatan !== 'all' && !empty($selectedJabatan)) {
            $userQuery->where('position', $selectedJabatan);
        }

        if ($selectedGugus !== 'all' && !empty($selectedGugus)) {
            $userQuery->where('gugus_mutu', $selectedGugus);
        }

        $users = $userQuery->orderBy('name')->get();

        // 4. Cari Surat Tugas Approved yang beririsan dengan [dateStart, dateEnd]
        $approvedLetters = Letter::where('status', 'Approved')
            ->where('date_start', '<=', $dateEnd)
            ->whereRaw('COALESCE(date_end, date_start) >= ?', [$dateStart])
            ->with('users')
            ->get();

        // Bangun peta konflik jadwal: user_id => array detail surat tugas
        $conflictMap = [];
        foreach ($approvedLetters as $letter) {
            $stStart = Carbon::parse($letter->date_start);
            $stEnd = Carbon::parse($letter->date_end ?? $letter->date_start);

            if ($stStart->format('Y-m-d') === $stEnd->format('Y-m-d')) {
                $rangeText = $stStart->translatedFormat('d F Y');
            } else {
                $rangeText = $stStart->translatedFormat('d') . ' s.d ' . $stEnd->translatedFormat('d F Y');
            }

            foreach ($letter->users as $u) {
                if (!isset($conflictMap[$u->id])) {
                    $conflictMap[$u->id] = [];
                }

                $conflictMap[$u->id][] = [
                    'letter_id'     => $letter->id,
                    'letter_number' => $letter->number ?? "ST-{$letter->id}",
                    'title'         => $letter->title,
                    'location'      => $letter->location ?? 'Sesuai ST',
                    'category'      => $letter->category ?? 'DLK',
                    'date_start'    => $stStart->format('Y-m-d'),
                    'date_end'      => $stEnd->format('Y-m-d'),
                    'date_range'    => $rangeText,
                ];
            }
        }

        // 5. Analisis Ketersediaan & Riwayat Dinas Tahunan per Pegawai
        $simulationList = $users->map(function ($user) use ($conflictMap, $year) {
            $isBusy = isset($conflictMap[$user->id]) && count($conflictMap[$user->id]) > 0;
            $conflicts = $isBusy ? $conflictMap[$user->id] : [];

            // Riwayat Tugas Tahun Berjalan (DLK, DLP, DLN)
            $approvedYearLetters = $user->letters()
                ->where('status', 'Approved')
                ->whereYear('date_start', $year)
                ->get();

            $dlkCount = $approvedYearLetters->filter(fn($l) => in_array($l->category, ['DLK', null]))->count();
            $dlpCount = $approvedYearLetters->where('category', 'DLP')->count();
            $dlnCount = $approvedYearLetters->where('category', 'DLN')->count();
            $totalTrips = $dlkCount + $dlpCount + $dlnCount;

            return [
                'user'         => $user,
                'is_busy'      => $isBusy,
                'is_available' => !$isBusy,
                'conflicts'    => $conflicts,
                'dlk_count'    => $dlkCount,
                'dlp_count'    => $dlpCount,
                'dln_count'    => $dlnCount,
                'total_trips'  => $totalTrips,
            ];
        });

        // 6. Filter Tambahan berdasarkan Ketersediaan
        if ($selectedStatus === 'available') {
            $simulationList = $simulationList->where('is_available', true);
        } elseif ($selectedStatus === 'busy') {
            $simulationList = $simulationList->where('is_busy', true);
        }

        // 7. Hitung Statistik Ringkas untuk Dasbor Simulasi
        $totalPegawaiFiltered = $simulationList->count();
        $totalAvailable = $simulationList->where('is_available', true)->count();
        $totalBusy = $simulationList->where('is_busy', true)->count();
        $availabilityRate = $totalPegawaiFiltered > 0 ? round(($totalAvailable / $totalPegawaiFiltered) * 100) : 100;

        // Jika request via AJAX / JSON
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'date_start'       => $dateStart,
                    'date_end'         => $dateEnd,
                    'total_filtered'   => $totalPegawaiFiltered,
                    'total_available'  => $totalAvailable,
                    'total_busy'       => $totalBusy,
                    'availability_rate'=> $availabilityRate,
                    'list'             => $simulationList->values()
                ]
            ]);
        }

        return view('admin.simulation.index', compact(
            'simulationList',
            'dateStart',
            'dateEnd',
            'selectedJabatan',
            'selectedGugus',
            'selectedStatus',
            'search',
            'availablePositions',
            'availableGugusMutu',
            'totalPegawaiFiltered',
            'totalAvailable',
            'totalBusy',
            'availabilityRate',
            'year'
        ));
    }
}
