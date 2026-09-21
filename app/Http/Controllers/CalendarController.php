<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use App\Models\User;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Daftar Kalender Kerja SIPEGA (Interactive UI dengan penandaan Dinas Luar)
     */
    public function index(Request $request)
    {
        $year = (int)$request->get('year', 2026);
        $currentMonthIdx = (int)$request->get('month', now()->month);

        // Ambil semua event di tahun terpilih (Hari libur, cuti bersama, dll)
        $events = CalendarEvent::whereYear('date', $year)->get();

        // Pegawai yang dilihat jadwal tugas individunya
        // Khusus Admin/Pimpinan/Kasubag dapat memilih pegawai lain, pegawai biasa melihat jadwal diri sendiri
        if (in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            $selectedUserId = $request->get('user_id', auth()->id());
            $targetUser = User::find($selectedUserId) ?? auth()->user();
        } else {
            $targetUser = auth()->user();
        }

        // Ambil seluruh Surat Tugas Approved untuk pegawai ini di tahun terpilih
        $dutyLetters = $targetUser->letters()
            ->where('status', 'Approved')
            ->where(function($q) use ($year) {
                $q->whereYear('date_start', $year)
                  ->orWhereYear('date_end', $year);
            })
            ->get();

        // Petakan setiap tanggal Dinas Luar ke array map: 'YYYY-MM-DD' => [list data tugas]
        $dutyDates = [];
        $monthDutyCount = 0;
        foreach ($dutyLetters as $letter) {
            if (!$letter->date_start) continue;
            $cur = Carbon::parse($letter->date_start)->copy();
            $end = Carbon::parse($letter->date_end ?? $letter->date_start)->copy();
            while ($cur->lte($end)) {
                $dateStr = $cur->toDateString();
                if ($cur->year == $year && $cur->month == $currentMonthIdx) {
                    $monthDutyCount++;
                }
                if (!isset($dutyDates[$dateStr])) {
                    $dutyDates[$dateStr] = [];
                }
                $dutyDates[$dateStr][] = [
                    'id' => $letter->id,
                    'number' => $letter->number ?? 'Tanpa Nomor',
                    'title' => $letter->title,
                    'location' => $letter->location ?? 'Lokasi Penugasan',
                    'category' => $letter->category,
                    'category_label' => $letter->category_label,
                    'dates' => Carbon::parse($letter->date_start)->translatedFormat('d M Y') . ($letter->date_end ? ' s.d ' . Carbon::parse($letter->date_end)->translatedFormat('d M Y') : ''),
                ];
                $cur->addDay();
            }
        }

        // Daftar seluruh pegawai untuk dropdown filter jika user adalah Admin/Pimpinan/Kasubag
        $allUsers = in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']) 
            ? User::orderBy('name')->get() 
            : collect([auth()->user()]);

        return view('admin.calendar.index', compact('events', 'year', 'currentMonthIdx', 'targetUser', 'dutyDates', 'allUsers', 'monthDutyCount'));
    }

    /**
     * Tambah/Update Status Hari Kalender
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:Working Day,Shared Leave,Holiday,Overtime',
            'description' => 'required|string'
        ]);

        CalendarEvent::updateOrCreate(
            ['date' => $request->date],
            ['type' => $request->type, 'description' => $request->description]
        );

        return back()->with('success', 'Kalender SIPEGA berhasil diperbarui.');
    }

    /**
     * SIPEGA: Import Kalender dari Excel/CSV (Bulk)
     */
    public function import(Request $request)
    {
        $request->validate(['excel_file' => 'required|mimes:xlsx,xls,csv']);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\CalendarImport, $request->file('excel_file'));
            return back()->with('success', 'Kalender SIPEGA berhasil di-import secara massal.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
