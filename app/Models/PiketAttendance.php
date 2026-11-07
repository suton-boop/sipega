<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiketAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'piket_schedule_assignment_id',
        'user_id',
        'date',
        'check_in',
        'check_out',
        'attendance_status',
        'visitors_served_count',
        'service_notes',
        'issues_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'visitors_served_count' => 'integer',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(PiketScheduleAssignment::class, 'piket_schedule_assignment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
