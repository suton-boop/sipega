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
        'proof_file_path', 
        'submitted_at', 
        'status'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
