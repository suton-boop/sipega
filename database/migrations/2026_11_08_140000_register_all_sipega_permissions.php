<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\RolePermissionController;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $permissions = array_keys(RolePermissionController::PERMISSIONS_METADATA);

            foreach ($permissions as $p) {
                Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            }

            $admin = Role::firstOrCreate(['name' => 'Admin']);
            $pimpinan = Role::firstOrCreate(['name' => 'Pimpinan']);
            $kasubag = Role::firstOrCreate(['name' => 'Kasubag']);
            $operator = Role::firstOrCreate(['name' => 'Operator']);
            Role::firstOrCreate(['name' => 'Pegawai']);

            // Admin memiliki semua permission
            $admin->syncPermissions($permissions);

            // Default untuk pimpinan & kasubag
            $leaderPermissions = [
                'view_private_st',
                'approve_claims',
                'manage_dispositions',
                'view_reports',
                'evaluate_agenda',
                'approve_tukin',
                'approve_leaves',
                'approve_mik',
                'approve_piket',
            ];
            $pimpinan->givePermissionTo($leaderPermissions);
            $kasubag->givePermissionTo($leaderPermissions);

            // Default untuk operator
            $operatorPermissions = [
                'issue_st',
                'import_attendance',
                'manage_meetings',
                'manage_tukin',
                'manage_leave_balances',
                'manage_piket',
            ];
            $operator->givePermissionTo($operatorPermissions);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable $e) {
            \Log::warning('Migration register_all_sipega_permissions: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
