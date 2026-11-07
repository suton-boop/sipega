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
        Schema::create('auto_evaluation_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('executed_at');
            $table->date('target_date');
            $table->string('trigger_type')->default('manual_instant'); // cron_24h, manual_instant, etc.
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('triggered_by_name')->nullable();
            $table->integer('score_given')->default(90);
            $table->integer('total_evaluated')->default(0);
            $table->text('feedback_given')->nullable();
            $table->string('scope')->default('all_unrated');
            $table->text('affected_summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_evaluation_logs');
    }
};
