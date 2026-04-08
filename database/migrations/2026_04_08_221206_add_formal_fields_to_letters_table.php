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
        Schema::table('letters', function (Blueprint $table) {
            $table->text('basis')->nullable()->after('title'); // Dasar: Berdasarkan nota dinas...
            $table->text('purpose')->nullable()->after('basis'); // Untuk: Untuk mengikuti...
            $table->string('signatory_name')->nullable();
            $table->string('signatory_nip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            //
        });
    }
};
