<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyAgenda;
use App\Models\DailyAgendaItem;
use App\Models\Setting;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Barryvdh\DomPDF\Facade\Pdf;

class EvidenceController extends Controller
{
    /**
     * SIPEGA: Menu Bukti Fisik (Laporan Realisasi & SKP)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $date = $request->get('date', Carbon::today('Asia/Makassar')->format('Y-m-d'));
        
        $agenda = DailyAgenda::with('items')
            ->where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        // Statistik Cepat
        $stats = [
            'total' => $agenda ? $agenda->items->count() : 0,
            'completed' => $agenda ? $agenda->items->where('status', 'completed')->count() : 0,
            'progress' => $agenda ? $agenda->items->where('status', 'progress')->count() : 0,
        ];

        return view('evidence.index', compact('agenda', 'user', 'date', 'stats'));
    }

    /**
     * Simpan Bukti Fisik (Foto & Teks) per Item Kegiatan
     */
    public function update(Request $request, $itemId)
    {
        $user = auth()->user();
        $now = Carbon::now('Asia/Makassar');
        
        $request->validate([
            'proof_text' => 'nullable|string',
            'proof_file' => 'nullable|image|max:3072', // Max 3MB
            'status' => 'required|in:completed,progress,changed',
            'workflow_phase' => 'required|in:Tujuan,Rencana,Prioritas,Kerja,Pantau,Evaluasi,Perbaiki',
            'evaluation_notes' => 'nullable|string',
            'improvement_plan' => 'nullable|string'
        ]);

        $item = DailyAgendaItem::findOrFail($itemId);
        $agenda = $item->dailyAgenda;

        if ($agenda->user_id !== $user->id) {
            abort(403);
        }

        $dbPath = $item->proof_file_path;

        if ($request->hasFile('proof_file')) {
            $file = $request->file('proof_file');
            $filename = 'evidence_' . $user->id . '_' . $itemId . '_' . time() . '.jpg';
            $path = storage_path('app/public/evidences/' . $filename);

            if (!file_exists(storage_path('app/public/evidences'))) {
                mkdir(storage_path('app/public/evidences'), 0777, true);
            }

            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            $image->scaleDown(width: 1200);

            // Watermark SIPEGA-Elite
            $watermarkText = "SIKLUS KERJA: " . strtoupper($request->workflow_phase) . "\nPegawai: " . $user->name . "\nWaktu: " . $now->format('d/m/Y H:i') . " WITA";
            
            $image->drawRectangle(0, 0, function ($rectangle) {
                $rectangle->size(1200, 100);
                $rectangle->background('rgba(0,0,0,0.6)');
            });

            $image->text($watermarkText, 30, 20, function($font) {
                $font->file(3); // Default font
                $font->color('#ffffff');
                $font->size(16);
                $font->valign('top');
                $font->lineHeight(1.4);
            });

            $image->toJpeg(75)->save($path);
            $dbPath = 'evidences/' . $filename;
        }

        $item->update([
            'status' => $request->status,
            'proof_text' => $request->proof_text,
            'proof_file_path' => $dbPath,
            'workflow_phase' => $request->workflow_phase,
            'evaluation_notes' => $request->evaluation_notes,
            'improvement_plan' => $request->improvement_plan,
            'realization_notes' => $request->proof_text // Sync for compatibility
        ]);

        // Auto-update parent status
        if ($agenda->items()->where('status', 'pending')->count() === 0) {
            $agenda->update(['realization_submitted_at' => $now]);
        }

        return back()->with('success', 'Bukti fisik berhasil diperbarui! 📄✅');
    }

    /**
     * Cetak Bukti Fisik / Laporan Realisasi SKP (PDF)
     */
    public function downloadPdf(Request $request)
    {
        $user = auth()->user();
        $date = $request->get('date', Carbon::today('Asia/Makassar')->format('Y-m-d'));
        $now = Carbon::now('Asia/Makassar');

        // Check 15:00 constraint (Optional, simplified)
        if ($date === $now->format('Y-m-d') && $now->hour < 15 && Setting::get('is_realization_open_anytime') !== '1') {
            // return back()->with('error', 'Laporan Realisasi baru dapat dicetak setelah jam 15:00 WITA.');
        }

        $agenda = DailyAgenda::with('items')
            ->where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        if (!$agenda) {
            return back()->with('error', 'Data agenda tidak ditemukan untuk tanggal tersebut.');
        }

        $pdf = Pdf::loadView('pdf.daily_report', [
            'agenda' => $agenda,
            'user' => $user,
            'date' => Carbon::parse($date)->translatedFormat('l, d F Y'),
            'print_time' => $now->format('d/m/Y H:i:s')
        ]);

        return $pdf->setPaper('a4', 'portrait')->download('LAPORAN_HARIAN_' . $user->name . '_' . $date . '.pdf');
    }
}
