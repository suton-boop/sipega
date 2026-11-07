<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PiketPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'piket_schedule_id',
        'piket_unit_id',
        'document_number',
        'year',
        'title',
        'effective_date',
        'signatory_name',
        'signatory_nip',
        'signatory_position',
        'pdf_file_path',
        'version_tag',
    ];

    protected $casts = [
        'year' => 'integer',
        'effective_date' => 'date',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PiketSchedule::class, 'piket_schedule_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(PiketUnit::class, 'piket_unit_id');
    }
}
