<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssignmentLetter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;

class AssignmentLetterController extends Controller
{
    /**
     * Pengajuan SK & Surat Tugas (SIPEGA-Assign)
     */
    public function index()
    {
        $user = auth()->user();
        $myAssignments = AssignmentLetter::whereHas('users', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })->latest()->get();

        $allUsers = User::orderBy('name')->get();

        return view('assignments.index', compact('myAssignments', 'allUsers', 'user'));
    }

    /**
     * Manajemen Surat Tugas (SIPEGA-Assign)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'is_private' => 'required|boolean',
            'type' => 'required|in:Individu,Kolektif',
            'assigned_users' => 'required|array',
            'assigned_users.*' => 'exists:users,id',
            'justification' => 'nullable|string', // Added for Flowchart Logic 3
        ]);

        $date = $request->date;
        $users = User::whereIn('id', $request->assigned_users)->get();
        
        // --- LOGIC 3: Override Justification ---
        $needJustification = $users->contains(fn($u) => in_array($u->performance_color, ['Kuning', 'Merah']));
        if ($needJustification && empty($request->justification)) {
            return back()->withErrors(['justification' => "PERINGATAN: Terdapat pegawai dengan performa Kuning/Merah. Anda WAJIB mengisi 'Justifikasi/Catatan Pimpinan' untuk melanjutkan penerbitan ST ini."])->withInput();
        }

        // Fitur Wajib 2: Deteksi Bentrok Jadwal
        foreach ($request->assigned_users as $userId) {
            $isConflict = DB::table('assignment_letter_user')
                ->join('assignment_letters', 'assignment_letter_user.assignment_letter_id', '=', 'assignment_letters.id')
                ->where('assignment_letter_user.user_id', $userId)
                ->where('assignment_letters.date', $date)
                ->exists();

            if ($isConflict) {
                $userConflict = User::find($userId);
                return back()->withErrors(['assigned_users' => "Bentrok jadwal: Pegawai {$userConflict->name} sudah memiliki Surat Tugas lain pada tanggal {$date}."]);
            }
        }

        // Simpan Data Surat
        $letter = AssignmentLetter::create([
            'letter_number' => 'ST/SIPEGA/' . time(),
            'title' => $request->title,
            'description' => $request->description,
            'justification' => $request->justification, // Added here
            'date' => $date,
            'is_private' => $request->is_private,
            'type' => $request->type,
            'created_by' => auth()->id() ?? 1,
        ]);

        // Proses Sinkronisasi Multi-select ke database pivot "assignment_letter_user"
        $letter->users()->attach($request->assigned_users);

        // Fitur Wajib 4: Integrasi Telegram Bot (Notifikasi Otomatis)
        $this->sendTelegramNotification($letter, $request->assigned_users);

        return redirect()->back()->with('success', 'Surat Tugas SIPEGA berhasil diterbitkan sesuai panduan Logic Flowchart.');
    }

    /**
     * Fitur Wajib 3: Generator PDF dengan 2 template
     */
    public function generatePdf($id)
    {
        $letter = AssignmentLetter::with('users', 'creator')->findOrFail($id);
        
        $pdf = Pdf::loadView('pdf.assignment_letter', compact('letter'));
        
        return $pdf->download(str_replace('/', '-', $letter->letter_number) . '.pdf');
    }

    /**
     * Logika Notifikasi Telegram Bot 
     */
    private function sendTelegramNotification(AssignmentLetter $letter, array $userIds)
    {
        $users = User::whereIn('id', $userIds)->get();

        if ($letter->is_private) {
            // Sifat Surat: Private (Hanya dikirim Japri ke pegawai terkait)
            foreach ($users as $user) {
                // Simulasi Telegram Bot API direct message (butuh setting chat_id pegawai)
                Log::info("TELEGRAM JAPRI: Mengirim notifikasi ST {$letter->title} ke ID/Akun Telegram User: {$user->name}");
                // Telegram::sendMessage(['chat_id' => $user->telegram_id, 'text' => "Anda mendapat tugas tertutup."]);
            }
        } else {
            // Sifat Surat: Publik (Bisa dibroadcast ke Channel/Grup Telegram Dinas)
            Log::info("TELEGRAM GRUP: Broadcast Notifikasi ST {$letter->title} bahwa " . count($users) . " pegawai ditugaskan.");
            // Telegram::sendMessage(['chat_id' => env('TELEGRAM_GROUP_ID'), 'text' => "Tugas Baru diterbitkan..."]);
        }
    }
}
