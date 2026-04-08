<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Hubungkan cache agar tidak ada konflik
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definisikan Permisson (Fitur Utama SIPEGA)
        $permissions = [
            'manage_users',         // Manajemen Pegawai & RBAC
            'issue_st',             // Terbitkan Surat Tugas
            'import_attendance',    // Impor Excel Mesin Absen
            'view_private_st',      // Lihat Surat Tugas Rahasia
            'approve_claims',      // Setujui Lupa Absen
            'manage_dispositions',  // Kelola Instruksi Pimpinan
            'view_reports',         // Lihat Laporan Performa Global
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // 2. Definisikan Roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $pimpinan = Role::firstOrCreate(['name' => 'Pimpinan']);
        $kasubag = Role::firstOrCreate(['name' => 'Kasubag']);
        $operator = Role::firstOrCreate(['name' => 'Operator']);
        $pegawai = Role::firstOrCreate(['name' => 'Pegawai']);

        // 3. Mapping Permission ke Roles (Default Awal)
        $admin->syncPermissions($permissions); // Admin punya semua
        
        $pimpinan->syncPermissions([
            'view_private_st',
            'approve_claims',
            'manage_dispositions',
            'view_reports'
        ]);

        $kasubag->syncPermissions([
            'view_private_st',
            'approve_claims',
            'manage_dispositions'
        ]);

        $operator->syncPermissions([
            'issue_st',
            'import_attendance'
        ]);

        // 4. Buat Akun Admin Utama (Jika belum ada)
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@sipega.com'],
            [
                'name' => 'Administrator SIPEGA',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'role' => 'Admin',
                'is_active' => true,
                'nip' => '1234567890',
                'position' => 'System Administrator'
            ]
        );

        $adminUser->assignRole('Admin');
    }
}
