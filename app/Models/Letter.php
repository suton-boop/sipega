<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    protected $fillable = [
        'type',
        'st_model',
        'category',
        'number',
        'title',
        'date_start',
        'date_end',
        'location',
        'basis',
        'purpose',
        'invitation_from',
        'invitation_number',
        'invitation_date',
        'invitation_subject',
        'dipa_source',
        'show_keterangan',
        'break_closing_paragraph',
        'signatory_name',
        'signatory_nip',
        'signature_date_type',
        'signed_at',
        'file_pdf',
        'justification',
        'created_by',
        'status',
    ];

    protected $casts = [
        'show_keterangan' => 'boolean',
        'break_closing_paragraph' => 'boolean',
        'invitation_date' => 'date',
        'date_start' => 'date',
        'date_end' => 'date',
        'signed_at' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'letter_user')
            ->withPivot(
                'user_nip',
                'user_golongan',
                'user_position',
                'report_text', 
                'report_photo_1', 
                'report_photo_2', 
                'report_status',
                'custom_role',
                'keterangan',
                'city_destination',
                'venue',
                'execution_dates',
                'person_in_charge'
            )
            ->withTimestamps();
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'DLK' => 'Kegiatan Kantor (DLK)',
            'DLP' => 'Kegiatan Pusat (DLP)',
            'DLN' => 'Kegiatan Kemitraan (DLN)',
            default => 'Kegiatan Kantor (DLK)',
        };
    }

    public function getModelLabelAttribute(): string
    {
        return match($this->st_model) {
            'model_1' => 'Model 1 (1 Orang)',
            'model_2' => 'Model 2 (>1 Orang)',
            'model_3' => 'Model 3 (Daring)',
            'model_4' => 'Model 4 (Lampiran Matriks)',
            'model_5' => 'Model 5 (Kolom Keterangan)',
            default => 'Model 1 (1 Orang)',
        };
    }
}
