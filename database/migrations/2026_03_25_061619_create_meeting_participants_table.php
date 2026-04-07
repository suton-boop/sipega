<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['Hadir', 'Izin', 'Tanpa Keterangan', 'Undangan'])->default('Undangan');
            $table->string('remark')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->decimal('discipline_score', 5, 2)->nullable(); // Point reduction if needed
            $table->timestamps();
            
            $table->unique(['meeting_id', 'user_id']); // One user per meeting
        });

        // Add late_threshold to meetings
        Schema::table('meetings', function (Blueprint $table) {
            $table->integer('late_threshold_minutes')->default(15);
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('late_threshold_minutes');
        });
        Schema::dropIfExists('meeting_participants');
    }
};
