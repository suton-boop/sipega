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
        if (Schema::hasTable('tukin_calculations')) {
            Schema::table('tukin_calculations', function (Blueprint $table) {
                if (!Schema::hasColumn('tukin_calculations', 'is_approved')) {
                    $table->boolean('is_approved')->default(false)->after('netto_amount');
                }
                if (!Schema::hasColumn('tukin_calculations', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('is_approved');
                }
                if (!Schema::hasColumn('tukin_calculations', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tukin_calculations')) {
            Schema::table('tukin_calculations', function (Blueprint $table) {
                if (Schema::hasColumn('tukin_calculations', 'approved_by')) {
                    $table->dropForeign(['approved_by']);
                    $table->dropColumn('approved_by');
                }
                if (Schema::hasColumn('tukin_calculations', 'approved_at')) {
                    $table->dropColumn('approved_at');
                }
                if (Schema::hasColumn('tukin_calculations', 'is_approved')) {
                    $table->dropColumn('is_approved');
                }
            });
        }
    }
};
