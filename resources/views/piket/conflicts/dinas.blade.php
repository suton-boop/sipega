<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight tracking-tight flex items-center gap-2">
                    <span>📋</span> Penugasan Kedinasan & Agenda Pegawai
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Monitoring data kegiatan Surat Tugas (ST), Perjalanan Dinas Luar, dan Cuti ASN aktif BPMP Kaltim
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('piket.conflicts.index') }}" class="px-3.5 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg text-xs font-bold transition flex items-center gap-1.5 border border-rose-200">
                    <span>⚠️</span> Buka Deteksi Konflik
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @include('piket.partials.nav')

            <!-- FILTER RENTANG TANGGAL -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('piket.conflicts.dinas') }}" class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Dari Tanggal:</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="text-xs rounded-lg border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium">
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Sampai Tanggal:</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="text-xs rounded-lg border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium">
                    </div>

                    <button type="submit" class="px-4 py-2 bg-sipega-navy text-white text-xs font-bold rounded-lg hover:bg-sipega-navy/90 transition shadow-sm">
                        Filter Periode
                    </button>

                    <a href="{{ route('piket.conflicts.dinas') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700 ml-auto">
                        Bulan Ini
                    </a>
                </form>
            </div>

            <!-- DUA KOLOM: SURAT TUGAS & CUTI PEGAWAI -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- 1. SURAT TUGAS KEDINASAN -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden flex flex-col">
                    <div class="p-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">ST</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Surat Tugas / Perjalanan Dinas</h3>
                                <p class="text-[11px] text-slate-500">Penugasan resmi pegawai ke luar kantor / kegiatan dinas</p>
                            </div>
                        </div>
                        <span class="text-xs font-black bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full border border-indigo-200">
                            {{ $assignmentLetters->total() }} Data
                        </span>
                    </div>

                    <div class="p-4 flex-1 overflow-x-auto space-y-3">
                        @forelse($assignmentLetters as $st)
                            <div class="p-3.5 rounded-xl border border-slate-200 hover:border-indigo-300 transition bg-white shadow-sm space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="text-[11px] font-bold text-indigo-700 font-mono">{{ $st->letter_number ?? 'Nomor Belum Terbit' }}</div>
                                        <div class="font-black text-slate-800 text-xs mt-0.5">{{ $st->destination ?? $st->activity_name ?? 'Kegiatan Dinas' }}</div>
                                    </div>
                                    <div class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700 whitespace-nowrap">
                                        📅 {{ \Carbon\Carbon::parse($st->date)->translatedFormat('d M') }} 
                                        @if($st->end_date && $st->end_date != $st->date)
                                            - {{ \Carbon\Carbon::parse($st->end_date)->translatedFormat('d M Y') }}
                                        @endif
                                    </div>
                                </div>

                                <div class="text-[11px] text-slate-600 line-clamp-2">{{ $st->description ?? $st->purpose ?? '-' }}</div>

                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px]">
                                    <div class="text-slate-500 font-medium">
                                        Pegawai Ditugaskan: 
                                        <strong class="text-slate-800">
                                            @if($st->users && $st->users->count() > 0)
                                                {{ $st->users->pluck('name')->join(', ') }}
                                            @else
                                                -
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-10 text-center text-slate-400 text-xs">
                                Tidak ada data Surat Tugas pada rentang tanggal ini.
                            </div>
                        @endforelse
                    </div>

                    @if($assignmentLetters->hasPages())
                        <div class="p-3 border-t border-slate-100 bg-slate-50/50">
                            {{ $assignmentLetters->links() }}
                        </div>
                    @endif
                </div>

                <!-- 2. CUTI PEGAWAI BERJALAN -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden flex flex-col">
                    <div class="p-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">🏖️</span>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Cuti Pegawai Aktif / Disetujui</h3>
                                <p class="text-[11px] text-slate-500">Pegawai yang sedang izin cuti resmi ASN</p>
                            </div>
                        </div>
                        <span class="text-xs font-black bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full border border-amber-200">
                            {{ $leaveRequests->total() }} Data
                        </span>
                    </div>

                    <div class="p-4 flex-1 overflow-x-auto space-y-3">
                        @forelse($leaveRequests as $leave)
                            <div class="p-3.5 rounded-xl border border-slate-200 hover:border-amber-300 transition bg-white shadow-sm space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="font-black text-slate-800 text-xs">{{ $leave->user->name ?? $leave->employee_name ?? 'Pegawai' }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono">{{ $leave->user->nip ?? $leave->nip ?? '-' }}</div>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ in_array(strtolower($leave->status), ['disetujui', 'approved']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $leave->status }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-slate-700">{{ $leave->leave_type ?? 'Cuti Tahunan' }}</span>
                                    <span class="text-slate-500 font-medium">
                                        📅 {{ \Carbon\Carbon::parse($leave->start_date)->translatedFormat('d M') }} s.d. {{ \Carbon\Carbon::parse($leave->end_date)->translatedFormat('d M Y') }}
                                        ({{ $leave->days_count ?? '1' }} hari)
                                    </span>
                                </div>

                                @if($leave->reason)
                                    <div class="text-[10px] text-slate-500 italic bg-slate-50 p-2 rounded">
                                        "{{ $leave->reason }}"
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="py-10 text-center text-slate-400 text-xs">
                                Tidak ada data cuti aktif pada rentang tanggal ini.
                            </div>
                        @endforelse
                    </div>

                    @if($leaveRequests->hasPages())
                        <div class="p-3 border-t border-slate-100 bg-slate-50/50">
                            {{ $leaveRequests->links() }}
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
