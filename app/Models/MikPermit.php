<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MikPermit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'employee_name',
        'employee_nip',
        'employee_position',
        'gugus_mutu',
        'date',
        'out_time',
        'return_time',
        'estimated_return_time',
        'destination',
        'purpose',
        'status',
        'responder_role',
        'responder_id',
        'responder_name',
        'responder_notes',
        'responded_at',
        'cancelled_at',
    ];

    protected $casts = [
        'date' => 'date',
        'responded_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    public function scopeKantor($query)
    {
        return $query->where('type', 'kantor');
    }

    public function scopeRuangan($query)
    {
        return $query->where('type', 'ruangan');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'belum direspon');
    }

    public function getFormattedOutTimeAttribute(): string
    {
        return $this->out_time ? substr($this->out_time, 0, 5) : '-';
    }

    public function getFormattedReturnTimeAttribute(): string
    {
        return $this->return_time ? substr($this->return_time, 0, 5) : '-';
    }

    public function getFormattedEstimatedReturnTimeAttribute(): string
    {
        return $this->estimated_return_time ? substr($this->estimated_return_time, 0, 5) : '-';
    }

    /**
     * Teks Rentang Jam (Waktu Keluar s.d Kembali / Estimasi)
     */
    public function getTimeRangeTextAttribute(): string
    {
        $out = $this->formatted_out_time;
        if ($this->return_time) {
            return "{$out} - " . substr($this->return_time, 0, 5);
        } elseif ($this->estimated_return_time) {
            return "{$out} - " . substr($this->estimated_return_time, 0, 5) . " (est)";
        }
        return "{$out} - s.d selesai";
    }

    /**
     * Hitung durasi waktu izin dalam Jam & Menit
     */
    public function getDurationTextAttribute(): string
    {
        if (!$this->out_time) {
            return '-';
        }

        $endTime = $this->return_time ?: $this->estimated_return_time;
        if (!$endTime) {
            return 'Sedang berlangsung';
        }

        try {
            $start = \Carbon\Carbon::createFromFormat('H:i:s', strlen($this->out_time) === 5 ? $this->out_time . ':00' : $this->out_time);
            $end = \Carbon\Carbon::createFromFormat('H:i:s', strlen($endTime) === 5 ? $endTime . ':00' : $endTime);

            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $diffMinutes = $start->diffInMinutes($end);
            $hours = intdiv($diffMinutes, 60);
            $mins = $diffMinutes % 60;

            if ($hours > 0 && $mins > 0) {
                return "{$hours} jam {$mins} mnt";
            } elseif ($hours > 0) {
                return "{$hours} jam";
            }
            return "{$mins} menit";
        } catch (\Throwable $e) {
            return '-';
        }
    }
}
