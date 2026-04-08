<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan halaman User Management untuk Admin
     */
    public function index(Request $request)
    {
        // Pastikan hanya admin/kasubag/pimpinan yang bisa lihat (Role Based)
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan'])) {
            return abort(403, 'Akses Ditolak.');
        }

        // Ambil semua user kecuali superadmin sendiri
        $users = User::where('id', '!=', auth()->id())->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Update data fleksibel: Role, Drive, Status, Reset Device
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'role' => ['required', Rule::in(['Admin', 'Pimpinan', 'Kasubag', 'Pegawai', 'Operator'])],
            'drive_folder_url' => 'nullable|url',
        ]);

        $user->role = $request->role;
        $user->drive_folder_url = $request->drive_folder_url;
        
        // Handle Switch Toggle "Status Aktif"
        $user->is_active = $request->has('is_active');

        // Handle Fitur "Reset Device Binding"
        if ($request->has('reset_device') && $request->reset_device == '1') {
            $user->device_id = null; // Membuka kunci HP
        }
        
        // Handle "Reset Password"
        if ($request->has('reset_password') && $request->reset_password == '1') {
            $user->password = Hash::make('12345678'); // Default reset password
        }

        $user->save();

        return back()->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    /**
     * Kalkulasi ulang skor performa seluruh pegawai
     */
    public function recalculatePerformance()
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan'])) {
            return abort(403);
        }

        $service = new \App\Services\PerformanceService();
        $users = User::all();
        
        foreach ($users as $user) {
            $service->updateScore($user);
        }

        return back()->with('success', 'Skor performa seluruh pegawai berhasil di-kalkulasi ulang berdasarkan bobot adil SIPEGA.');
    }

    /**
     * Import Data Pegawai dari Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\UserImport, $request->file('file'));
            return back()->with('success', 'Data pegawai berhasil di-import dari Excel.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal meng-import data. Pastikan format kolom sudah benar. Error: ' . $e->getMessage());
        }
    }

    /**
     * Download Template Excel Pegawai
     */
    public function downloadTemplate()
    {
        $headers = ['Nama', 'Email', 'Password', 'Role', 'NIP', 'Jabatan', 'Golongan', 'KJ'];
        $filename = "Template_Import_Pegawai_SIPEGA.csv";
        
        $handle = fopen('php://output', 'w');
        fputcsv($handle, $headers);
        
        // Contoh Data dengan tanda kutip di depan NIP agar Excel menganggapnya teks
        fputcsv($handle, ['Budi Santoso', 'budi@sipega.com', 'sipega123', 'Pegawai', "'198501012010011001", 'Penyusun Laporan', 'III/a', '7']);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        fclose($handle);
        exit;
    }

    /**
     * Simpan Pegawai Baru Secara Manual
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nip' => 'required|string|unique:users',
            'role' => ['required', Rule::in(['Admin', 'Pimpinan', 'Kasubag', 'Pegawai', 'Operator'])],
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nip' => $request->nip,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        $user->assignRole($request->role);

        return back()->with('success', "Pegawai {$user->name} berhasil ditambahkan ke sistem.");
    }
}
