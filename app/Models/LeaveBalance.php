<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'initial_quota',
        'used_quota',
        'remaining_quota',
        'remaining_n1',
        'remaining_n2',
        'cuti_besar_taken',
        'cuti_sakit_taken',
        'cuti_melahirkan_taken',
        'cuti_alasan_penting_taken',
        'cltn_taken',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dapatkan atau inisialisasi kuota cuti tahunan pegawai untuk tahun tertentu
     */
    public static function getOrCreateForUser(User $user, int $year): self
    {
        $balance = self::where('user_id', $user->id)
            ->where('year', $year)
            ->first();

        if ($balance) {
            return $balance;
        }

        // Ambil data tahun sebelumnya jika ada
        $prevBalance = self::where('user_id', $user->id)
            ->where('year', $year - 1)
            ->first();

        // Hitung carry over sesuai regulasi:
        // Cuti tahun sebelumnya yang tidak diambil dapat diambil pada tahun berjalan maksimal 6 hari kerja
        $n1 = 0;
        $n2 = 0;
        if ($prevBalance) {
            $n1 = min(6, max(0, $prevBalance->remaining_quota));
            $n2 = min(6, max(0, $prevBalance->remaining_n1));
        }

        // Jika Guru dan tidak ada penugasan khusus, hak cuti tahunan disesuaikan
        $isGuru = str_contains(strtolower($user->position ?? ''), 'guru');
        $initial = $isGuru ? 0 : 12;

        return self::create([
            'user_id' => $user->id,
            'year' => $year,
            'initial_quota' => $initial,
            'used_quota' => 0,
            'remaining_quota' => $initial,
            'remaining_n1' => $n1,
            'remaining_n2' => $n2,
        ]);
    }
}
