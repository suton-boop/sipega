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
        Schema::create('travel_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('letter_id')->nullable()->constrained()->onDelete('set null');
            
            // Identitas Pegawai
            $table->string('employee_name');
            $table->string('employee_nip')->nullable();
            $table->string('employee_position')->nullable();
            $table->string('st_number');
            $table->date('st_date')->nullable();
            
            // Kegiatan
            $table->text('activity_name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('date_range_text')->nullable();
            $table->text('location');
            $table->longText('purpose')->nullable();
            $table->text('target_audience')->nullable();
            $table->text('output')->nullable();
            
            // Isi Laporan
            $table->longText('description');
            $table->longText('conclusion')->nullable();
            
            // Penandatanganan
            $table->string('signed_place')->default('Samarinda');
            $table->date('signed_date')->nullable();
            $table->string('signature_image')->nullable();
            
            // Dokumentasi Foto (JSON: [{path, caption}])
            $table->json('photos')->nullable();
            
            $table->string('status')->default('Submitted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_reports');
    }
};
