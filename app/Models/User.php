<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role', 
        'position',      // Jabatan
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
        'is_active'
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

    public function letters()
    {
        return $this->belongsToMany(Letter::class, 'letter_user')
            ->withPivot('report_text', 'report_photo_1', 'report_photo_2', 'report_status')
            ->withTimestamps();
    }
}
