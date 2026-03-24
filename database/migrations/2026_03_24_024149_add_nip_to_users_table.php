<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip')->unique()->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'telegram_id')) {
                $table->string('telegram_id')->nullable()->after('device_id');
            }
        });

        Schema::table('assignment_letters', function (Blueprint $table) {
            if (!Schema::hasColumn('assignment_letters', 'status')) {
                $table->enum('status', ['draft', 'published', 'completed'])->default('published')->after('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
