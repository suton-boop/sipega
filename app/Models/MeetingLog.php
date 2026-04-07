<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['meeting_id', 'user_id', 'check_in_time', 'check_in_lat', 'check_in_lng', 'is_valid'])]
class MeetingLog extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
