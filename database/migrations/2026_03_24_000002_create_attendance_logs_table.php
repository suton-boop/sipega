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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->integer('tl_minutes')->default(0); // Terlambat (TL)
            $table->integer('psw_minutes')->default(0); // Pulang Sebelum Waktunya (PSW)
            $table->string('proof_file_path')->nullable(); // Klaim lupa absen
            $table->enum('approval_status', ['None', 'Pending', 'Approved', 'Rejected'])->default('None');
            $table->enum('source', ['Mesin', 'ST', 'Manual'])->default('Manual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
