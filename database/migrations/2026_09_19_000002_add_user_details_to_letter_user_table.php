<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_user', function (Blueprint $table) {
            if (!Schema::hasColumn('letter_user', 'user_nip')) {
                $table->string('user_nip')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('letter_user', 'user_golongan')) {
                $table->string('user_golongan')->nullable()->after('user_nip');
            }
            if (!Schema::hasColumn('letter_user', 'user_position')) {
                $table->string('user_position')->nullable()->after('user_golongan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('letter_user', function (Blueprint $table) {
            $table->dropColumn(['user_nip', 'user_golongan', 'user_position']);
        });
    }
};
