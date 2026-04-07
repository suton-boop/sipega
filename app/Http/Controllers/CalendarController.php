<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CalendarEvent;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Daftar Kalender Kerja SIPEGA (Interactive UI)
     */
    public function index()
    {
        // Ambil semua event di tahun 2026 agar Dot status muncul semua
        $events = CalendarEvent::whereYear('date', 2026)->get();
        return view('admin.calendar.index', compact('events'));
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
