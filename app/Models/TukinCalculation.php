<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TukinCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'period',
        'user_id',
        'nip',
        'name',
        'position',
        'golongan',
        'grade',
        'base_amount',
        'hadir_count',
        'dl_count',
        'cuti_count',
        'sakit_count',
        'alpa_count',
        'tl1_count',
        'tl2_count',
        'tl3_count',
        'tl4_count',
        'tl5_count',
        'psw1_count',
        'psw2_count',
        'psw3_count',
        'psw4_count',
        'psw5_count',
        'tkd_count',
        'tkp_count',
        'tbtk_count',
        'attendance_penalty_percent',
        'attendance_deduction',
        'performance_predicate',
        'performance_penalty_percent',
        'performance_deduction',
        'total_penalty_percent',
        'total_deduction',
        'bruto_after_deduction',
        'tax_percent',
        'tax_amount',
        'netto_amount',
        'bank_name',
        'account_number',
        'notes',
        'source',
        'created_by',
    ];

    protected $casts = [
        'base_amount' => 'float',
        'attendance_penalty_percent' => 'float',
        'attendance_deduction' => 'float',
        'performance_penalty_percent' => 'float',
        'performance_deduction' => 'float',
        'total_penalty_percent' => 'float',
        'total_deduction' => 'float',
        'bruto_after_deduction' => 'float',
        'tax_percent' => 'float',
        'tax_amount' => 'float',
        'netto_amount' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Hitung PPh 21 berdasarkan Golongan ASN
     * Golongan IV: 15%
     * Golongan III / PPPK Golongan IX s.d XVII: 5%
     * Golongan II / I / PPPK Golongan V: 0%
     */
    public static function getTaxRateByGolongan(?string $golongan): float
    {
        if (!$golongan) return 0.0;

        $rules = \App\Services\TukinCalculatorService::getRules();

        $g = strtoupper(trim($golongan));
        if (str_starts_with($g, 'IV') || str_contains($g, 'IV/') || str_contains($g, 'GOLONGAN IV') || str_contains($g, 'GOL. IV')) {
            return (float)($rules['tax_gol_4'] ?? 15.0);
        }

        if (
            str_starts_with($g, 'III') || str_contains($g, 'III/') || str_contains($g, 'GOLONGAN III') || str_contains($g, 'GOL. III') ||
            str_contains($g, 'GOLONGAN IX') || str_contains($g, 'GOL. IX') || $g === 'IX' ||
            str_contains($g, 'GOLONGAN X') || str_contains($g, 'GOL. X') || $g === 'X'
        ) {
            return (float)($rules['tax_gol_3'] ?? 5.0);
        }

        return (float)($rules['tax_gol_1_2'] ?? 0.0);
    }
}
