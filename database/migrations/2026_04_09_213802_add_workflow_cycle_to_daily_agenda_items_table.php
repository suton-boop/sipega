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
            $table->enum('workflow_phase', [
                'Tujuan', 'Rencana', 'Prioritas', 'Kerja', 'Pantau', 'Evaluasi', 'Perbaiki'
            ])->default('Kerja')->after('status');
            $table->text('evaluation_notes')->nullable()->after('proof_text');
            $table->text('improvement_plan')->nullable()->after('evaluation_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_agenda_items', function (Blueprint $table) {
            $table->dropColumn(['workflow_phase', 'evaluation_notes', 'improvement_plan']);
        });
    }
};
