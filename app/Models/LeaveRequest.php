<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_number',
        'request_date',
        'employee_name',
        'employee_nip',
        'employee_position',
        'employee_status',
        'work_duration',
        'work_unit',
        'leave_type',
        'reason',
        'duration_amount',
        'duration_unit',
        'start_date',
        'end_date',
        'quota_n2',
        'year_n2',
        'quota_n1',
        'year_n1',
        'quota_n',
        'year_n',
        'note_cuti_besar',
        'note_cuti_sakit',
        'note_cuti_melahirkan',
        'note_cuti_alasan_penting',
        'note_cltn',
        'leave_address',
        'phone_number',
        'signature_applicant',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'supervisor_id',
        'supervisor_name',
        'supervisor_nip',
        'supervisor_position',
        'supervisor_decision',
        'supervisor_notes',
        'supervisor_action_at',
        'verifier_id',
        'verifier_name',
        'verifier_notes',
        'verified_at',
        'approver_id',
        'approver_name',
        'approver_nip',
        'approver_position',
        'approver_decision',
        'approver_notes',
        'approver_action_at',
        'status',
        'ai_validation_status',
        'ai_validation_summary',
        'ai_validation_details',
    ];

    protected $casts = [
        'request_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'supervisor_action_at' => 'datetime',
        'verified_at' => 'datetime',
        'approver_action_at' => 'datetime',
        'ai_validation_details' => 'array',
    ];

    public const LEAVE_TYPES = [
        'cuti_tahunan' => 'Cuti Tahunan',
        'cuti_besar' => 'Cuti Besar',
        'cuti_sakit' => 'Cuti Sakit',
        'cuti_melahirkan' => 'Cuti Melahirkan',
        'cuti_alasan_penting' => 'Cuti Karena Alasan Penting',
        'cuti_cltn' => 'Cuti di Luar Tanggungan Negara',
    ];

    public function getLeaveTypeLabelAttribute(): string
    {
        return self::LEAVE_TYPES[$this->leave_type] ?? ucwords(str_replace('_', ' ', $this->leave_type));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Scope untuk permohonan yang perlu ditinjau oleh atasan atau admin
     */
    public function scopePendingSupervisor($query, $userId)
    {
        return $query->where('supervisor_id', $userId)
            ->where('supervisor_decision', 'pending');
    }

    public function scopePendingApprover($query, $userId = null)
    {
        $q = $query->where('supervisor_decision', 'DISETUJUI')
            ->where('approver_decision', 'pending');
        if ($userId) {
            $q->where(function($sub) use ($userId) {
                $sub->where('approver_id', $userId)->orWhereNull('approver_id');
            });
        }
        return $q;
    }
}
