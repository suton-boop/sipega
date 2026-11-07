<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiketConflict extends Model
{
    use HasFactory;

    protected $fillable = [
        'piket_schedule_assignment_id',
        'user_id',
        'conflict_date',
        'conflict_source',
        'reference_number',
        'activity_title',
        'activity_start_date',
        'activity_end_date',
        'status',
        'resolution_notes',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'conflict_date' => 'date',
        'activity_start_date' => 'date',
        'activity_end_date' => 'date',
        'resolved_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(PiketScheduleAssignment::class, 'piket_schedule_assignment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
