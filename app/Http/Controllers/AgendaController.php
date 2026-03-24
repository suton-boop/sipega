<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyAgenda;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AgendaController extends Controller
{
    /**
     * Store (Submit) Agenda Harian & Foto Bukti
     */
    public function store(Request $request)
    {
        $request->validate([
            'activity_plan' => 'required|string|min:5',
            'proof_file' => 'required|image|mimes:jpeg,png,jpg|max:5120' // Maksimal 5MB dari HP
        ]);

        $user = auth()->user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now('Asia/Makassar');

        // Cek Deadline Jam 17:00 (Hanya Status, tidak menolak input)
        $status = 'Submitted';
        if ($now->format('H:i') > '17:00') {
            $status = 'Late';
        }

        // Cek kalau udah pernah submit hari ini
        $existing = DailyAgenda::where('user_id', $user->id)->where('date', $today)->first();
        if ($existing) {
            return back()->with('error', 'Anda sudah men-submit Agenda untuk hari ini!');
        }

        try {
            // PROSES FOTO (SIPEGA-Report: Auto-Compress & Watermark)
            $file = $request->file('proof_file');
            $filename = 'agenda_' . $user->id . '_' . time() . '.jpg';
            $path = storage_path('app/public/agendas/' . $filename);

            // Buat folder jika belum ada
            if (!file_exists(storage_path('app/public/agendas'))) {
                mkdir(storage_path('app/public/agendas'), 0777, true);
            }

            // Gunakan GD Driver bawaan PHP via Intervention Image V3
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());

            // 1. Auto-Compress (Resize max width 1080px to save Hostinger memory)
            $image->scaleDown(width: 1080);

            // 2. Watermark Otentik
            $watermarkText = "SIPEGA BPMP KALTIM\n" . 
                             "Nama: " . $user->name . "\n" . 
                             "Waktu: " . $now->format('d/m/Y H:i:s') . " WITA";

            // Pasang kotak latar belakang hitam transparan untuk watermark agar terbaca di foto terang
            $image->drawRectangle(10, 10, function ($rectangle) {
                $rectangle->size(400, 70); // Lebar dan Tinggi kotak background
                $rectangle->background('rgba(0, 0, 0, 0.5)'); // Hitam 50%
            });

            // Tulis Teks Watermark di koordinat X:20 Y:30
            $image->text($watermarkText, 20, 30, function($font) {
                // Di V3, gunakan file() dengan font file atau built-in number 1-5 untuk default GD
                $font->file(3);   // Font bitmap bawaan PHP (Ukuran menengah)
                $font->color('#ffffff'); // Warna Teks Putih
                $font->align('left');
                $font->valign('top');
                $font->lineHeight(1.5);
            });

            // Simpan foto yg sudah tercompress (Quality 80%)
            $image->toJpeg(80)->save($path);

            $dbPath = 'agendas/' . $filename;

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses gambar: ' . $e->getMessage());
        }

        // SIMPAN KE DATABASE
        DailyAgenda::create([
            'user_id' => $user->id,
            'date' => $today,
            'activity_plan' => $request->activity_plan,
            'proof_file_path' => $dbPath,
            'submitted_at' => $now,
            'status' => $status
        ]);

        return back()->with('success', 'Agenda dan Foto Bukti berhasil diunggah! Skor Anda aman hari ini.');
    }
}
