<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'Pegawai'");
        } catch (\Throwable $e) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('role', 50)->default('Pegawai')->change();
                });
            } catch (\Throwable $ex) {
                \Log::warning('Migration change_role_to_string_in_users_table: ' . $ex->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('Admin', 'Pimpinan', 'Kasubag', 'Pegawai', 'Operator', 'Ketua GM') NOT NULL DEFAULT 'Pegawai'");
        } catch (\Throwable $e) {}
    }
};
