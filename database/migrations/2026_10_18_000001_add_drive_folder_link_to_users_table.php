<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'drive_folder_link')) {
                $table->string('drive_folder_link', 500)->nullable()->after('photo')
                      ->comment('Link Google Drive folder pegawai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'drive_folder_link')) {
                $table->dropColumn('drive_folder_link');
            }
        });
    }
};
