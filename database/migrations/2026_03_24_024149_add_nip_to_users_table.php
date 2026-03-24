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
            $table->string('nip')->unique()->nullable()->after('email');
            $table->string('telegram_id')->nullable()->after('device_id');
        });

        Schema::table('assignment_letters', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published', 'completed'])->default('published')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'telegram_id']);
        });

        Schema::table('assignment_letters', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
