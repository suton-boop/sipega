<?php

namespace App\Http\Controllers;

use App\Exports\TukinExport;
use App\Models\User;
use App\Models\JobClass;
use App\Models\Setting;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\TukinService;
use Barryvdh\DomPDF\Facade\Pdf;

class TukinController extends Controller
{
    /**
     * Dashboard Tukin Utama
     */
    public function index()
    {
        if (Setting::get('is_tukin_active') === '0') {
            return redirect()->route('dashboard')->with('error', 'Modul Tukin sedang dinonaktifkan oleh Administrator.');
        }

        $user = auth()->user();
        if (in_array($user->role, ['Admin', 'Kasubag', 'Pimpinan', 'Sekpri'])) {
            $users = User::realPegawai()->with('jobClass')->orderBy('name')->get();
            $jobClasses = JobClass::orderBy('base_amount', 'desc')->get();
            return view('tukin.index', compact('users', 'jobClasses'));
        } else {
            $tukin = $user->calculateMonthlyTukin();
            return view('tukin.pegawai', compact('user', 'tukin'));
        }
    }

    /**
     * Download Individual Tukin Slip (PDF)
     */
    public function downloadSlip($id = null)
    {
        $targetUser = $id ? User::findOrFail($id) : auth()->user();
        if (auth()->id() !== $targetUser->id && !in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Sekpri'])) {
            abort(403);
        }
        $tukin = (new TukinService())->calculateForUser($targetUser);
        $user = $targetUser;
        return Pdf::loadView('pdf.tukin_slip', compact('user', 'tukin'))
                  ->download("Slip_Tukin_{$user->name}_" . now()->format('M_Y') . ".pdf");
    }

    /**
     * Download FORMAL Payment List (100% Identical to User Screenshot)
     */
    public function downloadPaymentList()
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Sekpri'])) {
            abort(403);
        }
        $users = User::realPegawai()->with('jobClass')->orderBy('name', 'asc')->get();
        $month = Carbon::now('Asia/Makassar')->translatedFormat('F Y');
        return Pdf::loadView('pdf.tukin_payment_list', compact('users', 'month'))
                  ->setPaper('a4', 'landscape')
                  ->download("Daftar_Pembayaran_Tukin_" . date('Y_m_d') . ".pdf");
    }

    /**
     * Download DETAILED Recap (Lampiran Keuangan A3)
     */
    public function downloadRecap()
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Sekpri'])) {
            abort(403);
        }
        $users = User::realPegawai()->with('jobClass')->orderBy('name', 'asc')->get();
        $month = Carbon::now('Asia/Makassar')->translatedFormat('F Y');
        return Pdf::loadView('pdf.tukin_recap', compact('users', 'month'))
                  ->setPaper('a3', 'landscape')
                  ->download("Rekap_Detail_Tukin_Keuangan_" . date('Y_m_d') . ".pdf");
    }

    /**
     * Manage Job Classes
     */
    public function classes()
    {
        $jobClasses = JobClass::orderBy('base_amount', 'desc')->get();
        return view('tukin.job_classes', compact('jobClasses'));
    }

    public function storeClass(Request $request)
    {
        JobClass::create($request->validate([
            'class_name' => 'required|string',
            'base_amount' => 'required|numeric'
        ]));
        return back()->with('success', 'Kelas Jabatan berhasil ditambahkan.');
    }

    public function export()
    {
        $monthName = Carbon::now('Asia/Makassar')->translatedFormat('F_Y');
        return Excel::download(new TukinExport, "Rekap_Tukin_SIPEGA_{$monthName}.xlsx");
    }
}
