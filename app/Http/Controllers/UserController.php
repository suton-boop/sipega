<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan halaman User Management untuk Admin dengan Filter
     */
    public function index(Request $request)
    {
        // Pastikan hanya admin/kasubag/pimpinan yang bisa lihat (Role Based)
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan'])) {
            return abort(403, 'Akses Ditolak.');
        }

        $query = User::where('id', '!=', auth()->id());

        // 1. Filter Pencarian: Nama, NIP, Email, atau Jabatan
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('position', 'LIKE', "%{$search}%");
            });
        }

        // 2. Filter Peran (Role)
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // 3. Filter Status Aktif
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // 4. Filter Device Binding (Kunci HP)
        if ($request->filled('device') && $request->device !== 'all') {
            if ($request->device === 'locked') {
                $query->whereNotNull('device_id');
            } elseif ($request->device === 'unbound') {
                $query->whereNull('device_id');
            }
        }

        $totalUsers = User::where('id', '!=', auth()->id())->count();
        $users = $query->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'totalUsers'));
    }

    /**
     * Update data fleksibel: Role, Drive, Status, Reset Device
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'role' => ['required', Rule::in(['Admin', 'Pimpinan', 'Kasubag', 'Pegawai', 'Operator', 'Sekpri'])],
            'drive_folder_url' => 'nullable|url',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $user->photo = $path;
        }

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
    public function downloadTemplate(Request $request)
    {
        if ($request->get('format') === 'csv') {
            $headers = ['Nama', 'NIP', 'Jabatan', 'Golongan', 'KJ', 'Role', 'Email'];
            $filename = "Template_Import_Pegawai_SIPEGA.csv";
            
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, $headers, ';');
            fputcsv($handle, ['Ahmad Fauzi, S.Pd', "'198705122011011002", 'Widyaprada Ahli Muda', 'III/c', '9', 'Pegawai', 'ahmad.fauzi@bpmpkaltim.id'], ';');
            fputcsv($handle, ['Siti Rahmah, M.Pd', "'199003152014022003", 'Pengembang Penilaian Pendidikan', 'III/b', '8', 'Pegawai', 'siti.rahmah@bpmpkaltim.id'], ';');

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            fclose($handle);
            exit;
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EmployeeTemplateExport, 'Template_Import_Pegawai_SIPEGA.xlsx');
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
            'role' => ['required', Rule::in(['Admin', 'Pimpinan', 'Kasubag', 'Pegawai', 'Operator', 'Sekpri'])],
            'password' => 'required|string|min:8',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nip' => $request->nip,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'is_active' => true,
            'photo' => $photoPath,
        ]);

        $user->assignRole($request->role);

        return back()->with('success', "Pegawai {$user->name} berhasil ditambahkan ke sistem.");
    }

    /**
     * Hapus Data Pegawai (Khusus Level Admin)
     */
    public function destroy($id)
    {
        // 1. Otorisasi Ketat: Hanya role 'Admin' yang memiliki hak menghapus
        if (auth()->user()->role !== 'Admin') {
            return abort(403, 'Akses ditolak: Hanya Administrator yang berhak menghapus data pegawai.');
        }

        // 2. Cegah Admin menghapus akunnya sendiri
        if (auth()->id() == $id) {
            return back()->with('error', 'Tindakan Ditolak: Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user = User::findOrFail($id);
        $name = $user->name;
        $nip = $user->nip;

        // 3. Hapus foto profil dari disk jika ada
        if ($user->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo);
        }

        // 4. Hapus data pegawai (relasi di tabel lain akan cascade delete)
        $user->delete();

        return back()->with('success', "Pegawai {$name} (NIP: " . ($nip ?: '-') . ") berhasil dihapus secara permanen dari sistem.");
    }
}
