<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role', 
        'position',      // Jabatan
        'gugus_mutu',    // Gugus Mutu (GM 1 s.d GM 5)
        'golongan',      // Pangkat/Golongan
        'nip', 
        'grade',         // KJ
        'device_id', 
        'performance_score', 
        'performance_color', 
        'performance_predicate',
        'telegram_id', 
        'base_tukin', 
        'job_class_id', 
        'is_active',
        'photo',
        'profile_photo_path'
    ];

    protected $hidden = [
        'password', 
        'remember_token',
    ];

    public const PANGKAT_GOLONGAN = [
        'I/a'           => 'Juru Muda, I/a',
        'I/b'           => 'Juru Muda Tingkat I, I/b',
        'I/c'           => 'Juru, I/c',
        'I/d'           => 'Juru Tingkat I, I/d',
        'II/a'          => 'Pengatur Muda, II/a',
        'II/b'          => 'Pengatur Muda Tingkat I, II/b',
        'II/c'          => 'Pengatur, II/c',
        'II/d'          => 'Pengatur Tingkat I, II/d',
        'III/a'         => 'Penata Muda, III/a',
        'III/b'         => 'Penata Muda Tingkat I, III/b',
        'III/c'         => 'Penata, III/c',
        'III/d'         => 'Penata Tingkat I, III/d',
        'IV/a'          => 'Pembina, IV/a',
        'IV/b'          => 'Pembina Tingkat I, IV/b',
        'IV/c'          => 'Pembina Utama Muda, IV/c',
        'IV/d'          => 'Pembina Utama Madya, IV/d',
        'IV/e'          => 'Pembina Utama, IV/e',
        'Golongan V'    => 'Golongan V',
        'Golongan VI'   => 'Golongan VI',
        'Golongan VII'  => 'Golongan VII',
        'Golongan VIII' => 'Golongan VIII',
        'Golongan IX'   => 'Golongan IX',
        'Golongan X'    => 'Golongan X',
    ];

    /**
     * Konversi atau standarisasi teks golongan/ruang menjadi Pangkat, Golongan resmi (PNS & PPPK)
     */
    public static function formatPangkatGolongan(?string $value): ?string
    {
        if (empty($value) || trim($value) === '' || trim($value) === '-') {
            return null;
        }

        $clean = trim($value);

        // Jika sudah persis salah satu nilai resmi
        if (in_array($clean, self::PANGKAT_GOLONGAN)) {
            return $clean;
        }

        // 1. Cek format PPPK (misal: "Golongan IX", "Gol. IX", "Gol IX", "PPPK Golongan IX", "IX")
        foreach (['VIII', 'VII', 'VI', 'IX', 'X', 'V'] as $roman) {
            // Hindari tabrakan dengan ruang PNS seperti IV/a
            if (!preg_match('/' . $roman . '\/[a-e]/i', $clean)) {
                if (
                    preg_match('/(?:^|[,\s])gol(?:ongan|\.)?\s*' . $roman . '(?:\b|$)/i', $clean) ||
                    preg_match('/(?:^|[,\s])pppk\b.*' . $roman . '(?:\b|$)/i', $clean) ||
                    strcasecmp($clean, $roman) === 0 ||
                    strcasecmp($clean, 'Golongan ' . $roman) === 0 ||
                    strcasecmp($clean, 'Gol. ' . $roman) === 0
                ) {
                    return 'Golongan ' . $roman;
                }
            }
        }

        // 2. Cek jika persis kode ruang/golongan (case-insensitive, misal "IV/a", "iii/c")
        foreach (self::PANGKAT_GOLONGAN as $code => $full) {
            if (strcasecmp($clean, $code) === 0 || strcasecmp($clean, $full) === 0) {
                return $full;
            }
        }

        // 3. Cek akhiran kode didahului pemisah (koma atau spasi), urutkan kode terpanjang dahulu (III/.. -> II/.. -> I/..)
        $sorted = self::PANGKAT_GOLONGAN;
        uksort($sorted, fn($a, $b) => strlen($b) <=> strlen($a));

        foreach ($sorted as $code => $full) {
            if (preg_match('/(?:^|[,\s])' . preg_quote($code, '/') . '$/i', $clean)) {
                return $full;
            }
        }

        // 4. Cek jika mengandung nama pangkat PNS
        foreach ($sorted as $code => $full) {
            $pangkatName = trim(explode(',', $full)[0]);
            if (stripos($clean, $pangkatName) !== false) {
                return $full;
            }
        }

        return $clean;
    }

    /**
     * Accessor untuk Pangkat & Golongan terformat
     */
    public function getPangkatGolonganAttribute(): ?string
    {
        return self::formatPangkatGolongan($this->golongan);
    }

    public function jobClass()
    {
        return $this->belongsTo(JobClass::class);
    }

    public function calculateMonthlyTukin($monthYear = null)
    {
        return (new \App\Services\TukinService())->calculateForUser($this, $monthYear);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function receivedVotes()
    {
        return $this->hasMany(Vote::class, 'target_id');
    }

    public function givenVotes()
    {
        return $this->hasMany(Vote::class, 'voter_id');
    }

    public function assignmentLetters()
    {
        return $this->belongsToMany(AssignmentLetter::class, 'assignment_letter_user');
    }

    public function scopeRealPegawai($query)
    {
        return $query->whereNotIn('role', ['Sekpri', 'Admin', 'Administrator', 'Pimpinan', 'Kasubag'])
            ->where('name', 'not like', '%administrator%')
            ->where('email', 'not like', '%admin%');
    }

    public function isRealPegawai(): bool
    {
        return !in_array($this->role, ['Sekpri', 'Admin', 'Administrator', 'Pimpinan', 'Kasubag'])
            && stripos($this->name, 'administrator') === false
            && stripos($this->email, 'admin') === false;
    }

    public function letters()
    {
        return $this->belongsToMany(Letter::class, 'letter_user')
            ->withPivot(
                'user_nip',
                'user_golongan',
                'user_position',
                'report_text', 
                'report_photo_1', 
                'report_photo_2', 
                'report_status', 
                'custom_role', 
                'keterangan', 
                'city_destination', 
                'venue', 
                'execution_dates', 
                'person_in_charge'
            )
            ->withTimestamps();
    }
}
