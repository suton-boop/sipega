<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyAgenda;
use App\Models\DailyAgendaItem;
use App\Models\UserRhk;
use App\Models\Setting;
use App\Services\GoogleDriveService;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AgendaController extends Controller
{
    /**
     * Halaman Utama Agenda & Realisasi Kerja (SIPEGA-Agenda + e-Kinerja Companion)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        
        $myAgendaToday = DailyAgenda::with('items')
            ->where('user_id', $user->id)
            ->where('date', $selectedDate)
            ->first();

        // Get upcoming meetings/agenda for suggestions
        $upcomingMeetings = \App\Models\Meeting::where('date', $selectedDate)
            ->where(function($query) use ($user) {
                $query->where('target_type', 'All')
                      ->orWhereHas('participants', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->get();

        // Get individual schedules for suggestions (Agenda Mandiri)
        $mySchedules = \App\Models\EmployeeSchedule::where('user_id', $user->id)
            ->where('date', $selectedDate)
            ->get();

        // Get duty letters (Surat Tugas Resmi Approved) for suggestions
        $dutyLetters = $user->letters()
            ->where('status', 'Approved')
            ->where(function($q) use ($selectedDate) {
                $q->whereDate('date_start', '<=', $selectedDate)
                  ->where(function($sq) use ($selectedDate) {
                      $sq->whereDate('date_end', '>=', $selectedDate)
                        ->orWhereNull('date_end');
                  });
            })
            ->get();

        // Daftar RHK Pegawai
        $userRhks = UserRhk::where('user_id', $user->id)->orderBy('classification', 'desc')->orderBy('id', 'asc')->get();

        // Google Drive integration status
        $driveService = new GoogleDriveService();
        $driveConfigured = $driveService->isConfigured();
        $serviceAccountEmail = $driveService->getServiceAccountEmail();
        $masterFolder = Setting::get('google_drive_master_folder');

        // Auto-generate folder untuk pegawai jika belum ada dan master folder telah disetel
        if (empty($user->drive_folder_link) && $driveConfigured && !empty($masterFolder)) {
            $createdFolder = $driveService->generateUserFolder($user, $masterFolder);
            if ($createdFolder) {
                $user->refresh();
            }
        }

        // Auto-generate Bukti PDF untuk item kegiatan yang masih berupa folder link atau belum ada PDF-nya
        if ($myAgendaToday && $myAgendaToday->items) {
            foreach ($myAgendaToday->items as $agendaItem) {
                if (empty($agendaItem->drive_link) || str_contains($agendaItem->drive_link, '/drive/folders/')) {
                    $this->generateItemEvidencePdf($agendaItem);
                }
            }
            $myAgendaToday->load('items');
        }

        // Hitung Skor Kesiapan e-Kinerja (0 - 100%)
        $totalItems = $myAgendaToday ? $myAgendaToday->items->count() : 0;
        $readinessScore = 0;
        $readinessColor = 'Merah';
        $itemsReadiness = [];

        if ($totalItems > 0) {
            $sumScores = 0;
            foreach ($myAgendaToday->items as $item) {
                $itemScore = 0;
                // 1. RHK terisi (+20)
                if (!empty($item->rhk_text)) $itemScore += 20;
                // 2. Jam mulai & selesai (+20)
                if (!empty($item->start_time) && !empty($item->end_time)) $itemScore += 20;
                // 3. Uraian kegiatan (+20)
                if (!empty($item->plan_description)) $itemScore += 20;
                // 4. Realisasi kegiatan (+20)
                if (!empty($item->realization_notes) || $item->status === 'completed') $itemScore += 20;
                // 5. Link bukti dukung Drive (+20)
                if (!empty($item->drive_link) || !empty($user->drive_folder_link)) $itemScore += 20;

                $itemsReadiness[$item->id] = $itemScore;
                $sumScores += $itemScore;
            }
            $readinessScore = round($sumScores / $totalItems);
            if ($readinessScore >= 80) {
                $readinessColor = 'Hijau';
            } elseif ($readinessScore >= 50) {
                $readinessColor = 'Kuning';
            } else {
                $readinessColor = 'Merah';
            }
        }

        return view('agenda.index', compact(
            'myAgendaToday',
            'user',
            'upcomingMeetings',
            'mySchedules',
            'dutyLetters',
            'userRhks',
            'driveConfigured',
            'serviceAccountEmail',
            'readinessScore',
            'readinessColor',
            'itemsReadiness',
            'selectedDate'
        ));
    }

    /**
     * Store (Submit) Rencana Agenda Pagi SIPEGA
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now('Asia/Makassar');
        $hour = (int) $now->format('H');

        $status = 'Submitted';
        if ($hour >= 9) {
            $status = 'Late'; 
        }
        
        $isFlexible = Setting::get('is_realization_open_anytime') === '1';

        // Sistem mengunci input setelah jam 17:00 jika bukan mode flexible
        if (($hour < 7 || $hour >= 17) && !$isFlexible) {
            return back()->with('error', 'SIPEGA: Pengisian rencana agenda harian hanya tersedia pukul 07:00 - 17:00 WITA.');
        }

        $existing = DailyAgenda::where('user_id', $user->id)->where('date', $today)->first();
        if ($existing) {
            return back()->with('error', 'Anda sudah men-submit rencana agenda untuk hari ini! Gunakan fitur Tambah Kegiatan Cepat di bawah.');
        }

        $request->validate([
            'plans' => 'required|array|min:1',
            'plans.*' => 'required|string|min:3',
            'rhk_texts' => 'nullable|array',
            'start_times' => 'nullable|array',
            'end_times' => 'nullable|array',
        ]);

        $agenda = DailyAgenda::create([
            'user_id' => $user->id,
            'date' => $today,
            'activity_plan' => implode("\n", $request->plans),
            'proof_file_path' => null, 
            'submitted_at' => $now,
            'status' => $status
        ]);

        foreach ($request->plans as $idx => $plan) {
            DailyAgendaItem::create([
                'daily_agenda_id' => $agenda->id,
                'plan_description' => $plan,
                'rhk_text' => $request->rhk_texts[$idx] ?? null,
                'start_time' => $request->start_times[$idx] ?? '08:00',
                'end_time' => $request->end_times[$idx] ?? '10:00',
                'drive_link' => $user->drive_folder_link,
                'status' => 'pending'
            ]);
        }

        return back()->with('success', 'Rencana Agenda Pagi SIPEGA berhasil disimpan. Anda memiliki ' . count($request->plans) . ' kegiatan hari ini!');
    }

    /**
     * Tambah Kegiatan Cepat (Bisa via Ketik atau Suara 🎙️ kapan saja)
     */
    public function quickAddItem(Request $request)
    {
        $user = auth()->user();
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $now = Carbon::now('Asia/Makassar');

        $request->validate([
            'plan_description' => 'required|string|min:3',
            'item_id' => 'nullable|integer',
            'rhk_text' => 'nullable|string',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'realization_notes' => 'nullable|string',
            'status' => 'nullable|in:pending,completed,changed,progress',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'proof_files' => 'nullable|array',
            'proof_files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        // Cari atau buat parent DailyAgenda
        $agenda = DailyAgenda::firstOrCreate(
            ['user_id' => $user->id, 'date' => $date],
            [
                'activity_plan' => $request->plan_description,
                'submitted_at' => $now,
                'status' => 'Submitted'
            ]
        );

        // Kumpulkan berkas foto (bisa dari proof_files[] multi atau proof_file single)
        $filesToProcess = [];
        if ($request->hasFile('proof_files')) {
            $filesToProcess = $request->file('proof_files');
        } elseif ($request->hasFile('proof_file')) {
            $filesToProcess = [$request->file('proof_file')];
        }

        $photoPaths = [];
        $dbPath = null;
        $destinationDir = storage_path('app/public/agendas');
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        if (!empty($filesToProcess)) {
            foreach ($filesToProcess as $idx => $file) {
                if (!$file || !$file->isValid()) continue;

                $extension = strtolower($file->getClientOriginalExtension());
                $filename = 'evidence_' . $user->id . '_' . time() . '_' . ($idx + 1) . '.' . $extension;
                $targetPath = $destinationDir . DIRECTORY_SEPARATOR . $filename;

                if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    try {
                        $manager = new ImageManager(new Driver());
                        $image = $manager->read($file->getRealPath());
                        $image->scaleDown(width: 1280);

                        $watermarkText = "SIPEGA e-KINERJA\nPegawai: " . $user->name . "\nWaktu: " . $now->format('d/m/Y H:i') . " WITA (Foto " . ($idx + 1) . ")";
                        $image->drawRectangle(10, 10, function ($rectangle) {
                            $rectangle->size(430, 65);
                            $rectangle->background('rgba(0,0,0,0.6)');
                        });
                        $image->text($watermarkText, 20, 25, function($font) {
                            $font->file(3);
                            $font->color('#ffffff');
                            $font->align('left');
                            $font->valign('top');
                            $font->lineHeight(1.4);
                        });
                        $image->toJpeg(85)->save($targetPath);
                    } catch (\Throwable $e) {
                        $file->move($destinationDir, $filename);
                    }
                } else {
                    $file->move($destinationDir, $filename);
                }

                $photoPaths[] = $targetPath;
                if (!$dbPath) {
                    $dbPath = 'agendas/' . $filename;
                }
            }
        }

        $itemStatus = $request->status ?: ($request->realization_notes ? 'completed' : 'pending');

        // Jika item_id diisi, update kegiatan yang sudah ada
        if ($request->filled('item_id')) {
            $existingItem = DailyAgendaItem::whereHas('dailyAgenda', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->find($request->item_id);

            if ($existingItem) {
                $existingItem->plan_description = $request->plan_description;
                if ($request->filled('rhk_text')) $existingItem->rhk_text = $request->rhk_text;
                if ($request->filled('start_time')) $existingItem->start_time = $request->start_time;
                if ($request->filled('end_time')) $existingItem->end_time = $request->end_time;
                if ($request->filled('realization_notes')) {
                    $existingItem->realization_notes = $request->realization_notes;
                    $existingItem->status = 'completed';
                }
                if ($dbPath) {
                    $existingItem->proof_file_path = $dbPath;
                }
                $existingItem->save();

                // Otomatis hasilkan & unggah PDF Bukti Dukung ke Google Drive dengan seluruh foto yang diunggah
                $this->generateItemEvidencePdf($existingItem, $photoPaths);

                return back()->with('success', 'Kegiatan agenda berhasil diperbarui dan berkas PDF bukti dukung siap disalin ke e-Kinerja!');
            }
        }

        $item = DailyAgendaItem::create([
            'daily_agenda_id' => $agenda->id,
            'plan_description' => $request->plan_description,
            'rhk_text' => $request->rhk_text,
            'start_time' => $request->start_time ?: '08:00',
            'end_time' => $request->end_time ?: '10:00',
            'status' => $itemStatus,
            'realization_notes' => $request->realization_notes,
            'proof_file_path' => $dbPath,
            'drive_link' => $user->drive_folder_link,
            'drive_file_id' => null,
        ]);

        // Otomatis hasilkan & unggah PDF Bukti Dukung ke Google Drive dengan seluruh foto yang diunggah
        $this->generateItemEvidencePdf($item, $photoPaths);

        return back()->with('success', 'Kegiatan baru berhasil dicatat dan berkas PDF bukti dukung siap disalin ke e-Kinerja!');
    }

    /**
     * Hasilkan PDF Bukti Dukung Resmi e-Kinerja dan Unggah ke Google Drive Pegawai
     */
    public function generateItemEvidencePdf(DailyAgendaItem $item, $photoLocalPaths = []): DailyAgendaItem
    {
        try {
            $item->loadMissing('dailyAgenda.user');
            $user = $item->dailyAgenda->user;
            $date = $item->dailyAgenda->date;

            // Foto-foto bukti fisik (convert ke Base64 untuk disematkan di dalam PDF Dompdf)
            $photoBase64s = [];
            
            // Format photoLocalPaths ke array
            if (is_string($photoLocalPaths)) {
                $photoLocalPaths = [$photoLocalPaths];
            } elseif (!is_array($photoLocalPaths)) {
                $photoLocalPaths = [];
            }

            if (!empty($photoLocalPaths)) {
                foreach ($photoLocalPaths as $pPath) {
                    if ($pPath && file_exists($pPath)) {
                        $type = pathinfo($pPath, PATHINFO_EXTENSION);
                        $data = file_get_contents($pPath);
                        $photoBase64s[] = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                }
            } elseif ($item->proof_file_path) {
                $fullPath = storage_path('app/public/' . $item->proof_file_path);
                if (file_exists($fullPath) && in_array(strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])) {
                    $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $data = file_get_contents($fullPath);
                    $photoBase64s[] = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }

            // Render PDF resmi menggunakan template activity_evidence
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.activity_evidence', [
                'item' => $item,
                'user' => $user,
                'date' => $date,
                'photoBase64s' => $photoBase64s,
                'photoBase64' => $photoBase64s[0] ?? null,
            ]);

            $cleanNip = preg_replace('/[^0-9]/', '', $user->nip ?: (string)$user->id);
            $cleanDate = str_replace('-', '', $date);
            $filename = 'Bukti_eKinerja_' . $cleanNip . '_' . $cleanDate . '_Item' . $item->id . '.pdf';

            $destinationDir = storage_path('app/public/agendas');
            if (!file_exists($destinationDir)) {
                mkdir($destinationDir, 0777, true);
            }
            $targetPdfPath = $destinationDir . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($targetPdfPath, $pdf->setPaper('a4', 'portrait')->output());

            $dbPath = 'agendas/' . $filename;
            $driveLink = route('agenda.item.view_pdf', $item->id);
            $driveFileId = null;

            // Unggah langsung file PDF ke Google Drive dalam Folder Pegawai
            $driveService = new GoogleDriveService();
            if ($driveService->isConfigured()) {
                // Pastikan folder Drive user sudah terpasang / temukan folder di Master Folder
                $targetFolder = $user->drive_folder_link;
                if (empty($targetFolder)) {
                    $foundFolder = $driveService->findOrCreateUserFolder($user);
                    if ($foundFolder && !empty($foundFolder['web_view_link'])) {
                        $targetFolder = $foundFolder['web_view_link'];
                    }
                }

                $driveUpload = $driveService->uploadEvidence(
                    $targetPdfPath,
                    $filename,
                    $targetFolder,
                    true
                );

                if ($driveUpload && !empty($driveUpload['web_view_link'])) {
                    $driveLink = $driveUpload['web_view_link'];
                    $driveFileId = $driveUpload['id'];
                }
            }

            $item->proof_file_path = $dbPath;
            $item->drive_link = $driveLink;
            if ($driveFileId) {
                $item->drive_file_id = $driveFileId;
            }
            $item->save();

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal generate PDF bukti dukung: ' . $e->getMessage());
        }

        return $item;
    }

    /**
     * Buka Langsung File PDF Bukti Dukung di Browser (Inline Streaming, Anti-403)
     */
    public function viewItemPdf($id)
    {
        $item = DailyAgendaItem::findOrFail($id);

        if (!$item->proof_file_path || !file_exists(storage_path('app/public/' . $item->proof_file_path)) || !str_ends_with($item->proof_file_path, '.pdf')) {
            $this->generateItemEvidencePdf($item);
        }

        $filePath = storage_path('app/public/' . $item->proof_file_path);
        if (!file_exists($filePath)) {
            abort(404, 'Berkas PDF bukti kegiatan tidak ditemukan.');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }

    /**
     * Download / Buka Langsung File PDF Bukti Dukung
     */
    public function downloadItemPdf($id)
    {
        $user = auth()->user();
        $item = DailyAgendaItem::whereHas('dailyAgenda', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        if (!$item->proof_file_path || !file_exists(storage_path('app/public/' . $item->proof_file_path)) || !str_ends_with($item->proof_file_path, '.pdf')) {
            $this->generateItemEvidencePdf($item);
        }

        $filePath = storage_path('app/public/' . $item->proof_file_path);
        if (!file_exists($filePath)) {
            return back()->with('error', 'Berkas PDF bukti kegiatan tidak ditemukan.');
        }

        return response()->download($filePath);
    }

    /**
     * Buat Ulang Berkas PDF & Tautan Drive untuk Item Tertentu
     */
    public function regenerateItemPdf($id)
    {
        $user = auth()->user();
        $item = DailyAgendaItem::whereHas('dailyAgenda', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $this->generateItemEvidencePdf($item);
        $item->refresh();

        if ($item->drive_file_id || str_contains($item->drive_link, 'drive.google.com')) {
            return back()->with('success', '✓ Berhasil diunggah ke Google Drive! Tautan Google Drive telah diperbarui di tabel e-Kinerja.');
        }

        return back()->with('success', 'Berkas PDF berhasil diproses di server SIPEGA.');
    }

    /**
     * Sinkronkan Seluruh Berkas PDF Kegiatan ke Google Drive Sekaligus
     */
    public function syncAllToDrive(Request $request)
    {
        $user = auth()->user();
        $date = $request->date ?: Carbon::today('Asia/Makassar')->toDateString();

        $agenda = DailyAgenda::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->with('items')
            ->first();

        if (!$agenda || $agenda->items->isEmpty()) {
            return back()->with('error', 'Tidak ada kegiatan pada tanggal tersebut untuk disinkronkan ke Google Drive.');
        }

        $driveService = new GoogleDriveService();
        if (!$driveService->isConfigured()) {
            return back()->with('error', 'Google Drive API belum terkonfigurasi. Pastikan kredensial service-account.json sudah aktif di Smart Pengaturan.');
        }

        $masterFolder = Setting::get('google_drive_master_folder');
        if (empty($masterFolder)) {
            return back()->with('error', 'Folder Induk Google Drive (Master Folder) belum disetel di Smart Pengaturan.');
        }

        $driveService->findOrCreateUserFolder($user, $masterFolder);

        $successCount = 0;
        foreach ($agenda->items as $item) {
            try {
                $this->generateItemEvidencePdf($item);
                $item->refresh();
                if ($item->drive_file_id || str_contains($item->drive_link, 'drive.google.com')) {
                    $successCount++;
                }
            } catch (\Throwable $e) {}
        }

        if ($successCount > 0) {
            return back()->with('success', "✓ Berhasil menyinkronkan {$successCount} berkas kegiatan langsung ke folder Google Drive Anda!");
        }

        return back()->with('error', 'Gagal mengunggah ke Google Drive. Pastikan folder Master sudah dibagikan ke email robot SIPEGA sebagai EDITOR.');
    }

    /**
     * Hapus Item Kegiatan
     */
    public function deleteItem($id)
    {
        $user = auth()->user();
        $item = DailyAgendaItem::whereHas('dailyAgenda', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $item->delete();

        return back()->with('success', 'Item kegiatan berhasil dihapus.');
    }

    /**
     * Evaluasi Sore: Update Status Per Item, Realisasi & Upload Bukti ke Google Drive
     */
    public function updateRealization(Request $request, $id)
    {
        $user = auth()->user();
        $now = Carbon::now('Asia/Makassar');
        $hour = (int) $now->format('H');
        
        $isFlexible = Setting::get('is_realization_open_anytime') === '1';

        if (!$isFlexible) {
            if ($hour < 15 || $hour >= 17) {
                return back()->with('error', 'SIPEGA: Realisasi (Evaluasi Sore) hanya dapat dilakukan pada pukul 15:00 - 17:00 WITA.');
            }
        }

        $agenda = DailyAgenda::where('user_id', $user->id)->findOrFail($id);
        
        $request->validate([
            'items' => 'required|array',
            'items.*.status' => 'required|in:completed,changed,progress',
            'items.*.notes' => 'nullable|string',
            'items.*.rhk_text' => 'nullable|string',
            'items.*.start_time' => 'nullable|string',
            'items.*.end_time' => 'nullable|string',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240'
        ]);

        // Upload bukti foto/dokumen jika ada
        $dbPath = $agenda->proof_file_path;
        $driveLink = null;
        $driveFileId = null;

        if ($request->hasFile('proof_file')) {
            try {
                $file = $request->file('proof_file');
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = 'realization_' . $user->id . '_' . time() . '.' . $extension;
                $destinationDir = storage_path('app/public/agendas');
                
                if (!file_exists($destinationDir)) {
                    mkdir($destinationDir, 0777, true);
                }
                $targetPath = $destinationDir . DIRECTORY_SEPARATOR . $filename;

                if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($file->getRealPath());
                    $image->scaleDown(width: 1280);

                    $watermarkText = "SIPEGA REALISASI\nNama: " . $user->name . "\nWaktu: " . $now->format('d/m/Y H:i:s') . " WITA";
                    $image->drawRectangle(10, 10, function ($rectangle) {
                        $rectangle->size(420, 70);
                        $rectangle->background('rgba(0,0,0,0.5)');
                    });
                    $image->text($watermarkText, 20, 28, function($font) {
                        $font->file(3);
                        $font->color('#ffffff');
                        $font->align('left');
                        $font->valign('top');
                        $font->lineHeight(1.4);
                    });
                    $image->toJpeg(85)->save($targetPath);
                } else {
                    $file->move($destinationDir, $filename);
                }

                $dbPath = 'agendas/' . $filename;

                // Upload ke Google Drive
                $driveService = new GoogleDriveService();
                if ($driveService->isConfigured()) {
                    $driveUpload = $driveService->uploadEvidence(
                        $targetPath,
                        $filename,
                        $user->drive_folder_link,
                        true
                    );
                    if ($driveUpload && !empty($driveUpload['web_view_link'])) {
                        $driveLink = $driveUpload['web_view_link'];
                        $driveFileId = $driveUpload['id'];
                    }
                }
            } catch (\Throwable $e) {
                return back()->with('error', 'Gagal memproses lampiran: ' . $e->getMessage());
            }
        }

        foreach ($request->items as $itemId => $data) {
            $item = DailyAgendaItem::where('id', $itemId)->where('daily_agenda_id', $agenda->id)->first();
            if ($item) {
                $updateData = [
                    'status' => $data['status'],
                    'realization_notes' => $data['notes'] ?? $item->realization_notes,
                ];

                if (!empty($data['rhk_text'])) {
                    $updateData['rhk_text'] = $data['rhk_text'];
                }
                if (!empty($data['start_time'])) {
                    $updateData['start_time'] = $data['start_time'];
                }
                if (!empty($data['end_time'])) {
                    $updateData['end_time'] = $data['end_time'];
                }

                // Update link drive jika baru diupload, atau default ke folder user
                if ($driveLink) {
                    $updateData['drive_link'] = $driveLink;
                    $updateData['drive_file_id'] = $driveFileId;
                } elseif (empty($item->drive_link) && !empty($user->drive_folder_link)) {
                    $updateData['drive_link'] = $user->drive_folder_link;
                }

                $item->update($updateData);

                // Otomatis hasilkan & perbarui PDF Bukti Dukung resmi yang memuat catatan realisasi & unggah ke Google Drive
                $this->generateItemEvidencePdf($item);
            }
        }

        $agenda->update([
            'activity_realization' => 'Verified Daily Items',
            'proof_file_path' => $dbPath,
            'realization_submitted_at' => $now
        ]);

        return back()->with('success', 'Evaluasi harian SIPEGA berhasil disimpan dan disinkronkan ke Google Drive!');
    }

    /**
     * Simpan / Tambah RHK Pegawai
     */
    public function storeRhk(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'rhk_desc' => 'required|string|min:5',
            'classification' => 'nullable|string|in:Utama,Tambahan',
            'target_quarter' => 'nullable|string',
            'year' => 'nullable|integer',
        ]);

        UserRhk::create([
            'user_id' => $user->id,
            'rhk_desc' => trim($request->rhk_desc),
            'classification' => $request->classification ?: 'Utama',
            'target_quarter' => $request->target_quarter ?: 'Semua',
            'year' => $request->year ?: (int) date('Y'),
        ]);

        return back()->with('success', 'Rencana Hasil Kerja (RHK) berhasil ditambahkan!');
    }

    /**
     * Hapus RHK Pegawai
     */
    public function deleteRhk($id)
    {
        $user = auth()->user();
        $rhk = UserRhk::where('user_id', $user->id)->findOrFail($id);
        $rhk->delete();

        return back()->with('success', 'RHK berhasil dihapus.');
    }

    /**
     * Update Tautan Folder Google Drive Pegawai
     */
    public function updateDriveFolder(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'drive_folder_link' => 'required|string|min:5',
        ]);

        $user->update([
            'drive_folder_link' => trim($request->drive_folder_link)
        ]);

        // Perbarui dan sinkronkan seluruh item agenda hari ini ke folder Drive yang baru disetel
        $todayItems = DailyAgendaItem::whereHas('dailyAgenda', function($q) use ($user) {
            $q->where('user_id', $user->id)->whereDate('date', Carbon::today('Asia/Makassar'));
        })->get();

        foreach ($todayItems as $item) {
            $this->generateItemEvidencePdf($item);
        }

        return back()->with('success', 'Link folder Google Drive pegawai berhasil diperbarui dan berkas agenda hari ini disinkronkan ke Drive!');
    }

    /**
     * Buat / Hubungkan Folder Google Drive Pribadi secara Otomatis
     */
    public function generateMyFolder(Request $request)
    {
        $user = auth()->user();
        $driveService = new GoogleDriveService();

        if (!$driveService->isConfigured()) {
            return back()->with('error', 'Google Drive API belum terkonfigurasi di server.');
        }

        $master = Setting::get('google_drive_master_folder');
        if (empty($master)) {
            return back()->with('error', 'Folder Induk Google Drive (Master Folder) belum disetel oleh Administrator di Pengaturan Sistem.');
        }

        $folder = $driveService->findOrCreateUserFolder($user, $master);
        if ($folder) {
            // Upload / sinkronkan seluruh item agenda hari ini ke folder Drive yang baru terhubung
            $todayItems = DailyAgendaItem::whereHas('dailyAgenda', function($q) use ($user) {
                $q->where('user_id', $user->id)->whereDate('date', Carbon::today('Asia/Makassar'));
            })->get();
            foreach ($todayItems as $item) {
                $this->generateItemEvidencePdf($item);
            }

            return back()->with('success', 'Folder Google Drive Anda berhasil dihubungkan dan seluruh berkas agenda hari ini telah disinkronkan ke Drive!');
        }

        return back()->with('error', 'Gagal menghubungkan folder Google Drive otomatis. Pastikan Service Account memiliki izin Editor pada Folder Induk.');
    }

    /**
     * Halaman Monitoring & Penilaian Kerja (Untuk Pimpinan/Admin/Kasubag)
     */
    public function leaderIndex()
    {
        $user = auth()->user();
        if (!in_array($user->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Anda tidak memiliki hak akses Penilaian.');
        }

        $pendingAgendas = DailyAgenda::with(['user', 'items'])
            ->whereHas('user', function($q) {
                $q->realPegawai();
            })
            ->whereNotNull('realization_submitted_at')
            ->orderBy('realization_submitted_at', 'desc')
            ->paginate(15);

        return view('agenda.leader_index', compact('pendingAgendas'));
    }

    /**
     * Pimpinan Memberikan Penilaian & Masukan
     */
    public function evaluate(Request $request, $id)
    {
        $leader = auth()->user();
        if (!in_array($leader->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            return back()->with('error', 'Akses Ditolak.');
        }

        $request->validate([
            'rating' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string'
        ]);

        $agenda = DailyAgenda::findOrFail($id);
        $agenda->update([
            'leader_rating' => $request->rating,
            'leader_feedback' => $request->feedback,
            'evaluated_at' => now('Asia/Makassar'),
            'evaluated_by' => $leader->id,
            'status' => 'Verified'
        ]);

        $employee = $agenda->user;
        if ($employee && $employee->isRealPegawai()) {
            $avgScore = DailyAgenda::where('user_id', $employee->id)
                ->whereNotNull('leader_rating')
                ->avg('leader_rating');
                
            if ($avgScore) {
                $employee->update([
                    'performance_score' => $avgScore,
                    'performance_color' => $avgScore >= 80 ? 'Hijau' : ($avgScore >= 60 ? 'Kuning' : 'Merah')
                ]);
            }
        }

        return back()->with('success', 'Penilaian SIPEGA berhasil disimpan. Skor pegawai telah diperbarui otomatis.');
    }

    /**
     * Penilaian Massal oleh Pimpinan
     */
    public function bulkEvaluate(Request $request)
    {
        $leader = auth()->user();
        if (!in_array($leader->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            return back()->with('error', 'Akses Ditolak.');
        }

        $request->validate([
            'agenda_ids' => 'required|array',
            'agenda_ids.*' => 'exists:daily_agendas,id',
            'bulk_rating' => 'required|integer|min:0|max:100'
        ]);

        $evaluatedCount = 0;
        foreach ($request->agenda_ids as $id) {
            $agenda = DailyAgenda::findOrFail($id);
            
            if (!$agenda->leader_rating) {
                $agenda->update([
                    'leader_rating' => $request->bulk_rating,
                    'leader_feedback' => 'Penilaian Massal (Verified by ' . $leader->role . ')',
                    'evaluated_at' => now('Asia/Makassar'),
                    'evaluated_by' => $leader->id,
                    'status' => 'Verified'
                ]);

                $employee = $agenda->user;
                if ($employee && $employee->isRealPegawai()) {
                    $avgScore = DailyAgenda::where('user_id', $employee->id)
                        ->whereNotNull('leader_rating')
                        ->avg('leader_rating');
                        
                    if ($avgScore) {
                        $employee->update([
                            'performance_score' => $avgScore,
                            'performance_color' => $avgScore >= 80 ? 'Hijau' : ($avgScore >= 60 ? 'Kuning' : 'Merah')
                        ]);
                    }
                }
                $evaluatedCount++;
            }
        }

        return back()->with('success', $evaluatedCount . ' laporan berhasil dinilai secara massal.');
    }
}
