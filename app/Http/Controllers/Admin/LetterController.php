<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Letter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LetterController extends Controller
{
    public function index(Request $request)
    {
        $query = Letter::with('users', 'creator');

        // Level Pegawai hanya melihat yang sudah Approved yang menugaskan dirinya
        if (auth()->user()->role === 'Pegawai') {
            $query->where('status', 'Approved')
                  ->whereHas('users', function($q) {
                      $q->where('users.id', auth()->id());
                  });
        }

        // Filter Kategori (DLK, DLP, DLN)
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter Model (model_1 s.d model_5)
        if ($request->filled('st_model')) {
            $query->where('st_model', $request->st_model);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('invitation_from', 'like', "%{$search}%");
            });
        }

        $letters = $query->latest()->paginate(15)->withQueryString();

        // Statistik ringkas untuk Kasubag/Admin
        $stats = [
            'total' => Letter::count(),
            'dlk' => Letter::where('category', 'DLK')->count(),
            'dlp' => Letter::where('category', 'DLP')->count(),
            'dln' => Letter::where('category', 'DLN')->count(),
            'draft' => Letter::where('status', 'Draft')->count(),
            'approved' => Letter::where('status', 'Approved')->count(),
        ];

        return view('admin.letters.index', compact('letters', 'stats'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        
        // Template nomor otomatis
        $nextNumber = sprintf('%04d/C6.24/DM.00.02/%s', Letter::count() + 1, date('Y'));

        $defaultSignatory = [
            'name' => 'Dr. Jarwoko, M. Pd',
            'nip' => '197003191997031001',
            'position' => 'Kepala Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur'
        ];

        return view('admin.letters.create', compact('users', 'nextNumber', 'defaultSignatory'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'st_model' => 'required|in:model_1,model_2,model_3,model_4,model_5',
            'category' => 'required|in:DLK,DLP,DLN',
            'number' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'date_start' => 'required|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'location' => 'required|string|max:255',
            'invitation_from' => 'nullable|string',
            'invitation_number' => 'nullable|string|max:150',
            'invitation_date' => 'nullable|date',
            'invitation_subject' => 'nullable|string',
            'dipa_source' => 'nullable|string',
            'signatory_name' => 'nullable|string|max:150',
            'signatory_nip' => 'nullable|string|max:50',
            'signature_date_type' => 'nullable|in:auto,manual',
            'signed_at' => 'nullable|date',
            'participants' => 'required|array|min:1',
            'participants.*.user_id' => 'required|exists:users,id',
        ]);

        // Model 1 mewajibkan tepat 1 orang
        if ($request->st_model === 'model_1' && count($request->participants) > 1) {
            return back()->withErrors(['participants' => 'Model 1 khusus untuk surat tugas 1 orang. Untuk lebih dari 1 orang, gunakan Model 2, 4, atau 5.'])->withInput();
        }

        // Validasi bentrok jadwal pegawai (irisan tanggal dengan surat Approved lain)
        $participantUserIds = array_filter(array_column($request->participants, 'user_id'));
        $conflicts = $this->getDutyConflicts($participantUserIds, $request->date_start, $request->date_end);
        if (!empty($conflicts)) {
            $conflictErrors = [];
            foreach ($conflicts as $c) {
                $conflictErrors[] = "Pegawai {$c['user_name']} (NIP: {$c['user_nip']}) tidak dapat ditugaskan karena sedang berdinas pada kegiatan '{$c['title']}' (No. ST: {$c['letter_number']}) tanggal {$c['date_range']} di {$c['location']}.";
            }
            return back()->withErrors(['participants' => implode(' | ', $conflictErrors)])->withInput();
        }

        // Tentukan status awal berdasarkan tombol yang diklik
        $status = ($request->action_type === 'approve') ? 'Approved' : 'Draft';

        $letter = Letter::create([
            'type' => 'ST',
            'st_model' => $request->st_model,
            'category' => $request->category,
            'number' => $request->number ?? sprintf('%04d/C6.24/DM.00.02/%s', Letter::count() + 1, date('Y')),
            'title' => $request->title,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end ?? $request->date_start,
            'location' => $request->location,
            'basis' => $request->basis,
            'purpose' => $request->purpose,
            'invitation_from' => $request->invitation_from,
            'invitation_number' => $request->invitation_number,
            'invitation_date' => $request->invitation_date,
            'invitation_subject' => $request->invitation_subject,
            'dipa_source' => $request->dipa_source,
            'show_keterangan' => $request->boolean('show_keterangan'),
            'signatory_name' => $request->signatory_name ?? 'Dr. Jarwoko, M. Pd',
            'signatory_nip' => $request->signatory_nip ?? '197003191997031001',
            'signature_date_type' => $request->signature_date_type ?? 'auto',
            'signed_at' => $request->signature_date_type === 'manual' ? $request->signed_at : null,
            'justification' => $request->justification,
            'created_by' => auth()->id() ?? 1,
            'status' => $status,
        ]);

        // Format data pivot peserta
        $pivotData = [];
        foreach ($request->participants as $p) {
            $userId = $p['user_id'];
            $nip = isset($p['nip']) ? trim($p['nip']) : null;
            $golongan = isset($p['golongan']) ? trim($p['golongan']) : null;
            $position = isset($p['position']) ? trim($p['position']) : null;

            $pivotData[$userId] = [
                'user_nip' => $nip,
                'user_golongan' => $golongan,
                'user_position' => $position,
                'custom_role' => $p['custom_role'] ?? null,
                'keterangan' => $p['keterangan'] ?? null,
                'city_destination' => $p['city_destination'] ?? null,
                'venue' => $p['venue'] ?? null,
                'execution_dates' => $p['execution_dates'] ?? null,
                'person_in_charge' => $p['person_in_charge'] ?? null,
            ];

            // Sinkronisasi jika user belum memiliki NIP di database users
            if (!empty($nip) && $nip !== '-') {
                $userObj = User::find($userId);
                if ($userObj && (empty($userObj->nip) || $userObj->nip === '-')) {
                    $userObj->update(['nip' => $nip]);
                }
            }
        }

        $letter->users()->attach($pivotData);

        $msg = ($status === 'Approved') 
            ? 'Surat Tugas berhasil dibuat dan langsung Disetujui (Approved). Penugasan otomatis dihitung pada Rekap Tugas!'
            : 'Surat Tugas berhasil disimpan sebagai Draft.';

        return redirect()->route('letters.index')->with('success', $msg);
    }

    public function edit($id)
    {
        $letter = Letter::with('users')->findOrFail($id);
        $users = User::orderBy('name')->get();
        return view('admin.letters.edit', compact('letter', 'users'));
    }

    public function update(Request $request, $id)
    {
        $letter = Letter::findOrFail($id);

        $request->validate([
            'st_model' => 'required|in:model_1,model_2,model_3,model_4,model_5',
            'category' => 'required|in:DLK,DLP,DLN',
            'number' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'date_start' => 'required|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'signatory_name' => 'nullable|string|max:150',
            'signatory_nip' => 'nullable|string|max:50',
            'signature_date_type' => 'nullable|in:auto,manual',
            'signed_at' => 'nullable|date',
            'participants' => 'required|array|min:1',
            'participants.*.user_id' => 'required|exists:users,id',
        ]);

        // Model 1 mewajibkan tepat 1 orang
        if ($request->st_model === 'model_1' && count($request->participants) > 1) {
            return back()->withErrors(['participants' => 'Model 1 khusus untuk surat tugas 1 orang. Untuk lebih dari 1 orang, gunakan Model 2, 4, atau 5.'])->withInput();
        }

        // Validasi bentrok jadwal pegawai (irisan tanggal dengan surat Approved lain, kecualikan surat ini sendiri)
        $participantUserIds = array_filter(array_column($request->participants, 'user_id'));
        $conflicts = $this->getDutyConflicts($participantUserIds, $request->date_start, $request->date_end, $letter->id);
        if (!empty($conflicts)) {
            $conflictErrors = [];
            foreach ($conflicts as $c) {
                $conflictErrors[] = "Pegawai {$c['user_name']} (NIP: {$c['user_nip']}) tidak dapat ditugaskan karena sedang berdinas pada kegiatan '{$c['title']}' (No. ST: {$c['letter_number']}) tanggal {$c['date_range']} di {$c['location']}.";
            }
            return back()->withErrors(['participants' => implode(' | ', $conflictErrors)])->withInput();
        }

        $letter->update([
            'st_model' => $request->st_model,
            'category' => $request->category,
            'number' => $request->number,
            'title' => $request->title,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end ?? $request->date_start,
            'location' => $request->location,
            'basis' => $request->basis,
            'purpose' => $request->purpose,
            'invitation_from' => $request->invitation_from,
            'invitation_number' => $request->invitation_number,
            'invitation_date' => $request->invitation_date,
            'invitation_subject' => $request->invitation_subject,
            'dipa_source' => $request->dipa_source,
            'show_keterangan' => $request->boolean('show_keterangan'),
            'signatory_name' => $request->signatory_name,
            'signatory_nip' => $request->signatory_nip,
            'signature_date_type' => $request->signature_date_type ?? 'auto',
            'signed_at' => $request->signature_date_type === 'manual' ? $request->signed_at : null,
            'justification' => $request->justification,
        ]);

        if ($request->action_type === 'approve') {
            $letter->update(['status' => 'Approved']);
        } elseif ($request->action_type === 'unapprove') {
            $letter->update(['status' => 'Draft']);
        }

        // Format data pivot peserta
        $pivotData = [];
        foreach ($request->participants as $p) {
            $userId = $p['user_id'];
            $nip = isset($p['nip']) ? trim($p['nip']) : null;
            $golongan = isset($p['golongan']) ? trim($p['golongan']) : null;
            $position = isset($p['position']) ? trim($p['position']) : null;

            $pivotData[$userId] = [
                'user_nip' => $nip,
                'user_golongan' => $golongan,
                'user_position' => $position,
                'custom_role' => $p['custom_role'] ?? null,
                'keterangan' => $p['keterangan'] ?? null,
                'city_destination' => $p['city_destination'] ?? null,
                'venue' => $p['venue'] ?? null,
                'execution_dates' => $p['execution_dates'] ?? null,
                'person_in_charge' => $p['person_in_charge'] ?? null,
            ];

            // Sinkronisasi jika user belum memiliki NIP di database users
            if (!empty($nip) && $nip !== '-') {
                $userObj = User::find($userId);
                if ($userObj && (empty($userObj->nip) || $userObj->nip === '-')) {
                    $userObj->update(['nip' => $nip]);
                }
            }
        }

        $letter->users()->sync($pivotData);

        return redirect()->route('letters.index')->with('success', 'Surat Tugas berhasil diperbarui.');
    }

    public function approve($id)
    {
        $letter = Letter::with('users')->findOrFail($id);

        // Verifikasi ketat tidak ada bentrok sebelum di-approve
        $participantUserIds = $letter->users->pluck('id')->toArray();
        $conflicts = $this->getDutyConflicts(
            $participantUserIds,
            $letter->date_start->format('Y-m-d'),
            $letter->date_end ? $letter->date_end->format('Y-m-d') : null,
            $letter->id
        );

        if (!empty($conflicts)) {
            $conflictList = [];
            foreach ($conflicts as $c) {
                $conflictList[] = "{$c['user_name']} (sedang berdinas pada '{$c['title']}' tanggal {$c['date_range']})";
            }
            return back()->with('error', 'Gagal menyetujui Surat Tugas! Terdapat bentrok jadwal pegawai yang sedang berdinas: ' . implode('; ', $conflictList));
        }

        $letter->update(['status' => 'Approved']);
        return back()->with('success', 'Surat Tugas berhasil Disetujui (Approved). Penugasan otomatis tercatat pada Rekap Tugas!');
    }

    public function unapprove($id)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            return abort(403, 'Akses Ditolak.');
        }

        $letter = Letter::with('users')->findOrFail($id);
        
        $letter->update(['status' => 'Draft']);

        return back()->with('success', 'Persetujuan Surat Tugas ' . ($letter->number ?: '') . ' berhasil DIBATALKAN. Status kembali menjadi DRAFT dan perhitungan Rekap Dinas Luar otomatis disinkronkan.');
    }

    public function reject($id)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
            return abort(403, 'Akses Ditolak.');
        }

        $letter = Letter::with('users')->findOrFail($id);
        $letter->update(['status' => 'Rejected']);

        return back()->with('success', 'Surat Tugas ditolak (Rejected). Data tidak dihitung dalam Rekap Dinas Luar.');
    }

    public function checkConflicts(Request $request)
    {
        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'nullable|date',
            'exclude_letter_id' => 'nullable|integer',
        ]);

        $dateStart = $request->date_start;
        $dateEnd = $request->date_end ?? $dateStart;
        $excludeId = $request->exclude_letter_id;

        $conflicts = $this->getDutyConflicts([], $dateStart, $dateEnd, $excludeId, true);

        return response()->json([
            'success' => true,
            'conflicts' => $conflicts
        ]);
    }

    protected function getDutyConflicts(array $userIds, string $dateStart, ?string $dateEnd, ?int $excludeLetterId = null, bool $allBusy = false): array
    {
        \Carbon\Carbon::setLocale('id');
        $dateEnd = $dateEnd ?? $dateStart;

        $query = Letter::where('status', 'Approved')
            ->where('date_start', '<=', $dateEnd)
            ->whereRaw('COALESCE(date_end, date_start) >= ?', [$dateStart])
            ->with('users');

        if ($excludeLetterId) {
            $query->where('id', '!=', $excludeLetterId);
        }

        if (!$allBusy && !empty($userIds)) {
            $query->whereHas('users', function ($q) use ($userIds) {
                $q->whereIn('users.id', $userIds);
            });
        }

        $approvedLetters = $query->get();
        $conflicts = [];

        foreach ($approvedLetters as $approved) {
            $start = \Carbon\Carbon::parse($approved->date_start);
            $end = \Carbon\Carbon::parse($approved->date_end ?? $approved->date_start);
            if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
                $dateText = $start->translatedFormat('d F Y');
            } else {
                $dateText = $start->translatedFormat('d') . ' s.d ' . $end->translatedFormat('d F Y');
            }

            foreach ($approved->users as $u) {
                if (!$allBusy && !in_array($u->id, $userIds)) {
                    continue;
                }

                if (!isset($conflicts[$u->id])) {
                    $conflicts[$u->id] = [
                        'user_id' => $u->id,
                        'user_name' => $u->name,
                        'user_nip' => $u->nip ?? '-',
                        'letter_id' => $approved->id,
                        'letter_number' => $approved->number ?? '-',
                        'title' => $approved->title,
                        'location' => $approved->location ?? '-',
                        'date_range' => $dateText,
                        'date_start' => $approved->date_start ? $approved->date_start->format('Y-m-d') : '',
                        'date_end' => $approved->date_end ? $approved->date_end->format('Y-m-d') : ($approved->date_start ? $approved->date_start->format('Y-m-d') : ''),
                    ];
                }
            }
        }

        return $conflicts;
    }

    public function downloadPdfSt($id)
    {
        $letter = Letter::with(['users', 'creator'])->findOrFail($id);
        
        $pdf = Pdf::loadView('admin.letters.pdf', compact('letter'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'dpi' => 150,
            'defaultFont' => 'Bookman'
        ]);

        $fileName = 'Surat_Tugas_' . str_replace(['/', '\\', ' '], '_', $letter->number ?? $letter->id) . '.pdf';
        return $pdf->stream($fileName);
    }

    public function downloadPdfSk($id)
    {
        $letter = Letter::with('users')->findOrFail($id);
        $pdf = Pdf::loadView('admin.letters.pdf_sk', compact('letter'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream('Surat_Keputusan_' . str_replace('/', '_', $letter->number) . '.pdf');
    }

    public function destroy($id)
    {
        $letter = Letter::findOrFail($id);
        $letter->users()->detach();
        $letter->delete();
        return redirect()->route('letters.index')->with('success', 'Surat Tugas berhasil dihapus.');
    }
}
