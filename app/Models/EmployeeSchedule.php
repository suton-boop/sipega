<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSchedule extends Model
{
    protected $fillable = ['user_id', 'title', 'date', 'start_time', 'location', 'remark'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
