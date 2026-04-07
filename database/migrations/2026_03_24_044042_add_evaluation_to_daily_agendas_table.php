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
            $table->timestamp('evaluated_at')->nullable();
            $table->integer('leader_rating')->nullable();
            $table->text('leader_feedback')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('daily_agendas', function (Blueprint $table) {
            $table->dropForeign(['evaluated_by']);
            $table->dropColumn(['evaluated_at', 'leader_rating', 'leader_feedback', 'evaluated_by']);
        });
    }
};
