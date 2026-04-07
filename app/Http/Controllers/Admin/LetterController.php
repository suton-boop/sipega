<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Letter;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LetterController extends Controller
{
    public function index(Request $request)
    {
        $letters = Letter::with('users', 'creator')->latest()->get();
        return view('admin.letters.index', compact('letters'));
    }

    public function create()
    {
        $users = User::with('letters')->get();
        return view('admin.letters.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:ST,SK',
            'title' => 'required|string|max:255',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
            'justification' => 'nullable|string'
        ]);

        // Logic check: Performance & Clash for non-executives
        $dateStart = $request->date_start;
        $dateEnd = $request->date_end;
        $selectedUserIds = $request->users;
        
        $users = User::whereIn('id', $selectedUserIds)->get();
        $needsJustification = false;

        foreach ($users as $user) {
            $isExecutive = in_array($user->role, ['Pimpinan', 'Admin']); // Kasubag/Kepala usually Pimpinan/Admin
            
            // Check performance
            if (!in_array($user->performance_color, ['Biru', 'Hijau']) && !$isExecutive) {
                $needsJustification = true;
            }

            // Check clash if date exists
            if ($dateStart && $dateEnd && !$isExecutive) {
                $hasClash = DB::table('letter_user')
                    ->join('letters', 'letter_user.letter_id', '=', 'letters.id')
                    ->where('letter_user.user_id', $user->id)
                    ->where('letters.status', 'Approved')
                    ->where(function ($q) use ($dateStart, $dateEnd) {
                        $q->whereBetween('letters.date_start', [$dateStart, $dateEnd])
                          ->orWhereBetween('letters.date_end', [$dateStart, $dateEnd])
                          ->orWhere(function($q2) use ($dateStart, $dateEnd) {
                              $q2->where('letters.date_start', '<=', $dateStart)
                                 ->where('letters.date_end', '>=', $dateEnd);
                          });
                    })->exists();
                
                if ($hasClash) {
                    $needsJustification = true; // Conflicting schedules require override
                }
            }
        }

        if ($needsJustification && empty($request->justification)) {
            return back()->with('error', 'Pimpinan harus mengisi Catatan/Justifikasi karena ada pegawai yang bentrok jadwal atau skor performanya di bawah standar (Kuning/Merah).')->withInput();
        }

        // Auto format surat tugas/sk number if provided initially
        $number = $request->number;
        if ($number) {
            $number = $number . '/C6.24/KP.10.00/' . date('Y');
        }

        $letter = Letter::create([
            'type' => $request->type,
            'number' => $number,
            'title' => $request->title,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'location' => $request->location,
            'justification' => $request->justification,
            'created_by' => auth()->id(),
            'status' => 'Pending' // Requires Approval if standard flow
        ]);

        $letter->users()->attach($selectedUserIds);

        return redirect()->route('letters.index')->with('success', 'Dokumen ' . $request->type . ' berhasil diajukan dan menunggu persetujuan.');
    }

    public function downloadPdfSt($id)
    {
        $letter = Letter::with('users')->findOrFail($id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.letters.pdf', compact('letter'));
        
        // Atur ukuran kertas A4
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Surat_Tugas_' . str_replace('/', '_', $letter->number) . '.pdf');
    }

    public function downloadPdfSk($id)
    {
        $letter = Letter::with('users')->findOrFail($id);
        
        // Menggunakan blade template khusus SK yang akan kita buat
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.letters.pdf_sk', compact('letter'));
        
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Surat_Keputusan_' . str_replace('/', '_', $letter->number) . '.pdf');
    }

    public function edit($id)
    {
        $letter = Letter::with('users')->findOrFail($id);
        $users = User::with('letters')->get();
        return view('admin.letters.edit', compact('letter', 'users'));
    }

    public function update(Request $request, $id)
    {
        $letter = Letter::findOrFail($id);
        
        $request->validate([
            'type' => 'required|in:ST,SK',
            'title' => 'required|string|max:255',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
            'justification' => 'nullable|string'
        ]);

        $letter->update([
            'type' => $request->type,
            'number' => $request->number,
            'title' => $request->title,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'location' => $request->location,
            'justification' => $request->justification,
        ]);

        $letter->users()->sync($request->users);

        return redirect()->route('letters.index')->with('success', 'Dokumen berhasil diupdate.');
    }

    public function approve($id)
    {
        $letter = Letter::findOrFail($id);
        $letter->update(['status' => 'Approved']);
        return back()->with('success', 'Dokumen berhasil disetujui, PDF kini dapat diunduh/diusulkan TTE.');
    }

    public function reject(Request $request, $id)
    {
        $letter = Letter::findOrFail($id);
        // Bisa tambahkan alasan penolakan pada kolom notes/justification ke depannya
        $letter->update(['status' => 'Rejected']);
        return back()->with('success', 'Dokumen ditolak dan dikembalikan ke operator.');
    }

    public function destroy($id)
    {
        Letter::findOrFail($id)->delete();
        return redirect()->route('letters.index')->with('success', 'Dokumen berhasil dihapus.');
    }
}
