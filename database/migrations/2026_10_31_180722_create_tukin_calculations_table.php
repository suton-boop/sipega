<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tukin_calculations', function (Blueprint $table) {
            $table->id();
            $table->string('period', 7)->index(); // YYYY-MM (e.g. 2026-09)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nip')->index();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('golongan')->nullable();
            $table->string('grade', 10)->nullable();
            $table->decimal('base_amount', 15, 2)->default(0);

            // Log Counts
            $table->integer('hadir_count')->default(0);
            $table->integer('dl_count')->default(0);
            $table->integer('cuti_count')->default(0);
            $table->integer('sakit_count')->default(0);
            $table->integer('alpa_count')->default(0);

            // Pelanggaran Jam Kerja (Permendikdasmen 14/2025)
            $table->integer('tl1_count')->default(0);
            $table->integer('tl2_count')->default(0);
            $table->integer('tl3_count')->default(0);
            $table->integer('tl4_count')->default(0);
            $table->integer('tl5_count')->default(0);
            $table->integer('psw1_count')->default(0);
            $table->integer('psw2_count')->default(0);
            $table->integer('psw3_count')->default(0);
            $table->integer('psw4_count')->default(0);
            $table->integer('psw5_count')->default(0);
            $table->integer('tkd_count')->default(0);
            $table->integer('tkp_count')->default(0);
            $table->integer('tbtk_count')->default(0);

            // Perhitungan Persentase & Rupiah
            $table->decimal('attendance_penalty_percent', 6, 2)->default(0);
            $table->decimal('attendance_deduction', 15, 2)->default(0);

            $table->string('performance_predicate')->default('Baik');
            $table->decimal('performance_penalty_percent', 6, 2)->default(0);
            $table->decimal('performance_deduction', 15, 2)->default(0);

            $table->decimal('total_penalty_percent', 6, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('bruto_after_deduction', 15, 2)->default(0);

            // Pajak PPh 21
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);

            // Netto Akhir
            $table->decimal('netto_amount', 15, 2)->default(0);

            // Info Pembayaran
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('notes')->nullable();
            $table->string('source')->default('system'); // 'excel_import', 'system_recalc', 'manual'
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['period', 'nip']);
        });

        // Update Job Classes ke Standar Resmi Perpres 18/2025 & Permendikdasmen 14/2025
        $officialRates = [
            '17' => 33240000,
            '16' => 27577500,
            '15' => 19280000,
            '14' => 17064000,
            '13' => 10936000,
            '12' => 9896000,
            '11' => 8757600,
            '10' => 5979200,
            '9'  => 5079200,
            '8'  => 4595150,
            '7'  => 3915950,
            '6'  => 3510400,
            '5'  => 3134250,
            '4'  => 2985000,
            '3'  => 2898000,
            '2'  => 2708250,
            '1'  => 2531250,
        ];

        foreach ($officialRates as $grade => $amount) {
            DB::table('job_classes')->updateOrInsert(
                ['class_name' => (string)$grade],
                ['base_amount' => $amount, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tukin_calculations');
    }
};
