<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAgenda extends Model
{
    protected $fillable = [
        'user_id', 
        'date', 
        'activity_plan', 
        'activity_realization', 
        'change_reason',
        'proof_file_path', 
        'submitted_at', 
        'realization_submitted_at',
        'status',
        'evaluated_at',
        'leader_rating',
        'leader_feedback',
        'evaluated_by'
    ];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyAgendaItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
