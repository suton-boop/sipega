<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    protected LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    /**
     * Menu: KELOLA DATA CUTI (Daftar Pengajuan & Monitoring Cuti)
     */
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $user = auth()->user();
        $isPrivileged = in_array($user->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator']);

        $query = LeaveRequest::with(['user', 'supervisor', 'approver']);

        // Pegawai biasa hanya melihat cuti miliknya sendiri
        if (!$isPrivileged) {
            $query->where('user_id', $user->id);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Jenis Cuti
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        // Filter Status Kepegawaian (PNS / PPPK)
        if ($request->filled('employee_status')) {
            $query->where('employee_status', $request->employee_status);
        }

        // Pencarian Nama / NIP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                  ->orWhere('employee_nip', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $leaves = $query->latest()->paginate(10)->withQueryString();

        // Statistik Cuti
        $statsBase = $isPrivileged ? LeaveRequest::query() : LeaveRequest::where('user_id', $user->id);
        $stats = [
            'total' => (clone $statsBase)->count(),
            'pending' => (clone $statsBase)->whereIn('status', ['Diajukan', 'Menunggu Pertimbangan Atasan', 'Diverifikasi Kepegawaian'])->count(),
            'approved' => (clone $statsBase)->where('status', 'Disetujui')->count(),
            'rejected' => (clone $statsBase)->whereIn('status', ['Ditolak', 'Tidak Disetujui'])->count(),
        ];

        // Kuota Cuti Pegawai Saat Ini
        $currentYear = (int) now()->year;
        $myBalance = LeaveBalance::getOrCreateForUser($user, $currentYear);

        return view('leaves.index', compact('leaves', 'stats', 'myBalance', 'currentYear', 'isPrivileged'));
    }

    /**
     * Menu: PENGAJUAN CUTI (Formulir Pengajuan Baru)
     */
    public function create(Request $request)
    {
        Carbon::setLocale('id');
        $user = auth()->user();
        $isPrivileged = in_array($user->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator']);

        // Jika privileged, dapat memilih mengajukan untuk pegawai lain
        $targetUser = $user;
        if ($isPrivileged && $request->filled('user_id')) {
            $targetUser = User::find($request->user_id) ?: $user;
        }

        $employees = $isPrivileged ? User::orderBy('name')->get() : collect([$user]);

        // Daftar calon Atasan Langsung & PPK
        $supervisors = User::whereIn('role', ['Pimpinan', 'Kasubag', 'Admin'])
            ->orWhere('position', 'like', '%Kepala%')
            ->orWhere('position', 'like', '%Kasubag%')
            ->orWhere('position', 'like', '%Koordinator%')
            ->orderBy('name')
            ->get();

        $ppkOfficers = User::whereIn('role', ['Pimpinan', 'Kasubag', 'Admin'])
            ->orWhere('position', 'like', '%Kepala%')
            ->orderBy('name')
            ->get();

        $currentYear = (int) now()->year;
        $balance = LeaveBalance::getOrCreateForUser($targetUser, $currentYear);
        $masaKerja = $targetUser->getMasaKerjaText();

        return view('leaves.create', compact('targetUser', 'employees', 'supervisors', 'ppkOfficers', 'balance', 'currentYear', 'masaKerja', 'isPrivileged'));
    }

    /**
     * Endpoint API AJAX: Verifikasi Real-Time Asisten AI SIPEGA
     */
    public function aiCheck(Request $request)
    {
        $userId = $request->input('user_id', auth()->id());
        $user = User::findOrFail($userId);

        $params = [
            'employee_status' => $request->input('employee_status', $user->effective_status_kepegawaian),
            'employee_position' => $request->input('employee_position', $user->position),
            'leave_type' => $request->input('leave_type', 'cuti_tahunan'),
            'duration_amount' => $request->input('duration_amount', 1),
            'duration_unit' => $request->input('duration_unit', 'hari'),
            'start_date' => $request->input('start_date', now()->format('Y-m-d')),
            'end_date' => $request->input('end_date', now()->format('Y-m-d')),
            'reason' => $request->input('reason', ''),
            'work_duration' => $request->input('work_duration', $user->getMasaKerjaText()),
            'has_attachment' => $request->boolean('has_attachment', false),
        ];

        $verification = $this->leaveService->verifyLeaveEligibility($user, $params);

        return response()->json($verification);
    }

    /**
     * Simpan Pengajuan Cuti Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|string',
            'duration_amount' => 'required|integer|min:1',
            'duration_unit' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'leave_address' => 'required|string',
            'phone_number' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $userId = $request->input('user_id', auth()->id());
        $user = User::findOrFail($userId);

        // Upload attachment jika ada
        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('leaves/attachments', 'public');
            $attachmentType = $file->getClientMimeType();
        }

        // Lakukan verifikasi AI
        $params = $request->all();
        $params['has_attachment'] = !empty($attachmentPath);
        $aiResult = $this->leaveService->verifyLeaveEligibility($user, $params, $request->file('attachment'));

        // Jika ditolak oleh aturan hukum dan pengguna bukan Admin/Kasubag yang memaksa bypass
        if ($aiResult['status'] === 'Ditolak' && !in_array(auth()->user()->role, ['Admin', 'Kasubag'])) {
            return back()->withInput()->withErrors([
                'leave_type' => 'Pengajuan tidak dapat diproses karena melanggar regulasi ASN: ' . implode(' ', $aiResult['errors'])
            ]);
        }

        $currentYear = (int) Carbon::parse($request->start_date)->year;
        $balance = LeaveBalance::getOrCreateForUser($user, $currentYear);

        // Atasan Langsung
        $supervisor = null;
        if ($request->filled('supervisor_id')) {
            $supervisor = User::find($request->supervisor_id);
        }

        // Pejabat Pembina Kepegawaian (PPK / Kepala)
        $approver = null;
        if ($request->filled('approver_id')) {
            $approver = User::find($request->approver_id);
        } else {
            // Default Kepala Balai
            $approver = User::where('position', 'like', '%Kepala BPMP%')
                ->orWhere('role', 'Pimpinan')
                ->first();
        }

        $requestNumber = 'CUTI/' . date('Ymd') . '/' . str_pad(LeaveRequest::count() + 1, 4, '0', STR_PAD_LEFT);

        $leave = LeaveRequest::create([
            'user_id' => $user->id,
            'request_number' => $requestNumber,
            'request_date' => now()->toDateString(),
            'employee_name' => $request->input('employee_name', $user->name),
            'employee_nip' => $request->input('employee_nip', $user->nip ?? '-'),
            'employee_position' => $request->input('employee_position', $user->position ?? '-'),
            'employee_status' => $request->input('employee_status', $user->effective_status_kepegawaian),
            'work_duration' => $request->input('work_duration', $user->getMasaKerjaText($request->start_date)),
            'work_unit' => $request->input('work_unit', $user->unit_kerja ?? 'BPMP Kalimantan Timur'),
            'leave_type' => $request->leave_type,
            'reason' => $request->reason,
            'duration_amount' => $request->duration_amount,
            'duration_unit' => $request->duration_unit,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            // Catatan Cuti (Snapshot Kuota Sesuai Lampiran PerBKN)
            'quota_n2' => $balance->remaining_n2,
            'year_n2' => (string) ($currentYear - 2),
            'quota_n1' => $balance->remaining_n1,
            'year_n1' => (string) ($currentYear - 1),
            'quota_n' => $balance->remaining_quota,
            'year_n' => (string) $currentYear,
            'note_cuti_besar' => $request->input('note_cuti_besar', '-'),
            'note_cuti_sakit' => $request->input('note_cuti_sakit', '-'),
            'note_cuti_melahirkan' => $request->input('note_cuti_melahirkan', '-'),
            'note_cuti_alasan_penting' => $request->input('note_cuti_alasan_penting', '-'),
            'note_cltn' => $request->input('note_cltn', '-'),
            'leave_address' => $request->leave_address,
            'phone_number' => $request->phone_number,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
            // Atasan Langsung
            'supervisor_id' => $supervisor?->id,
            'supervisor_name' => $supervisor?->name,
            'supervisor_nip' => $supervisor?->nip,
            'supervisor_position' => $supervisor?->position,
            'supervisor_decision' => 'pending',
            // PPK
            'approver_id' => $approver?->id,
            'approver_name' => $approver?->name,
            'approver_nip' => $approver?->nip,
            'approver_position' => $approver?->position ?? 'Kepala BPMP Kalimantan Timur',
            'approver_decision' => 'pending',
            'status' => 'Diajukan',
            // Validasi AI
            'ai_validation_status' => $aiResult['status'],
            'ai_validation_summary' => $aiResult['summary'],
            'ai_validation_details' => $aiResult,
        ]);

        return redirect()->route('leaves.show', $leave->id)
            ->with('success', 'Permohonan Cuti berhasil diajukan dengan status verifikasi AI: ' . $aiResult['status']);
    }

    /**
     * Detail Permohonan Cuti
     */
    public function show($id)
    {
        Carbon::setLocale('id');
        $leave = LeaveRequest::with(['user', 'supervisor', 'verifier', 'approver'])->findOrFail($id);

        $currentUser = auth()->user();
        $isPrivileged = in_array($currentUser->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator']);

        // Pastikan hanya yang berhak yang bisa melihat
        if (!$isPrivileged && $leave->user_id !== $currentUser->id && $leave->supervisor_id !== $currentUser->id && $leave->approver_id !== $currentUser->id) {
            abort(403, 'Akses Ditolak.');
        }

        $canSupervisorAction = ($currentUser->id === $leave->supervisor_id || $currentUser->role === 'Admin') && $leave->supervisor_decision === 'pending';
        $canApproverAction = ($currentUser->id === $leave->approver_id || in_array($currentUser->role, ['Admin', 'Pimpinan'])) && $leave->approver_decision === 'pending';

        return view('leaves.show', compact('leave', 'canSupervisorAction', 'canApproverAction', 'isPrivileged'));
    }

    /**
     * Menu: PERSETUJUAN CUTI (Approval Panel Atasan & PPK)
     */
    public function approvals(Request $request)
    {
        Carbon::setLocale('id');
        $user = auth()->user();

        $query = LeaveRequest::with(['user', 'supervisor', 'approver']);

        // Jika bukan Admin, saring sesuai wewenang Atasan Langsung atau PPK
        if ($user->role !== 'Admin') {
            $query->where(function ($q) use ($user) {
                $q->where('supervisor_id', $user->id)
                  ->orWhere('approver_id', $user->id);
                if (in_array($user->role, ['Pimpinan', 'Kasubag'])) {
                    $q->orWhereNull('approver_id');
                }
            });
        }

        // Tab Filter: Pending vs Selesai
        $tab = $request->input('tab', 'pending');
        if ($tab === 'pending') {
            $query->where(function ($q) {
                $q->where('supervisor_decision', 'pending')
                  ->orWhere('approver_decision', 'pending');
            })->whereNotIn('status', ['Disetujui', 'Ditolak', 'Tidak Disetujui']);
        } else {
            $query->whereIn('status', ['Disetujui', 'Ditolak', 'Tidak Disetujui', 'Perubahan', 'Ditangguhkan']);
        }

        $pendingApprovals = $query->latest()->paginate(10)->withQueryString();

        $counts = [
            'pending' => LeaveRequest::where(function ($q) use ($user) {
                if ($user->role !== 'Admin') {
                    $q->where('supervisor_id', $user->id)->orWhere('approver_id', $user->id);
                }
            })->where(function ($q) {
                $q->where('supervisor_decision', 'pending')->orWhere('approver_decision', 'pending');
            })->whereNotIn('status', ['Disetujui', 'Ditolak', 'Tidak Disetujui'])->count(),
            'done' => LeaveRequest::where(function ($q) use ($user) {
                if ($user->role !== 'Admin') {
                    $q->where('supervisor_id', $user->id)->orWhere('approver_id', $user->id);
                }
            })->whereIn('status', ['Disetujui', 'Ditolak', 'Tidak Disetujui', 'Perubahan', 'Ditangguhkan'])->count(),
        ];

        return view('leaves.approvals', compact('pendingApprovals', 'counts', 'tab'));
    }

    /**
     * Memproses Pertimbangan Atasan Langsung atau Keputusan PPK
     */
    public function processApproval(Request $request, $id)
    {
        $request->validate([
            'action_type' => 'required|in:supervisor,approver,verifier',
            'decision' => 'required|in:DISETUJUI,PERUBAHAN,DITANGGUHKAN,TIDAK DISETUJUI',
            'notes' => 'nullable|string',
        ]);

        $leave = LeaveRequest::with('user')->findOrFail($id);
        $user = auth()->user();

        if ($request->action_type === 'supervisor') {
            // Verifikasi bahwa user adalah atasan atau admin
            if ($leave->supervisor_id !== $user->id && !in_array($user->role, ['Admin', 'Kasubag', 'Pimpinan'])) {
                abort(403, 'Anda bukan atasan langsung yang ditugaskan.');
            }

            $leave->supervisor_name = $user->name;
            $leave->supervisor_nip = $user->nip;
            $leave->supervisor_position = $user->position;
            $leave->supervisor_decision = $request->decision;
            $leave->supervisor_notes = $request->notes;
            $leave->supervisor_action_at = now();

            if ($request->decision === 'DISETUJUI') {
                $leave->status = 'Disetujui Atasan';
            } elseif ($request->decision === 'TIDAK DISETUJUI') {
                $leave->status = 'Tidak Disetujui';
            } else {
                $leave->status = $request->decision;
            }

            $leave->save();

            return back()->with('success', "Pertimbangan Atasan Langsung ({$request->decision}) berhasil disimpan.");
        }

        if ($request->action_type === 'approver') {
            // Verifikasi bahwa user adalah PPK / Kepala atau admin
            if ($leave->approver_id !== $user->id && !in_array($user->role, ['Admin', 'Pimpinan', 'Kasubag'])) {
                abort(403, 'Akses Pejabat Pembina Kepegawaian (PPK) Ditolak.');
            }

            $leave->approver_name = $user->name;
            $leave->approver_nip = $user->nip;
            $leave->approver_position = $user->position ?? 'Kepala BPMP Kalimantan Timur';
            $leave->approver_decision = $request->decision;
            $leave->approver_notes = $request->notes;
            $leave->approver_action_at = now();

            if ($request->decision === 'DISETUJUI') {
                $leave->status = 'Disetujui';
                // Potong kuota cuti secara otomatis
                $this->leaveService->deductBalanceOnApproval($leave);
            } elseif ($request->decision === 'TIDAK DISETUJUI') {
                $leave->status = 'Ditolak';
            } else {
                $leave->status = $request->decision;
            }

            $leave->save();

            return back()->with('success', "Keputusan Pejabat yang Berwenang ({$request->decision}) berhasil ditetapkan.");
        }

        return back();
    }

    /**
     * Menu: PRINT PENGAJUAN CUTI (Tampilan Resmi Sesuai Lampiran PerBKN No. 24/2017)
     */
    public function print($id, Request $request)
    {
        Carbon::setLocale('id');
        $leave = LeaveRequest::with(['user', 'supervisor', 'approver'])->findOrFail($id);
        $type = $request->query('view', 'form'); // 'form' = Formulir BKN, 'letter' = Surat Pengantar

        return view('leaves.print', compact('leave', 'type'));
    }

    /**
     * Download PDF Resmi
     */
    public function downloadPdf($id, Request $request)
    {
        Carbon::setLocale('id');
        $leave = LeaveRequest::with(['user', 'supervisor', 'approver'])->findOrFail($id);
        $type = $request->query('view', 'form');

        $viewName = $type === 'letter' ? 'leaves.pdf_letter' : 'leaves.pdf_form';
        $pdf = Pdf::loadView($viewName, compact('leave'));
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'dpi' => 150,
            'defaultFont' => 'Bookman',
        ]);

        $fileName = ($type === 'letter' ? 'Surat_Permohonan_Cuti_' : 'Formulir_Cuti_BKN_') . str_replace(['/', ' '], '_', $leave->employee_name) . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Menu: KELOLA SALDO CUTI (Admin / Kasubag)
     */
    public function manageBalances(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator'])) {
            abort(403, 'Akses Ditolak.');
        }

        $year = (int) $request->input('year', now()->year);
        $users = User::orderBy('name')->paginate(15)->withQueryString();

        $balances = [];
        foreach ($users as $u) {
            $balances[$u->id] = LeaveBalance::getOrCreateForUser($u, $year);
        }

        return view('leaves.balances', compact('users', 'balances', 'year'));
    }

    /**
     * Update Saldo Cuti Manual oleh Admin
     */
    public function updateBalance(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag'])) {
            abort(403, 'Akses Ditolak.');
        }

        $balance = LeaveBalance::findOrFail($id);
        $balance->update($request->only([
            'initial_quota',
            'used_quota',
            'remaining_quota',
            'remaining_n1',
            'remaining_n2',
            'notes',
        ]));

        return back()->with('success', 'Saldo cuti pegawai berhasil diperbarui.');
    }

    /**
     * Konfigurasi Skenario Kuota Bersamaan (Lembaga vs Gugus Mutu, 5% - 10%)
     */
    public function updateConcurrentSettings(Request $request)
    {
        if (!in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan'])) {
            abort(403, 'Akses Ditolak.');
        }

        $request->validate([
            'leave_concurrent_scenario' => 'required|in:lembaga,gugus_mutu',
            'leave_concurrent_percentage' => 'required|numeric|min:1|max:50',
        ]);

        \App\Models\Setting::set('leave_concurrent_scenario', $request->leave_concurrent_scenario);
        \App\Models\Setting::set('leave_concurrent_percentage', $request->leave_concurrent_percentage);
        \App\Models\Setting::set('leave_concurrent_strict', $request->boolean('leave_concurrent_strict') ? 1 : 0);

        $scenarioLabel = $request->leave_concurrent_scenario === 'gugus_mutu' ? 'Skenario Gugus Mutu' : 'Skenario Lembaga (Satker)';

        return back()->with('success', "Pengaturan kuota cuti bersamaan berhasil disimpan: {$scenarioLabel} ({$request->leave_concurrent_percentage}%).");
    }
}
