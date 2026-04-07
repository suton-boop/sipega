<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['letter_number', 'title', 'description', 'justification', 'date', 'is_private', 'type', 'created_by', 'report_path', 'report_submitted_at'])]
class AssignmentLetter extends Model
{
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
            'date' => 'date',
        ];
    }
}
