<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'letter_id',
        'employee_name',
        'employee_nip',
        'employee_position',
        'st_number',
        'st_date',
        'activity_name',
        'start_date',
        'end_date',
        'date_range_text',
        'location',
        'purpose',
        'target_audience',
        'output',
        'description',
        'conclusion',
        'signed_place',
        'signed_date',
        'signature_image',
        'photos',
        'status',
    ];

    protected $casts = [
        'st_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_date' => 'date',
        'photos' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    /**
     * Format tanggal rentang kegiatan (contoh: 29 September s.d 1 Oktober 2026)
     */
    public function getFormattedDateRangeAttribute(): string
    {
        if (!empty($this->date_range_text)) {
            return $this->date_range_text;
        }

        if (!$this->start_date) {
            return '-';
        }

        $bulanIndo = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $startD = $this->start_date->day;
        $startM = $bulanIndo[$this->start_date->month];
        $startY = $this->start_date->year;

        if (!$this->end_date || $this->start_date->isSameDay($this->end_date)) {
            return "{$startD} {$startM} {$startY}";
        }

        $endD = $this->end_date->day;
        $endM = $bulanIndo[$this->end_date->month];
        $endY = $this->end_date->year;

        if ($startY === $endY && $startM === $endM) {
            return "{$startD} s.d {$endD} {$startM} {$startY}";
        } elseif ($startY === $endY) {
            return "{$startD} {$startM} s.d {$endD} {$endM} {$startY}";
        } else {
            return "{$startD} {$startM} {$startY} s.d {$endD} {$endM} {$endY}";
        }
    }

    /**
     * Format tempat & tanggal tanda tangan (contoh: Samarinda, 3 Oktober 2026)
     */
    public function getFormattedSignedDateAttribute(): string
    {
        $place = $this->signed_place ?: 'Samarinda';
        if (!$this->signed_date) {
            return $place;
        }

        $bulanIndo = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $d = $this->signed_date->day;
        $m = $bulanIndo[$this->signed_date->month];
        $y = $this->signed_date->year;

        return "{$place}, {$d} {$m} {$y}";
    }
}
