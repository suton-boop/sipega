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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ST', 'SK'])->default('ST')->comment('Surat Tugas atau Surat Keputusan');
            $table->string('number')->nullable();
            $table->string('title');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->string('location')->nullable();
            $table->string('file_pdf')->nullable();
            $table->text('justification')->nullable()->comment('Alasan pimpinan menunjuk pegawai di bawah target performa');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['Draft', 'Pending', 'Approved', 'Rejected'])->default('Draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
