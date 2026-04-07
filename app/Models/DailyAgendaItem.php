<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAgendaItem extends Model
{
    protected $fillable = [
        'daily_agenda_id',
        'plan_description',
        'status',
        'realization_notes'
    ];

    public function dailyAgenda(): BelongsTo
    {
        return $this->belongsTo(DailyAgenda::class);
    }
}
