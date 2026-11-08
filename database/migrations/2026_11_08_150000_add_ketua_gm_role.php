<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $ketuaGm = Role::firstOrCreate(['name' => 'Ketua GM', 'guard_name' => 'web']);

            // Default izin untuk level Ketua Gugus Mutu
            $permissions = [
                'approve_mik',
                'evaluate_agenda',
                'view_reports',
            ];

            foreach ($permissions as $p) {
                Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            }

            $ketuaGm->givePermissionTo($permissions);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable $e) {
            \Log::warning('Migration add_ketua_gm_role: ' . $e->getMessage());
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
