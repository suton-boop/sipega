<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = [
        'user_id', 
        'date', 
        'check_in', 
        'check_out', 
        'tl_minutes', 
        'psw_minutes', 
        'status',
        'notes',
        'proof_file_path', 
        'approval_status', 
        'source'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
