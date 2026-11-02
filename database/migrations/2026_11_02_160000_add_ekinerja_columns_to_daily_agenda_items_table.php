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
        Schema::table('daily_agenda_items', function (Blueprint $table) {
            $table->string('start_time', 10)->nullable()->after('workflow_phase');
            $table->string('end_time', 10)->nullable()->after('start_time');
            $table->text('rhk_text')->nullable()->after('end_time');
            $table->text('drive_link')->nullable()->after('proof_text');
            $table->string('drive_file_id')->nullable()->after('drive_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_agenda_items', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'rhk_text', 'drive_link', 'drive_file_id']);
        });
    }
};
