<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Http\Controllers\RolePermissionController;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Hubungkan cache agar tidak ada konflik
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definisikan Permisson (Fitur Utama SIPEGA)
        $permissions = array_keys(RolePermissionController::PERMISSIONS_METADATA);

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
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
            'view_reports',
            'evaluate_agenda',
            'approve_tukin',
            'approve_leaves',
            'approve_mik',
            'approve_piket',
        ]);

        $kasubag->syncPermissions([
            'view_private_st',
            'approve_claims',
            'manage_dispositions',
            'view_reports',
            'evaluate_agenda',
            'approve_tukin',
            'approve_leaves',
            'approve_mik',
            'approve_piket',
        ]);

        $operator->syncPermissions([
            'issue_st',
            'import_attendance',
            'manage_meetings',
            'manage_tukin',
            'manage_leave_balances',
            'manage_piket',
        ]);

        // Pegawai tidak memiliki permission administratif khusus. Fitur perorangan aktif mandiri.

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

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
