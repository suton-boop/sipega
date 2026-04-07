<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['title', 'target_type', 'is_pleno', 'date', 'start_time', 'open_time', 'close_time', 'location_name', 'gps_lat', 'gps_lng', 'geofence_radius', 'current_qr_token', 'agenda', 'minutes_text', 'minutes_file_path', 'created_by', 'is_active'])]
class Meeting extends Model
{
    public function logs()
    {
        return $this->hasMany(MeetingLog::class);
    }

    public function participants()
    {
        return $this->hasMany(MeetingParticipant::class);
    }
}
