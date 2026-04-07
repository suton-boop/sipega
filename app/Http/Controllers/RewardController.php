<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use DB;

class RewardController extends Controller
{
    /**
     * Tampilkan SIPEGA Reward Center (Wall of Fame/Shame)
     */
    public function index()
    {
        // --- Setting: Master Switch Wall of Fame ---
        $isActive = \App\Models\Setting::get('is_reward_active', '1');
        if ($isActive === '0' && !auth()->user()->hasRole(['Admin', 'Pimpinan'])) {
            return redirect()->route('dashboard');
        }

        $monthYear = now()->format('m-Y');

        // 🏆 Top 3 "Pegawai Teladan" (Wall of Fame)
        // Kriteria: Skor > 90 + Terpilih dlm Voting
        $wallOfFame = User::where('performance_score', '>=', 90)
            ->withCount(['receivedVotes' => function($q) use ($monthYear) {
                $q->where('month_year', $monthYear);
            }])
            ->orderBy('received_votes_count', 'desc')
            ->orderBy('performance_score', 'desc')
            ->take(5)->get();

        // 🐢 Top 3 "Perlu Pembinaan" (Wall of Shame)
        $wallOfShame = User::where('performance_color', 'Merah')
            ->orderBy('performance_score', 'asc')
            ->take(3)->get();

        // Cek apakah user sudah vote bulan ini
        $myVote = Vote::where('voter_id', auth()->id())
            ->where('month_year', $monthYear)
            ->first();

        $allPegawai = User::where('id', '!=', auth()->id())->get();

        return view('reward.index', compact('wallOfFame', 'wallOfShame', 'myVote', 'allPegawai'));
    }

    /**
     * Simpan Vote Rekan Kerja Terbaik
     */
    public function vote(Request $request)
    {
        $request->validate([
            'target_id' => 'required|exists:users,id|not_in:' . auth()->id(),
            'comment' => 'nullable|string|max:100'
        ]);

        $monthYear = now()->format('m-Y');

        try {
            Vote::create([
                'voter_id' => auth()->id(),
                'target_id' => $request->target_id,
                'month_year' => $monthYear,
                'comment' => $request->comment
            ]);
            
            return back()->with('success', 'Terima kasih! Dukungan Anda untuk rekan teladan telah masuk ke Wall of Fame SIPEGA.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses suara: Anda kemungkinan besar sudah melakukan voting bulan ini.');
        }
    }
}
