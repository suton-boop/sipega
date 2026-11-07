<?php

namespace App\Http\Controllers\Piket;

use App\Http\Controllers\Controller;
use App\Models\PiketPublication;
use App\Models\PiketSchedule;
use App\Models\PiketScheduleAssignment;
use App\Models\PiketUnit;
use App\Models\User;
use App\Services\PiketConflictDetectionService;
use App\Services\PiketScheduleGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PiketScheduleController extends Controller
{
    protected PiketScheduleGeneratorService $generatorService;
    protected PiketConflictDetectionService $conflictService;

    public function __construct(
        PiketScheduleGeneratorService $generatorService,
        PiketConflictDetectionService $conflictService
    ) {
        $this->generatorService = $generatorService;
        $this->conflictService = $conflictService;
    }

    /**
     * Submenu 3: Daftar Jadwal Piket
     */
    public function index(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        $unitId = $request->input('unit_id');

        $units = PiketUnit::where('is_active', true)->get();

        $query = PiketSchedule::with(['unit', 'creator', 'publisher'])
            ->where('year', $year);

        if ($unitId) {
            $query->where('piket_unit_id', $unitId);
        }

        $schedules = $query->latest('id')->paginate(10)->withQueryString();

        return view('piket.schedules.index', compact('schedules', 'units', 'year', 'unitId'));
    }

    /**
     * Form Generate Jadwal Baru
     */
    public function create()
    {
        $units = PiketUnit::where('is_active', true)->withCount('eligibleEmployees')->get();
        return view('piket.schedules.create', compact('units'));
    }

    /**
     * Eksekusi Generate Jadwal Otomatis
     */
    public function store(Request $request)
    {
        $request->validate([
            'piket_unit_id' => 'required|exists:piket_units,id',
            'year' => 'required|integer|min:2024|max:2035',
            'period_type' => 'required|in:tahunan,bulanan',
            'month' => 'nullable|required_if:period_type,bulanan|integer|min:1|max:12',
            'title' => 'nullable|string|max:255',
        ]);

        try {
            $schedule = $this->generatorService->generate(
                (int) $request->piket_unit_id,
                (int) $request->year,
                $request->period_type === 'bulanan' ? (int) $request->month : null,
                $request->title,
                auth()->user()
            );

            return redirect()->route('piket.schedules.show', $schedule->id)
                ->with('success', "✓ Jadwal piket '{$schedule->title}' berhasil digenerate otomatis! Silakan tinjau penugasan harian dan deteksi konflik.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyusun jadwal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Tampilan Detail Jadwal Harian
     */
    public function show(Request $request, $id)
    {
        Carbon::setLocale('id');
        $schedule = PiketSchedule::with(['unit', 'creator', 'publisher', 'publication'])->findOrFail($id);

        $monthFilter = $request->input('month');
        $statusFilter = $request->input('status');
        $search = $request->input('search');

        $query = PiketScheduleAssignment::where('piket_schedule_id', $schedule->id)
            ->with(['primaryOfficer', 'backupOfficer', 'activeOfficer', 'conflicts', 'replacement.replacementOfficer']);

        if ($monthFilter) {
            $query->whereMonth('date', $monthFilter);
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('primaryOfficer', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('activeOfficer', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $assignments = $query->orderBy('date')->orderBy('role_label')->paginate(20)->withQueryString();

        // Audit ringkas
        $totalDays = PiketScheduleAssignment::where('piket_schedule_id', $schedule->id)->distinct('date')->count();
        $conflictsCount = PiketScheduleAssignment::where('piket_schedule_id', $schedule->id)->where('has_conflict', true)->count();
        $replacedCount = PiketScheduleAssignment::where('piket_schedule_id', $schedule->id)->where('is_replaced', true)->count();

        return view('piket.schedules.show', compact('schedule', 'assignments', 'totalDays', 'conflictsCount', 'replacedCount', 'monthFilter', 'statusFilter', 'search'));
    }

    /**
     * Audit Ulang Konflik Seluruh Jadwal
     */
    public function auditConflicts($id)
    {
        $res = $this->generatorService->auditScheduleConflicts($id);
        return back()->with('success', "Audit selesai: Diperiksa {$res['total_assignments']} slot penugasan. Ditemukan {$res['conflict_count']} potensi benturan jadwal dengan penugasan dinas.");
    }

    /**
     * Submenu 4: Kalender Piket Interaktif
     */
    public function calendar(Request $request)
    {
        Carbon::setLocale('id');
        $month = (int) $request->input('month', date('n'));
        $year = (int) $request->input('year', date('Y'));
        $unitId = $request->input('unit_id');

        $units = PiketUnit::where('is_active', true)->get();

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $query = PiketScheduleAssignment::with(['unit', 'activeOfficer', 'primaryOfficer', 'backupOfficer'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($unitId) {
            $query->where('piket_unit_id', $unitId);
        }

        $assignments = $query->get()->groupBy(fn($item) => $item->date->toDateString());

        return view('piket.schedules.calendar', compact('units', 'assignments', 'month', 'year', 'unitId', 'startDate', 'endDate'));
    }

    /**
     * Submenu 9: Formulir Penerbitan Jadwal Resmi
     */
    public function publishForm($id)
    {
        $schedule = PiketSchedule::with(['unit', 'publication'])->findOrFail($id);
        $kepala = User::where('role', 'Pimpinan')->first() ?: User::where('position', 'like', '%Kepala%')->first();

        return view('piket.schedules.publish', compact('schedule', 'kepala'));
    }

    /**
     * Eksekusi Penerbitan Jadwal Resmi
     */
    public function publish(Request $request, $id)
    {
        $request->validate([
            'document_number' => 'required|string|max:100',
            'effective_date' => 'required|date',
            'signatory_name' => 'required|string|max:255',
            'signatory_nip' => 'required|string|max:50',
            'signatory_position' => 'required|string|max:255',
        ]);

        $schedule = PiketSchedule::findOrFail($id);

        $pub = PiketPublication::updateOrCreate(
            ['piket_schedule_id' => $schedule->id],
            [
                'piket_unit_id' => $schedule->piket_unit_id,
                'document_number' => $request->document_number,
                'year' => $schedule->year,
                'title' => $schedule->title,
                'effective_date' => $request->effective_date,
                'signatory_name' => $request->signatory_name,
                'signatory_nip' => $request->signatory_nip,
                'signatory_position' => $request->signatory_position,
                'version_tag' => $schedule->status === 'published' ? 'Rev.' . ((int) filter_var($schedule->revision_number, FILTER_SANITIZE_NUMBER_INT) + 1) : '1.0',
            ]
        );

        $schedule->update([
            'status' => 'published',
            'published_by' => auth()->id(),
            'published_at' => now('Asia/Makassar'),
            'revision_number' => $pub->version_tag,
        ]);

        return redirect()->route('piket.schedules.show', $schedule->id)
            ->with('success', "✓ Jadwal piket '{$schedule->title}' RESMI DITERBITKAN dengan No. Dokumen: {$pub->document_number}!");
    }

    /**
     * Cetak Lembar Jadwal Resmi ke PDF (dengan Kop BPMP Kaltim 2026)
     */
    public function downloadPdf($id)
    {
        Carbon::setLocale('id');
        $schedule = PiketSchedule::with(['unit', 'publication', 'assignments.activeOfficer', 'assignments.backupOfficer'])
            ->findOrFail($id);

        $assignments = $schedule->assignments()->orderBy('date')->get()->groupBy(fn($i) => $i->date->toDateString());

        $pdf = Pdf::loadView('piket.pdf.jadwal_resmi', compact('schedule', 'assignments'))
            ->setPaper('a4', 'landscape');

        $filename = 'Jadwal_Piket_' . str_replace(' ', '_', $schedule->unit->code) . '_' . $schedule->year . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Submenu 12: Pengaturan Master Unit Piket
     */
    public function settings()
    {
        $units = PiketUnit::all();
        return view('piket.schedules.settings', compact('units'));
    }

    /**
     * Update Pengaturan Unit Piket
     */
    public function updateUnitSettings(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'daily_officers_count' => 'required|integer|min:1|max:10',
            'daily_backups_count' => 'required|integer|min:0|max:5',
        ]);

        $unit = PiketUnit::findOrFail($id);
        $unit->update($request->only([
            'name',
            'location',
            'start_time',
            'end_time',
            'daily_officers_count',
            'daily_backups_count',
        ]));

        return back()->with('success', "Pengaturan operasional unit {$unit->name} berhasil disimpan.");
    }
}
