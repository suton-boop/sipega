<?php

namespace App\Http\Controllers;

use App\Models\MikPermit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MikController extends Controller
{
    /**
     * DASHBOARD MIK (Monitoring Izin Karyawan - Halaman Utama)
     * Menampilkan 2 Section Sekaligus: KANTOR & RUANGAN sesuai tampilan MiK
     */
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        // Filter tanggal opsional (default hari ini atau semua)
        $date = $request->input('date');

        // Base Queries
        $kantorQuery = MikPermit::kantor()->with('user');
        $ruanganQuery = MikPermit::ruangan()->with('user');

        if ($date) {
            $kantorQuery->whereDate('date', $date);
            $ruanganQuery->whereDate('date', $date);
        }

        // Stats KANTOR
        $statsKantor = [
            'belum_direspon' => (clone $kantorQuery)->where('status', 'belum direspon')->count(),
            'disetujui' => (clone $kantorQuery)->where('status', 'disetujui')->count(),
            'tidak_disetujui' => (clone $kantorQuery)->where('status', 'tidak disetujui')->count(),
            'dibatalkan' => (clone $kantorQuery)->where('status', 'dibatalkan')->count(),
        ];

        // Stats RUANGAN
        $statsRuangan = [
            'belum_direspon' => (clone $ruanganQuery)->where('status', 'belum direspon')->count(),
            'disetujui' => (clone $ruanganQuery)->where('status', 'disetujui')->count(),
            'tidak_disetujui' => (clone $ruanganQuery)->where('status', 'tidak disetujui')->count(),
            'dibatalkan' => (clone $ruanganQuery)->where('status', 'dibatalkan')->count(),
        ];

        $kantorList = $kantorQuery->latest('id')->take(10)->get();
        $ruanganList = $ruanganQuery->latest('id')->take(10)->get();
        $recentKantor = $kantorList;
        $recentRuangan = $ruanganList;

        $employees = User::orderBy('name')->get();

        return view('mik.index', compact('statsKantor', 'statsRuangan', 'kantorList', 'ruanganList', 'recentKantor', 'recentRuangan', 'employees', 'date'));
    }

    /**
     * FORMULIR & MONITORING IZIN KELUAR KANTOR
     */
    public function formKantor(Request $request)
    {
        Carbon::setLocale('id');

        $query = MikPermit::kantor()->with('user');

        if ($request->filled('name')) {
            $query->where('employee_name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('date', $request->start_date);
        }

        if ($request->filled('start_time')) {
            $query->where('out_time', '>=', $request->start_time);
        }
        if ($request->filled('end_time')) {
            $query->where(function($q) use ($request) {
                $q->where('return_time', '<=', $request->end_time)
                  ->orWhere('estimated_return_time', '<=', $request->end_time)
                  ->orWhere('out_time', '<=', $request->end_time);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Stat Counters
        $baseStatQuery = MikPermit::kantor();
        $stats = [
            'belum_direspon' => (clone $baseStatQuery)->where('status', 'belum direspon')->count(),
            'disetujui' => (clone $baseStatQuery)->where('status', 'disetujui')->count(),
            'tidak_disetujui' => (clone $baseStatQuery)->where('status', 'tidak disetujui')->count(),
            'dibatalkan' => (clone $baseStatQuery)->where('status', 'dibatalkan')->count(),
        ];

        $permits = $query->latest('date')->latest('out_time')->paginate(15)->withQueryString();
        $employees = User::orderBy('name')->get();

        return view('mik.form_kantor', compact('permits', 'stats', 'employees'));
    }

    /**
     * FORMULIR & MONITORING IZIN KELUAR RUANGAN
     */
    public function formRuangan(Request $request)
    {
        Carbon::setLocale('id');

        $query = MikPermit::ruangan()->with('user');

        if ($request->filled('name')) {
            $query->where('employee_name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('date', $request->start_date);
        }

        if ($request->filled('start_time')) {
            $query->where('out_time', '>=', $request->start_time);
        }
        if ($request->filled('end_time')) {
            $query->where(function($q) use ($request) {
                $q->where('return_time', '<=', $request->end_time)
                  ->orWhere('estimated_return_time', '<=', $request->end_time)
                  ->orWhere('out_time', '<=', $request->end_time);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Stat Counters
        $baseStatQuery = MikPermit::ruangan();
        $stats = [
            'belum_direspon' => (clone $baseStatQuery)->where('status', 'belum direspon')->count(),
            'disetujui' => (clone $baseStatQuery)->where('status', 'disetujui')->count(),
            'tidak_disetujui' => (clone $baseStatQuery)->where('status', 'tidak disetujui')->count(),
            'dibatalkan' => (clone $baseStatQuery)->where('status', 'dibatalkan')->count(),
        ];

        $permits = $query->latest('date')->latest('out_time')->paginate(15)->withQueryString();
        $employees = User::orderBy('name')->get();

        return view('mik.form_ruangan', compact('permits', 'stats', 'employees'));
    }

    /**
     * SIMPAN PERMOHONAN IZIN (Keluar Kantor atau Keluar Ruangan)
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:kantor,ruangan',
            'date' => 'required|date',
            'out_time' => 'required',
            'estimated_return_time' => 'nullable',
            'destination' => 'nullable|string|max:255',
            'purpose' => 'required|string',
            'responder_role' => 'required|string',
        ]);

        $user = auth()->user();
        if ($request->filled('user_id') && in_array($user->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator'])) {
            $targetUser = User::find($request->user_id) ?: $user;
        } else {
            $targetUser = $user;
        }

        // Tentukan default responder nama jika relevan
        $responderId = null;
        $responderName = $request->responder_role;

        // Auto-assign responder user jika sesuai role
        if ($request->responder_role === 'Kasubbag. Umum') {
            $kasubag = User::where('role', 'Kasubag')->orWhere('position', 'like', '%Kasubag%')->first();
            if ($kasubag) {
                $responderId = $kasubag->id;
                $responderName = $kasubag->name;
            }
        } elseif ($request->responder_role === 'Kepala') {
            $kepala = User::where('role', 'Pimpinan')->orWhere('position', 'like', '%Kepala%')->first();
            if ($kepala) {
                $responderId = $kepala->id;
                $responderName = $kepala->name;
            }
        }

        MikPermit::create([
            'user_id' => $targetUser->id,
            'type' => $request->type,
            'employee_name' => $targetUser->name,
            'employee_nip' => $targetUser->nip,
            'employee_position' => $targetUser->position,
            'gugus_mutu' => $targetUser->gugus_mutu,
            'date' => $request->date,
            'out_time' => $request->out_time,
            'return_time' => null,
            'estimated_return_time' => $request->estimated_return_time,
            'destination' => $request->destination,
            'purpose' => $request->purpose,
            'status' => 'belum direspon',
            'responder_role' => $request->responder_role,
            'responder_id' => $responderId,
            'responder_name' => $responderName,
        ]);

        $redirectRoute = $request->type === 'kantor' ? 'mik.form_kantor' : 'mik.form_ruangan';
        return redirect()->route($redirectRoute)->with('success', 'Permohonan izin keluar ' . $request->type . ' berhasil dikirim.');
    }

    /**
     * CATAT WAKTU KEMBALI
     */
    public function markReturned(Request $request, $id)
    {
        $permit = MikPermit::findOrFail($id);

        $returnTime = $request->input('return_time', now()->format('H:i:s'));
        $permit->update([
            'return_time' => $returnTime,
        ]);

        return back()->with('success', 'Waktu kembali berhasil dicatat: ' . substr($returnTime, 0, 5));
    }

    /**
     * BATALKAN IZIN
     */
    public function cancel(Request $request, $id)
    {
        $permit = MikPermit::findOrFail($id);

        if ($permit->user_id !== auth()->id() && !in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan'])) {
            abort(403, 'Akses Ditolak.');
        }

        $permit->update([
            'status' => 'dibatalkan',
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Permohonan izin berhasil dibatalkan.');
    }

    /**
     * MENU PERSETUJUAN (Approval List Ketua Gugus Mutu, Kasubag, Kepala)
     */
    public function approvals(Request $request)
    {
        Carbon::setLocale('id');
        $user = auth()->user();

        $type = $request->input('type'); // all, kantor, ruangan
        $status = $request->input('status');

        $query = MikPermit::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($request->filled('start_time')) {
            $query->where('out_time', '>=', $request->start_time);
        }
        if ($request->filled('end_time')) {
            $query->where(function($q) use ($request) {
                $q->where('return_time', '<=', $request->end_time)
                  ->orWhere('estimated_return_time', '<=', $request->end_time)
                  ->orWhere('out_time', '<=', $request->end_time);
            });
        }

        if ($type === 'kantor') {
            $query->where('type', 'kantor');
        } elseif ($type === 'ruangan') {
            $query->where('type', 'ruangan');
        }

        $permits = $query->latest('date')->latest('out_time')->paginate(15)->withQueryString();
        $pendingPermits = $permits;

        $counts = [
            'pending_all' => MikPermit::where('status', 'belum direspon')->count(),
            'pending_kantor' => MikPermit::where('type', 'kantor')->where('status', 'belum direspon')->count(),
            'pending_ruangan' => MikPermit::where('type', 'ruangan')->where('status', 'belum direspon')->count(),
        ];

        return view('mik.approvals', compact('permits', 'pendingPermits', 'counts', 'type', 'user'));
    }

    /**
     * PROSES PERSETUJUAN OLEH KETUA GUGUS MUTU / KASUBAG / KEPALA
     */
    public function processApproval(Request $request, $id)
    {
        $decision = $request->input('decision', $request->input('status'));
        $notes = $request->input('notes', $request->input('note'));

        if (!in_array($decision, ['disetujui', 'tidak disetujui'])) {
            return back()->withErrors(['decision' => 'Status persetujuan tidak valid.']);
        }

        $permit = MikPermit::findOrFail($id);
        $user = auth()->user();

        // Tentukan peran responder yang sedang merespon
        $responderRole = $permit->responder_role;
        if (in_array($user->role, ['Pimpinan']) || stripos($user->position, 'Kepala') !== false) {
            $responderRole = 'Kepala';
        } elseif (in_array($user->role, ['Kasubag']) || stripos($user->position, 'Kasubag') !== false) {
            $responderRole = 'Kasubbag. Umum';
        } elseif ($user->gugus_mutu) {
            // Contoh: Ketua Gugus 1 s.d 5
            preg_match('/GM\s*(\d)/i', $user->gugus_mutu, $matches);
            $gmNum = $matches[1] ?? '';
            $responderRole = $gmNum ? 'Ketua Gugus ' . $gmNum : 'Ketua Gugus Mutu';
        }

        $permit->update([
            'status' => $decision,
            'responder_id' => $user->id,
            'responder_name' => $user->name,
            'responder_role' => $responderRole,
            'responder_notes' => $notes,
            'responded_at' => now(),
        ]);

        return back()->with('success', "Izin pegawai {$permit->employee_name} berhasil ditandai '{$decision}' oleh {$responderRole}.");
    }
}
