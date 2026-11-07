<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiketReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'piket_schedule_assignment_id',
        'original_officer_id',
        'replacement_officer_id',
        'priority_level',
        'reason',
        'approval_status',
        'approved_by',
        'approved_at',
        'approver_notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(PiketScheduleAssignment::class, 'piket_schedule_assignment_id');
    }

    public function originalOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_officer_id');
    }

    public function replacementOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replacement_officer_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
