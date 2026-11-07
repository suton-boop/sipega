<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PiketUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'location',
        'description',
        'start_time',
        'end_time',
        'daily_officers_count',
        'daily_backups_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'daily_officers_count' => 'integer',
        'daily_backups_count' => 'integer',
    ];

    public function eligibleEmployees(): HasMany
    {
        return $this->hasMany(PiketEligibleEmployee::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(PiketSchedule::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(PiketScheduleAssignment::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(PiketPublication::class);
    }
}
