<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * Definisi Metadata Lengkap untuk Seluruh Fitur (Permissions) di SIPEGA
     */
    public const PERMISSIONS_METADATA = [
        'manage_users' => [
            'label' => 'Manajemen Pegawai & Akun',
            'desc'  => 'Izin kelola data pegawai, biodata, jabatan, golongan, dan akun pengguna sistem.',
            'group' => 'Kepegawaian & Pengguna',
        ],
        'manage_rbac' => [
            'label' => 'Manajemen Hak Akses (RBAC)',
            'desc'  => 'Izin mengatur matriks peran dan konfigurasi hak akses fitur sistem.',
            'group' => 'Kepegawaian & Pengguna',
        ],
        'manage_settings' => [
            'label' => 'Smart Pengaturan & Regulasi',
            'desc'  => 'Konfigurasi aturan aplikasi, integrasi Google Drive, dan sakelar menu sistem.',
            'group' => 'Sistem & Konfigurasi',
        ],
        'issue_st' => [
            'label' => 'Penerbitan Surat Tugas & SK',
            'desc'  => 'Buat, edit, dan terbitkan Surat Tugas (ST) serta Surat Keputusan (SK) dinas.',
            'group' => 'Persuratan & Penugasan',
        ],
        'view_private_st' => [
            'label' => 'Akses Surat Tugas Rahasia',
            'desc'  => 'Melihat dan mengunduh ST/SK kedinasan yang bersifat rahasia dan internal pimpinan.',
            'group' => 'Persuratan & Penugasan',
        ],
        'import_attendance' => [
            'label' => 'Impor Log Presensi Mesin',
            'desc'  => 'Unggah berkas Excel log presensi mesin dan sinkronisasi kehadiran harian.',
            'group' => 'Presensi & Kedisiplinan',
        ],
        'approve_claims' => [
            'label' => 'Persetujuan Klaim Lupa Absen',
            'desc'  => 'Verifikasi dan setujui pengajuan klaim lupa absen atau perbaikan presensi.',
            'group' => 'Presensi & Kedisiplinan',
        ],
        'manage_meetings' => [
            'label' => 'Kelola Rapat & Presensi QR',
            'desc'  => 'Jadwalkan rapat dinas, terbitkan QR code presensi digital, dan buat notula.',
            'group' => 'Presensi & Kedisiplinan',
        ],
        'manage_dispositions' => [
            'label' => 'Kelola Disposisi Pimpinan',
            'desc'  => 'Buat, teruskan, dan pantau instruksi serta lembar disposisi pimpinan.',
            'group' => 'Layanan Pimpinan',
        ],
        'view_reports' => [
            'label' => 'Lihat Laporan & Rekap Global',
            'desc'  => 'Akses laporan performa pegawai, rekapitulasi dinas luar, dan statistik satker.',
            'group' => 'Laporan & Kinerja',
        ],
        'evaluate_agenda' => [
            'label' => 'Evaluasi & Penilaian Agenda',
            'desc'  => 'Menilai, mengoreksi, dan menyetujui laporan realisasi agenda harian pegawai.',
            'group' => 'Laporan & Kinerja',
        ],
        'manage_tukin' => [
            'label' => 'Hitung & Kelola Tukin',
            'desc'  => 'Kalkulasi, impor berkas dan rekapitulasi Tukin Permendikdasmen 14/2025.',
            'group' => 'Tunjangan Kinerja (Tukin)',
        ],
        'approve_tukin' => [
            'label' => 'Approve Final Tukin Periode',
            'desc'  => 'Persetujuan final perhitungan Tukin bulanan sebelum cetak slip resmi (PDF).',
            'group' => 'Tunjangan Kinerja (Tukin)',
        ],
        'approve_leaves' => [
            'label' => 'Persetujuan Cuti ASN',
            'desc'  => 'Verifikasi dan berikan persetujuan permohonan cuti tahunan, sakit, dll.',
            'group' => 'Layanan Cuti ASN',
        ],
        'manage_leave_balances' => [
            'label' => 'Kelola Saldo & Kuota Cuti',
            'desc'  => 'Atur dan perbarui kuota sisa saldo cuti tahunan seluruh pegawai satker.',
            'group' => 'Layanan Cuti ASN',
        ],
        'approve_mik' => [
            'label' => 'Persetujuan Izin MiK',
            'desc'  => 'Persetujuan permohonan izin keluar kantor dan keluar ruangan kerja pegawai.',
            'group' => 'Monitoring Izin (MiK)',
        ],
        'manage_piket' => [
            'label' => 'Kelola Jadwal & Petugas Piket',
            'desc'  => 'Pengaturan jadwal tahunan, penetapan petugas, dan unit layanan (ULT & Perpustakaan).',
            'group' => 'Piket Pelayanan (SIM SPP)',
        ],
        'approve_piket' => [
            'label' => 'Persetujuan Tukar Jadwal Piket',
            'desc'  => 'Verifikasi dan persetujuan pengajuan pergantian atau tukar jadwal piket layanan.',
            'group' => 'Piket Pelayanan (SIM SPP)',
        ],
    ];

    /**
     * Tampilkan Matriks Hak Akses (RBAC)
     */
    public function index()
    {
        // 1. Bersihkan cache Spatie terlebih dahulu agar data selalu segar
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable $e) {}

        // 2. Pastikan seluruh permission sistem dan role terdaftar otomatis di database
        try {
            foreach (array_keys(self::PERMISSIONS_METADATA) as $permName) {
                Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            }

            // Pastikan role-role standar SIPEGA tersedia
            $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'Pimpinan', 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'Kasubag', 'guard_name' => 'web']);
            $ketuaGmRole = Role::firstOrCreate(['name' => 'Ketua GM', 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'Operator', 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => 'Pegawai', 'guard_name' => 'web']);

            // Berikan default permission awal untuk Ketua GM jika belum ada
            if ($ketuaGmRole->permissions()->count() === 0) {
                $ketuaGmRole->givePermissionTo(['approve_mik', 'evaluate_agenda', 'view_reports']);
            }

            // Pastikan role Admin selalu memiliki semua permissions
            if ($adminRole) {
                $allPerms = Permission::all();
                $adminRole->syncPermissions($allPerms);
            }
        } catch (\Throwable $e) {}

        // Urutkan peran secara hierarkis: Admin -> Pimpinan -> Kasubag -> Ketua GM -> Operator -> Pegawai
        $roleOrder = ['Admin', 'Pimpinan', 'Kasubag', 'Ketua GM', 'Operator', 'Pegawai'];
        $roles = Role::where('name', '!=', 'SuperAdmin')
            ->get()
            ->sortBy(function($r) use ($roleOrder) {
                $idx = array_search($r->name, $roleOrder);
                return $idx !== false ? $idx : 99;
            });
        
        // Urutkan permissions sesuai urutan di PERMISSIONS_METADATA
        $allPermissions = Permission::all()->keyBy('name');
        $permissions = collect();
        foreach (array_keys(self::PERMISSIONS_METADATA) as $name) {
            if ($allPermissions->has($name)) {
                $permissions->push($allPermissions->get($name));
            }
        }
        // Tambahkan permissions lain yang mungkin ada di DB di luar metadata
        foreach ($allPermissions as $name => $perm) {
            if (!isset(self::PERMISSIONS_METADATA[$name])) {
                $permissions->push($perm);
            }
        }

        $metadata = self::PERMISSIONS_METADATA;
        
        return view('admin.rbac.index', compact('roles', 'permissions', 'metadata'));
    }

    /**
     * Reset Matriks Hak Akses ke Konfigurasi Default Seeder
     */
    public function resetDefaults()
    {
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'RolePermissionSeeder',
                '--force' => true,
            ]);
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            return back()->with('success', 'Matriks Hak Akses (RBAC) berhasil di-reset dan disinkronkan ke setelan default awal.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal reset permissions: ' . $e->getMessage());
        }
    }

    /**
     * Sinkronisasi Hak Akses via Checkbox
     */
    public function sync(Request $request)
    {
        $roles = Role::all();
        foreach ($roles as $role) {
            // Ambil array permission untuk role ini dari request
            $rolePermissions = $request->input('permissions.' . $role->id, []);
            
            // Sync dengan spatie (akan menghapus yang tidak ada di array dan menambah yang baru)
            $role->syncPermissions($rolePermissions);
        }

        // Reset cache permission agar langsung berlaku seketika
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', 'Matriks Hak Akses (RBAC) SIPEGA berhasil diperbarui secara profesional.');
    }
}
