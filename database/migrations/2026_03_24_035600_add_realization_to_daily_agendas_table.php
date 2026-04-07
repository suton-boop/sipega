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
        Schema::table('daily_agendas', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_agendas', 'activity_realization')) {
                $table->text('activity_realization')->nullable();
            }
            if (!Schema::hasColumn('daily_agendas', 'change_reason')) {
                $table->text('change_reason')->nullable(); 
            }
            if (!Schema::hasColumn('daily_agendas', 'realization_submitted_at')) {
                $table->timestamp('realization_submitted_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_agendas', function (Blueprint $table) {
            $table->dropColumn(['activity_realization', 'change_reason', 'realization_submitted_at']);
        });
    }
};
