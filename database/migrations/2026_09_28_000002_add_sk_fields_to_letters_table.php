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
            if (!Schema::hasColumn('letters', 'sk_number')) {
                $table->string('sk_number', 150)->nullable()->after('number');
            }
            if (!Schema::hasColumn('letters', 'sk_date')) {
                $table->date('sk_date')->nullable()->after('sk_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            if (Schema::hasColumn('letters', 'sk_date')) {
                $table->dropColumn('sk_date');
            }
            if (Schema::hasColumn('letters', 'sk_number')) {
                $table->dropColumn('sk_number');
            }
        });
    }
};
