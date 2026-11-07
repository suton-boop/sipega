<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoEvaluationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'executed_at',
        'target_date',
        'trigger_type',
        'triggered_by',
        'triggered_by_name',
        'score_given',
        'total_evaluated',
        'feedback_given',
        'scope',
        'affected_summary',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
        'target_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
