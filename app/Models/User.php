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
