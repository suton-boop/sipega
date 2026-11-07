<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiketEligibleEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'piket_unit_id',
        'assignment_role',
        'is_available',
        'unavailability_reason',
        'max_shifts_per_month',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'max_shifts_per_month' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PiketUnit::class, 'piket_unit_id');
    }
}
