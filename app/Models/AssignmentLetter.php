<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['letter_number', 'title', 'description', 'date', 'is_private', 'type', 'created_by'])]
class AssignmentLetter extends Model
{
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
            'date' => 'date',
        ];
    }
}
