<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AttendanceImport;

class AttendanceController extends Controller
{
    /**
     * SIPEGA Check - Import Excel Absensi Mingguan Mesin (Admin)
     */
    public function importExcel(Request $request)
    {
        ini_set('max_execution_time', 300); // Hostinger Optimization
        
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:10240' // Max 10MB
        ]);

        try {
            // Eksekusi Excel Engine Import (Sudah dilindungi Logika Anti-Tertindih DL)
            Excel::import(new AttendanceImport, $request->file('excel_file'));

            return back()->with('success_import', 'Mesin Excel berhasil disinkronisasi ke SIPEGA! Status DL Pegawai yang sedang tugas telah dipertahankan.');
        } catch (\Exception $e) {
            return back()->withErrors(['excel_file' => 'Format file tidak sesuai blueprint atau isi corrupt: ' . $e->getMessage()]);
        }
    }
}
