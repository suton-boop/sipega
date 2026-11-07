<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PiketScheduleAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'piket_schedule_id',
        'piket_unit_id',
        'date',
        'primary_officer_id',
        'backup_officer_id',
        'active_officer_id',
        'role_label',
        'status',
        'has_conflict',
        'conflict_type',
        'conflict_description',
        'conflict_reference_number',
        'is_replaced',
        'replacement_id',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'has_conflict' => 'boolean',
        'is_replaced' => 'boolean',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PiketSchedule::class, 'piket_schedule_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PiketUnit::class, 'piket_unit_id');
    }

    public function primaryOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_officer_id');
    }

    public function backupOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'backup_officer_id');
    }

    public function activeOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'active_officer_id');
    }

    public function replacement(): BelongsTo
    {
        return $this->belongsTo(PiketReplacement::class, 'replacement_id');
    }

    public function conflicts(): HasMany
    {
        return $this->hasMany(PiketConflict::class);
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(PiketAttendance::class);
    }
}
