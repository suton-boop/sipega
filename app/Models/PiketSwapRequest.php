<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiketSwapRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'requester_assignment_id',
        'target_user_id',
        'target_assignment_id',
        'requester_date',
        'target_date',
        'reason',
        'target_user_agreement',
        'agreed_at',
        'admin_approval_status',
        'approved_by',
        'approved_at',
        'admin_notes',
    ];

    protected $casts = [
        'requester_date' => 'date',
        'target_date' => 'date',
        'agreed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function requesterAssignment(): BelongsTo
    {
        return $this->belongsTo(PiketScheduleAssignment::class, 'requester_assignment_id');
    }

    public function targetAssignment(): BelongsTo
    {
        return $this->belongsTo(PiketScheduleAssignment::class, 'target_assignment_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
