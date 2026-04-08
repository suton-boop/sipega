<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\MeetingLog;
use App\Models\MeetingParticipant;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class MeetingController extends Controller
{
    /**
     * Daftar Hadir Kegiatan (SIPEGA-Presence)
     */
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today()->format('Y-m-d');
        $myMeetings = MeetingLog::with('meeting')->where('user_id', $user->id)->latest()->get();
        
        if (in_array($user->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator'])) {
            $availableMeetings = Meeting::latest()->get();
        } else {
            // Tampilkan rapat di mana pegawai diundang ATAU rapat yang ditujukan untuk SEMUA
            $invitedMeetingIds = MeetingParticipant::where('user_id', $user->id)->pluck('meeting_id');
            $availableMeetings = Meeting::whereIn('id', $invitedMeetingIds)
                ->orWhere('target_type', 'All')
                ->latest()
                ->get();
        }

        $locations = \App\Models\Location::orderBy('name', 'asc')->get();
        $allUsers = \App\Models\User::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('attendance.index', compact('myMeetings', 'availableMeetings', 'user', 'locations', 'allUsers'));
    }

    /**
     * Tampilkan QR Code Absensi Rapat Dinamis
     */
    public function showQr($id)
    {
        $meeting = Meeting::findOrFail($id);
        $meeting->current_qr_token = \Str::random(32);
        $meeting->save();

        $qrData = json_encode([
            'id' => $meeting->id,
            'token' => $meeting->current_qr_token,
            'lat' => $meeting->gps_lat,
            'lng' => $meeting->gps_lng,
            'radius' => $meeting->geofence_radius ?? 50
        ]);

        $qrCode = QrCode::size(400)->color(0, 51, 102)->format('svg')->generate($qrData);
        return view('admin.meetings.qr', compact('meeting', 'qrCode'));
    }

    /**
     * Endpoint API/Web Check-in (Scan QR)
     * Ditambah Logika Kedisiplinan Rapat
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required|exists:meetings,id',
            'token' => 'required',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'device_id' => 'required'
        ]);

        $user = auth()->user();
        if ($user->device_id && $user->device_id !== $request->device_id) {
            return response()->json(['success' => false, 'message' => 'SIPEGA: Akun terdaftar di perangkat lain.'], 403);
        }
        
        if (!$user->device_id) $user->update(['device_id' => $request->device_id]);

        $meeting = Meeting::findOrFail($request->meeting_id);
        
        // Validasi Jam Buka/Tutup
        $nowTime = now()->format('H:i:s');
        if ($meeting->open_time && $nowTime < $meeting->open_time) {
            return response()->json(['success' => false, 'message' => "Presensi SIPEGA belum dibuka."], 403);
        }
        if ($meeting->close_time && $nowTime > $meeting->close_time) {
            return response()->json(['success' => false, 'message' => "Presensi SIPEGA sudah ditutup."], 403);
        }

        if ($meeting->current_qr_token !== $request->token) {
            return response()->json(['success' => false, 'message' => 'QR Code kedaluwarsa.'], 403);
        }

        // Geofence Check
        $distance = $this->calculateDistance($meeting->gps_lat, $meeting->gps_lng, $request->lat, $request->lng);
        if ($distance > ($meeting->geofence_radius ?? 100)) {
            return response()->json(['success' => false, 'message' => "Posisi terlalu jauh (" . round($distance, 1) . "m). SIPEGA melacak Anda berada " . round($distance - ($meeting->geofence_radius ?? 100), 1) . "m di luar area yang diizinkan."], 403);
        }

        // --- LOGIKA KEDISIPLINAN RAPAT SIPEGA ---
        $startTime = Carbon::parse($meeting->date . ' ' . $meeting->start_time);
        $checkInTime = now();
        $diffMinutes = $startTime->diffInMinutes($checkInTime, false); // Positif jika terlambat
        
        // Jika datang sebelum rapat, skor 100. Jika telat, potong skor.
        $disciplineScore = 100;
        if ($diffMinutes > 0) {
            $disciplineScore = max(0, 100 - $diffMinutes); // Potong 1 poin per menit telat
        }

        // 3. Simpan Log & Update Status Peserta
        MeetingLog::updateOrCreate(
            ['meeting_id' => $meeting->id, 'user_id' => $user->id],
            [
                'check_in_time' => $checkInTime,
                'check_in_lat' => $request->lat,
                'check_in_lng' => $request->lng,
                'is_valid' => true
            ]
        );

        // Update Tabel Peserta Rapat (Jika ada di daftar undangan)
        MeetingParticipant::updateOrCreate(
            ['meeting_id' => $meeting->id, 'user_id' => $user->id],
            [
                'status' => 'Hadir',
                'discipline_score' => $disciplineScore,
                'remark' => $diffMinutes > 0 ? "Terlambat {$diffMinutes} Menit" : "Tepat Waktu"
            ]
        );

        return response()->json(['success' => true, 'message' => "Presensi SIPEGA Berhasil. Skor Disiplin: {$disciplineScore}."]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $latFrom = deg2rad($lat1); $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2); $lonTo = deg2rad($lon2);
        $angle = 2 * asin(sqrt(pow(sin(($latTo - $latFrom) / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin(($lonTo - $lonFrom) / 2), 2)));
        return $angle * $earthRadius;
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'location_name' => 'required',
            'target_type' => 'required|in:All,Specific'
        ]);

        $meeting = Meeting::create([
            'title' => $request->title,
            'target_type' => $request->target_type,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'location_name' => $request->location_name,
            'agenda' => $request->agenda,
            'gps_lat' => $request->lat,
            'gps_lng' => $request->lng,
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'geofence_radius' => $request->geofence_radius ?? 100,
            'created_by' => auth()->id()
        ]);

        // Logika Pendaftaran Peserta (Invitations)
        if ($request->target_type === 'Specific' && $request->has('user_ids')) {
            foreach ($request->user_ids as $userId) {
                MeetingParticipant::create([
                    'meeting_id' => $meeting->id,
                    'user_id' => $userId,
                    'status' => 'Undangan',
                    'is_mandatory' => true
                ]);
            }
        }

        return back()->with('success', 'Kegiatan SIPEGA berhasil didaftarkan dengan target: ' . ($request->target_type === 'All' ? 'Seluruh Pegawai' : 'Undangan Terbatas'));
    }

    /**
     * Baru: Mengundang Peserta ke Rapat
     */
    public function invite(Request $request, $id)
    {
        $meeting = Meeting::findOrFail($id);
        $request->validate(['user_ids' => 'required|array']);

        foreach ($request->user_ids as $userId) {
            MeetingParticipant::updateOrCreate(
                ['meeting_id' => $meeting->id, 'user_id' => $userId],
                ['status' => 'Undangan', 'is_mandatory' => true]
            );
        }

        return back()->with('success', 'Peserta berhasil diundang ke rapat SIPEGA.');
    }

    public function update(Request $request, $id)
    {
        $meeting = Meeting::findOrFail($id);
        $meeting->update($request->all());
        return back()->with('success', 'Data diperbarui.');
    }

    public function downloadAttendance($id)
    {
        $meeting = Meeting::with(['logs.user', 'participants.user'])->findOrFail($id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.meeting_attendance', compact('meeting'));
        return $pdf->download('Daftar_Hadir_' . $meeting->id . '.pdf');
    }

    /**
     * Baru: Download Notulensi Rapat SIPEGA
     */
    public function downloadMinutes($id)
    {
        $meeting = Meeting::findOrFail($id);
        if (!$meeting->minutes_text) return back()->with('error', 'Notulensi belum diisi.');
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.meeting_minutes', compact('meeting'));
        return $pdf->download('Notulensi_' . $meeting->id . '.pdf');
    }

    /**
     * Baru: Print QR Code Absensi
     */
    public function printQr($id)
    {
        $meeting = Meeting::findOrFail($id);
        $qrData = json_encode(['id' => $meeting->id, 'token' => $meeting->current_qr_token]);
        $qrCode = QrCode::size(500)->color(0, 51, 102)->format('svg')->generate($qrData);
        
        return view('admin.meetings.print_qr', compact('meeting', 'qrCode'));
    }
}
