<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRhk extends Model
{
    protected $fillable = [
        'user_id',
        'rhk_desc',
        'classification',
        'target_quarter',
        'year',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
