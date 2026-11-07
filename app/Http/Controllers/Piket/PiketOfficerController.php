<?php

namespace App\Http\Controllers\Piket;

use App\Http\Controllers\Controller;
use App\Models\PiketEligibleEmployee;
use App\Models\PiketUnit;
use App\Models\User;
use Illuminate\Http\Request;

class PiketOfficerController extends Controller
{
    public function index(Request $request)
    {
        $unitId = $request->input('unit_id');
        $role = $request->input('assignment_role');
        $search = $request->input('search');

        $units = PiketUnit::where('is_active', true)->get();

        $query = PiketEligibleEmployee::with(['user', 'unit']);

        if ($unitId) {
            $query->where('piket_unit_id', $unitId);
        }

        if ($role) {
            $query->where('assignment_role', $role);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        $officers = $query->paginate(15)->withQueryString();

        // Pegawai BPMP yang belum masuk ke unit tertentu untuk modal tambah
        $availableEmployees = User::realPegawai()->where('is_active', true)->orderBy('name')->get();

        return view('piket.officers.index', compact('officers', 'units', 'availableEmployees', 'unitId', 'role', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'piket_unit_id' => 'required|exists:piket_units,id',
            'assignment_role' => 'required|in:utama,cadangan,keduanya',
            'max_shifts_per_month' => 'nullable|integer|min:1|max:20',
        ]);

        PiketEligibleEmployee::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'piket_unit_id' => $request->piket_unit_id,
            ],
            [
                'assignment_role' => $request->assignment_role,
                'is_available' => true,
                'max_shifts_per_month' => $request->max_shifts_per_month ?: 4,
            ]
        );

        return back()->with('success', 'Petugas piket berhasil ditambahkan ke daftar unit pelayanan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'assignment_role' => 'required|in:utama,cadangan,keduanya',
            'is_available' => 'required|boolean',
            'unavailability_reason' => 'nullable|string',
            'max_shifts_per_month' => 'required|integer|min:1|max:20',
        ]);

        $officer = PiketEligibleEmployee::findOrFail($id);
        $officer->update([
            'assignment_role' => $request->assignment_role,
            'is_available' => $request->is_available,
            'unavailability_reason' => $request->is_available ? null : $request->unavailability_reason,
            'max_shifts_per_month' => $request->max_shifts_per_month,
        ]);

        return back()->with('success', "Data penugasan petugas {$officer->user->name} berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $officer = PiketEligibleEmployee::findOrFail($id);
        $name = $officer->user->name;
        $officer->delete();

        return back()->with('success', "Pegawai {$name} telah dikeluarkan dari daftar petugas piket.");
    }
}
