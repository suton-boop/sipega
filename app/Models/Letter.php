<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $fillable = [
        'type',
        'number',
        'title',
        'date_start',
        'date_end',
        'location',
        'file_pdf',
        'justification',
        'created_by',
        'status',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'letter_user')
            ->withPivot('report_text', 'report_photo_1', 'report_photo_2', 'report_status')
            ->withTimestamps();
    }
}
