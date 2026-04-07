<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['voter_id', 'target_id', 'month_year', 'comment'])]
class Vote extends Model
{
    public function voter()
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
