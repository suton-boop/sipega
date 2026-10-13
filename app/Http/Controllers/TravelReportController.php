<?php

namespace App\Http\Controllers;

use App\Models\TravelReport;
use App\Models\Letter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TravelReportController extends Controller
{
    /**
     * Tampilkan daftar laporan perjalanan dinas
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = TravelReport::with(['user', 'letter']);

        // Pegawai hanya bisa melihat laporan miliknya sendiri
        if ($user->role === 'Pegawai') {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('st_number', 'like', "%{$search}%")
                  ->orWhere('activity_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('employee_name', 'like', "%{$search}%");
            });
        }

        // Filter Tahun
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        $reports = $query->latest()->paginate(15)->withQueryString();

        // Statistik ringkas
        $baseQuery = TravelReport::query();
        if ($user->role === 'Pegawai') {
            $baseQuery->where('user_id', $user->id);
        }
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'this_month' => (clone $baseQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'this_year' => (clone $baseQuery)->whereYear('created_at', now()->year)->count(),
        ];

        $users = in_array($user->role, ['Admin', 'Pimpinan', 'Kasubag']) 
            ? User::realPegawai()->orderBy('name')->get() 
            : collect([$user]);

        return view('travel_reports.index', compact('reports', 'stats', 'users'));
    }

    /**
     * Form pembuatan laporan perjalanan dinas baru
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        // Ambil daftar surat tugas yang sudah disetujui (Approved) untuk referensi auto-fill
        if (in_array($user->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            $letters = Letter::where('status', 'Approved')->latest()->get();
        } else {
            $letters = $user->letters()->where('status', 'Approved')->latest()->get();
        }

        $selectedLetter = null;
        if ($request->filled('letter_id')) {
            $selectedLetter = Letter::with('users')->find($request->letter_id);
        }

        return view('travel_reports.create', compact('letters', 'selectedLetter', 'user'));
    }

    /**
     * Simpan laporan perjalanan dinas
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_name' => 'required|string|max:255',
            'employee_nip' => 'nullable|string|max:50',
            'employee_position' => 'nullable|string|max:255',
            'st_number' => 'required|string|max:255',
            'st_date' => 'nullable|date',
            'activity_name' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'date_range_text' => 'nullable|string|max:255',
            'location' => 'required|string',
            'purpose' => 'nullable|string',
            'target_audience' => 'nullable|string',
            'output' => 'nullable|string',
            'description' => 'required|string',
            'conclusion' => 'nullable|string',
            'signed_place' => 'nullable|string|max:100',
            'signed_date' => 'nullable|date',
            'signature_image' => 'nullable|image|max:5120',
            'photos.*' => 'nullable|image|max:10240',
        ]);

        $user = auth()->user();

        // 1. Upload Tanda Tangan jika ada
        $signaturePath = null;
        if ($request->hasFile('signature_image')) {
            $signaturePath = $request->file('signature_image')->store('travel_reports/signatures', 'public');
        } elseif ($request->filled('existing_signature_image')) {
            $signaturePath = $request->existing_signature_image;
        }

        // 2. Upload Foto Dokumentasi
        $photosList = [];

        // Cek jika ada foto yang diupload baru
        if ($request->hasFile('photos')) {
            $captions = $request->input('photo_captions', []);
            foreach ($request->file('photos') as $index => $photoFile) {
                if ($photoFile && $photoFile->isValid()) {
                    $path = $photoFile->store('travel_reports/photos', 'public');
                    $caption = $captions[$index] ?? '';
                    $photosList[] = [
                        'path' => $path,
                        'caption' => $caption,
                    ];
                }
            }
        }

        // Cek jika ada preset sample foto yang digunakan (misal dari template bawaan)
        if ($request->filled('preset_photos_json')) {
            $presetPhotos = json_decode($request->preset_photos_json, true);
            if (is_array($presetPhotos)) {
                $photosList = array_merge($photosList, $presetPhotos);
            }
        }

        $report = TravelReport::create([
            'user_id' => $user->id,
            'letter_id' => $request->letter_id,
            'employee_name' => $request->employee_name,
            'employee_nip' => $request->employee_nip,
            'employee_position' => $request->employee_position,
            'st_number' => $request->st_number,
            'st_date' => $request->st_date,
            'activity_name' => $request->activity_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'date_range_text' => $request->date_range_text,
            'location' => $request->location,
            'purpose' => $request->purpose,
            'target_audience' => $request->target_audience,
            'output' => $request->output,
            'description' => $request->description,
            'conclusion' => $request->conclusion,
            'signed_place' => $request->signed_place ?: 'Samarinda',
            'signed_date' => $request->signed_date ?: now()->toDateString(),
            'signature_image' => $signaturePath,
            'photos' => $photosList,
            'status' => 'Submitted',
        ]);

        // Integrasikan dengan modul Surat Tugas (letter_user pivot) & Kinerja (PerformanceService)
        if ($request->filled('letter_id')) {
            $letter = Letter::find($request->letter_id);
            if ($letter) {
                $pivotUpdate = [
                    'report_text' => $request->description,
                    'report_status' => 'Submitted',
                ];
                if (!empty($photosList[0]['path'])) {
                    $pivotUpdate['report_photo_1'] = $photosList[0]['path'];
                }
                if (!empty($photosList[1]['path'])) {
                    $pivotUpdate['report_photo_2'] = $photosList[1]['path'];
                }

                $letter->users()->updateExistingPivot($user->id, $pivotUpdate);
            }
        }

        return redirect()->route('travel-reports.show', $report->id)
            ->with('success', 'Laporan Perjalanan Dinas berhasil dibuat dan disimpan.');
    }

    /**
     * Tampilkan detail laporan
     */
    public function show($id)
    {
        $report = TravelReport::with(['user', 'letter'])->findOrFail($id);
        $user = auth()->user();

        // Pegawai hanya boleh melihat laporannya sendiri
        if ($user->role === 'Pegawai' && $report->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat laporan ini.');
        }

        return view('travel_reports.show', compact('report'));
    }

    /**
     * Form edit laporan
     */
    public function edit($id)
    {
        $report = TravelReport::with(['user', 'letter'])->findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'Pegawai' && $report->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit laporan ini.');
        }

        if (in_array($user->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            $letters = Letter::where('status', 'Approved')->latest()->get();
        } else {
            $letters = $user->letters()->where('status', 'Approved')->latest()->get();
        }

        return view('travel_reports.edit', compact('report', 'letters', 'user'));
    }

    /**
     * Update laporan
     */
    public function update(Request $request, $id)
    {
        $report = TravelReport::findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'Pegawai' && $report->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses mengubah laporan ini.');
        }

        $request->validate([
            'employee_name' => 'required|string|max:255',
            'employee_nip' => 'nullable|string|max:50',
            'employee_position' => 'nullable|string|max:255',
            'st_number' => 'required|string|max:255',
            'st_date' => 'nullable|date',
            'activity_name' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'date_range_text' => 'nullable|string|max:255',
            'location' => 'required|string',
            'purpose' => 'nullable|string',
            'target_audience' => 'nullable|string',
            'output' => 'nullable|string',
            'description' => 'required|string',
            'conclusion' => 'nullable|string',
            'signed_place' => 'nullable|string|max:100',
            'signed_date' => 'nullable|date',
            'signature_image' => 'nullable|image|max:5120',
            'photos.*' => 'nullable|image|max:10240',
        ]);

        // Update signature jika ada file baru
        $signaturePath = $report->signature_image;
        if ($request->hasFile('signature_image')) {
            $signaturePath = $request->file('signature_image')->store('travel_reports/signatures', 'public');
        } elseif ($request->filled('remove_signature') && $request->remove_signature == '1') {
            $signaturePath = null;
        }

        // Manajemen Foto
        $existingPhotos = $report->photos ?: [];
        
        // Cek jika ada foto eksisting yang dipertahankan dan diedit captionnya
        $updatedPhotos = [];
        $keepIndexes = $request->input('keep_photos', []);
        $updatedCaptions = $request->input('existing_photo_captions', []);

        foreach ($keepIndexes as $idx) {
            if (isset($existingPhotos[$idx])) {
                $updatedPhotos[] = [
                    'path' => $existingPhotos[$idx]['path'],
                    'caption' => $updatedCaptions[$idx] ?? ($existingPhotos[$idx]['caption'] ?? ''),
                ];
            }
        }

        // Tambah foto-foto baru
        if ($request->hasFile('photos')) {
            $newCaptions = $request->input('photo_captions', []);
            foreach ($request->file('photos') as $index => $photoFile) {
                if ($photoFile && $photoFile->isValid()) {
                    $path = $photoFile->store('travel_reports/photos', 'public');
                    $caption = $newCaptions[$index] ?? '';
                    $updatedPhotos[] = [
                        'path' => $path,
                        'caption' => $caption,
                    ];
                }
            }
        }

        $report->update([
            'letter_id' => $request->letter_id,
            'employee_name' => $request->employee_name,
            'employee_nip' => $request->employee_nip,
            'employee_position' => $request->employee_position,
            'st_number' => $request->st_number,
            'st_date' => $request->st_date,
            'activity_name' => $request->activity_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'date_range_text' => $request->date_range_text,
            'location' => $request->location,
            'purpose' => $request->purpose,
            'target_audience' => $request->target_audience,
            'output' => $request->output,
            'description' => $request->description,
            'conclusion' => $request->conclusion,
            'signed_place' => $request->signed_place ?: 'Samarinda',
            'signed_date' => $request->signed_date,
            'signature_image' => $signaturePath,
            'photos' => $updatedPhotos,
        ]);

        return redirect()->route('travel-reports.show', $report->id)
            ->with('success', 'Laporan Perjalanan Dinas berhasil diperbarui.');
    }

    /**
     * Hapus laporan
     */
    public function destroy($id)
    {
        $report = TravelReport::findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'Pegawai' && $report->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses menghapus laporan ini.');
        }

        $report->delete();

        return redirect()->route('travel-reports.index')
            ->with('success', 'Laporan Perjalanan Dinas berhasil dihapus.');
    }

    /**
     * Unduh dalam format PDF
     */
    public function downloadPdf($id)
    {
        $report = TravelReport::with(['user', 'letter'])->findOrFail($id);
        
        $pdf = Pdf::loadView('pdf.travel_report', compact('report'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'dpi' => 150,
            'defaultFont' => 'Bookman'
        ]);

        $cleanName = 'Laporan_Perjadin_' . str_replace([' ', '/', '\\', '.', ','], '_', $report->employee_name) . '_' . date('Ymd', strtotime($report->created_at));
        return $pdf->download($cleanName . '.pdf');
    }

    /**
     * Unduh dalam format DOC (Microsoft Word)
     */
    public function downloadDoc($id)
    {
        $report = TravelReport::with(['user', 'letter'])->findOrFail($id);

        // Siapkan gambar dalam format Base64 agar Word dapat menampilkan offline tanpa broken image
        $signatureBase64 = null;
        if (!empty($report->signature_image)) {
            $sigPath = public_path('storage/' . $report->signature_image);
            if (!file_exists($sigPath)) {
                $sigPath = public_path($report->signature_image);
            }
            if (file_exists($sigPath)) {
                $type = pathinfo($sigPath, PATHINFO_EXTENSION);
                $data = file_get_contents($sigPath);
                $signatureBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        // Siapkan foto dokumentasi base64
        $docPhotos = [];
        if (!empty($report->photos) && is_array($report->photos)) {
            foreach ($report->photos as $p) {
                $path = $p['path'] ?? '';
                $caption = $p['caption'] ?? '';
                $fullPath = public_path('storage/' . $path);
                if (!file_exists($fullPath)) {
                    $fullPath = public_path($path);
                }
                $base64 = null;
                if (file_exists($fullPath) && !is_dir($fullPath)) {
                    $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($fullPath);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
                $docPhotos[] = [
                    'base64' => $base64,
                    'caption' => $caption,
                ];
            }
        }

        $viewHtml = view('reports.word_travel_report', compact('report', 'signatureBase64', 'docPhotos'))->render();

        $cleanName = 'Laporan_Perjadin_' . str_replace([' ', '/', '\\', '.', ','], '_', $report->employee_name) . '_' . date('Ymd', strtotime($report->created_at)) . '.doc';

        return response($viewHtml, 200, [
            'Content-Type' => 'application/msword; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $cleanName . '"',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
