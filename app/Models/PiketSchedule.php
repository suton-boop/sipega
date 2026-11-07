<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PiketSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'piket_unit_id',
        'year',
        'month',
        'period_type',
        'title',
        'status',
        'revision_number',
        'start_date',
        'end_date',
        'created_by',
        'published_by',
        'published_at',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'published_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PiketUnit::class, 'piket_unit_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(PiketScheduleAssignment::class);
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(PiketPublication::class, 'id', 'piket_schedule_id');
    }
}
