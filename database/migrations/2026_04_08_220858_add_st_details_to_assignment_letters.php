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
        Schema::table('assignment_letters', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('date');
            $table->text('basis')->nullable()->after('title'); // Dasar Penugasan
            $table->string('location')->nullable()->after('end_date');
            $table->text('purpose')->nullable()->after('basis'); // Untuk mengikuti...
            $table->string('signatory_name')->nullable();
            $table->string('signatory_nip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_letters', function (Blueprint $table) {
            //
        });
    }
};
