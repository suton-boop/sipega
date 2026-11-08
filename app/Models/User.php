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
        'profile_photo_path',
        'drive_folder_link',
        'status_kepegawaian',
        'unit_kerja',
        'tmt_cpns_pppk',
        'phone',
        'supervisor_id',
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
     * Kelas Jabatan / Grade Tukin Resmi (Perpres No. 18 Tahun 2025 / Permendikdasmen No. 14 Tahun 2025)
     */
    public const GRADES = [
        '17' => 'Grade 17 (Rp 33.240.000)',
        '16' => 'Grade 16 (Rp 27.577.500)',
        '15' => 'Grade 15 (Rp 19.280.000)',
        '14' => 'Grade 14 (Rp 17.064.000)',
        '13' => 'Grade 13 (Rp 10.936.000)',
        '12' => 'Grade 12 (Rp 9.896.000)',
        '11' => 'Grade 11 (Rp 8.757.600)',
        '10' => 'Grade 10 (Rp 5.979.200)',
        '9'  => 'Grade 9 (Rp 5.079.200)',
        '8'  => 'Grade 8 (Rp 4.595.150)',
        '7'  => 'Grade 7 (Rp 3.915.950)',
        '6'  => 'Grade 6 (Rp 3.510.400)',
        '5'  => 'Grade 5 (Rp 3.134.250)',
        '4'  => 'Grade 4 (Rp 2.985.000)',
        '3'  => 'Grade 3 (Rp 2.898.000)',
        '2'  => 'Grade 2 (Rp 2.708.250)',
        '1'  => 'Grade 1 (Rp 2.531.250)',
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

    public function travelReports()
    {
        return $this->hasMany(\App\Models\TravelReport::class);
    }

    public function dailyAgendas()
    {
        return $this->hasMany(DailyAgenda::class);
    }

    public function rhks()
    {
        return $this->hasMany(UserRhk::class)->orderBy('id', 'desc');
    }

    public function getDriveFolderUrlAttribute(): ?string
    {
        return $this->drive_folder_link ?: ($this->attributes['drive_folder_url'] ?? null);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /**
     * Hitung masa kerja pegawai berdasarkan TMT atau decode NIP resmi
     */
    public function getMasaKerjaText($asOfDate = null): string
    {
        $asOf = $asOfDate ? \Carbon\Carbon::parse($asOfDate) : now();

        $tmt = null;
        if (!empty($this->tmt_cpns_pppk)) {
            $tmt = \Carbon\Carbon::parse($this->tmt_cpns_pppk);
        } elseif (!empty($this->nip) && strlen(preg_replace('/\D/', '', $this->nip)) >= 14) {
            $cleanNip = preg_replace('/\D/', '', $this->nip);
            $year = substr($cleanNip, 8, 4);
            $month = substr($cleanNip, 12, 2);
            if (is_numeric($year) && is_numeric($month) && $year >= 1970 && $year <= 2030 && $month >= 1 && $month <= 12) {
                $tmt = \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1);
            }
        }

        if (!$tmt) {
            return '-';
        }

        $diff = $tmt->diff($asOf);
        $years = $diff->y;
        $months = $diff->m;

        $parts = [];
        if ($years > 0) {
            $parts[] = "{$years} tahun";
        }
        if ($months > 0 || empty($parts)) {
            $parts[] = "{$months} Bulan";
        }

        return implode(' ', $parts);
    }

    /**
     * Dapatkan masa kerja dalam tahun float (misal 5.5 tahun) untuk validasi syarat hak cuti
     */
    public function getMasaKerjaYears($asOfDate = null): float
    {
        $asOf = $asOfDate ? \Carbon\Carbon::parse($asOfDate) : now();

        $tmt = null;
        if (!empty($this->tmt_cpns_pppk)) {
            $tmt = \Carbon\Carbon::parse($this->tmt_cpns_pppk);
        } elseif (!empty($this->nip) && strlen(preg_replace('/\D/', '', $this->nip)) >= 14) {
            $cleanNip = preg_replace('/\D/', '', $this->nip);
            $year = substr($cleanNip, 8, 4);
            $month = substr($cleanNip, 12, 2);
            if (is_numeric($year) && is_numeric($month) && $year >= 1970 && $year <= 2030 && $month >= 1 && $month <= 12) {
                $tmt = \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1);
            }
        }

        if (!$tmt) {
            return 0.0;
        }

        return (float) $tmt->diffInDays($asOf) / 365.25;
    }

    /**
     * Cek status kepegawaian resmi (PNS atau PPPK)
     */
    public function getEffectiveStatusKepegawaianAttribute(): string
    {
        if (!empty($this->status_kepegawaian) && in_array(strtoupper($this->status_kepegawaian), ['PNS', 'PPPK'])) {
            return strtoupper($this->status_kepegawaian);
        }

        if ($this->golongan && (stripos($this->golongan, 'Golongan') !== false || stripos($this->golongan, 'PPPK') !== false)) {
            return 'PPPK';
        }

        return 'PNS';
    }

    /**
     * Memeriksa apakah user berhak memberikan persetujuan izin MiK (Ketua Gugus Mutu & Pimpinan/Admin)
     */
    public function canApproveMik(): bool
    {
        $role = strtolower(trim($this->role ?? ''));
        $position = strtolower(trim($this->position ?? ''));

        // Pimpinan & Admin
        if (in_array($role, ['admin', 'administrator', 'superadmin', 'pimpinan', 'kasubag'])) {
            return true;
        }

        if (str_contains($position, 'kepala') || str_contains($position, 'kasubag')) {
            return true;
        }

        // Ketua Gugus Mutu
        if (in_array($role, ['ketua gm', 'ketua gugus', 'ketua gugus mutu']) ||
            str_contains($position, 'ketua gugus') ||
            str_contains($position, 'ketua gm')) {
            return true;
        }

        // Pegawai dengan indikasi ketua gugus mutu di profil
        if (!empty($this->gugus_mutu) && (str_contains($position, 'ketua') || str_contains($role, 'ketua'))) {
            return true;
        }

        return false;
    }

    /**
     * Memeriksa apakah user berada di level Pimpinan (Kepala Balai, Kasubag, Admin)
     */
    public function isPimpinanOrAdmin(): bool
    {
        $role = strtolower(trim($this->role ?? ''));
        $position = strtolower(trim($this->position ?? ''));

        if (in_array($role, ['admin', 'administrator', 'superadmin', 'pimpinan', 'kasubag'])) {
            return true;
        }

        if (str_contains($position, 'kepala') || str_contains($position, 'kasubag')) {
            return true;
        }

        return false;
    }

    /**
     * Memeriksa apakah user berada di level Operator Kepegawaian (Operator, Kepegawaian, Admin)
     */
    public function isOperatorKepegawaian(): bool
    {
        $role = strtolower(trim($this->role ?? ''));
        $position = strtolower(trim($this->position ?? ''));

        $operatorRoles = ['admin', 'administrator', 'superadmin', 'operator', 'kepegawaian'];
        if (in_array($role, $operatorRoles)) {
            return true;
        }

        $operatorPositions = ['kepegawaian', 'sdm', 'operator', 'pengelola kepegawaian', 'analis sdm'];
        foreach ($operatorPositions as $pos) {
            if (str_contains($position, $pos)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Memeriksa apakah user berhak mengakses fitur Hitung Tukin
     * (Hanya ada di level Admin, Pimpinan, dan Kepegawaian)
     */
    public function canManageTukin(): bool
    {
        return $this->isOperatorKepegawaian() || $this->isPimpinanOrAdmin();
    }
}
