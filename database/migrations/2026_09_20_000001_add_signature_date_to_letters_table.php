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
            if (!Schema::hasColumn('letters', 'signature_date_type')) {
                $table->string('signature_date_type')->default('auto')->after('signatory_nip'); // 'auto' atau 'manual'
            }
            if (!Schema::hasColumn('letters', 'signed_at')) {
                $table->date('signed_at')->nullable()->after('signature_date_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            if (Schema::hasColumn('letters', 'signature_date_type')) {
                $table->dropColumn('signature_date_type');
            }
            if (Schema::hasColumn('letters', 'signed_at')) {
                $table->dropColumn('signed_at');
            }
        });
    }
};
