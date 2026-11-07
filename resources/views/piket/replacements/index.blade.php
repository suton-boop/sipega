<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight tracking-tight flex items-center gap-2">
                    <span>🔄</span> Riwayat Penggantian Petugas Piket
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Log transparansi penetapan petugas pengganti berdasarkan mekanisme 3 tingkat prioritas resmi
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('piket.conflicts.index') }}" class="px-3.5 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 rounded-lg text-xs font-bold transition flex items-center gap-1.5 border border-rose-200">
                    <span>🚨</span> Lihat Benturan Jadwal
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @include('piket.partials.nav')

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg shadow-sm text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- FILTER -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('piket.replacements.index') }}" class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Tingkat Prioritas:</label>
                        <select name="priority_level" onchange="this.form.submit()" class="text-xs rounded-lg border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium">
                            <option value="">Semua Tingkat</option>
                            <option value="prioritas_1_cadangan" {{ $priority === 'prioritas_1_cadangan' ? 'selected' : '' }}>Prioritas 1: Petugas Cadangan</option>
                            <option value="prioritas_2_tersedia" {{ $priority === 'prioritas_2_tersedia' ? 'selected' : '' }}>Prioritas 2: Petugas Tersedia</option>
                            <option value="prioritas_3_manual" {{ $priority === 'prioritas_3_manual' ? 'selected' : '' }}>Prioritas 3: Penunjukan Manual</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Cari Pegawai:</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Nama petugas..." class="text-xs rounded-lg border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium w-48">
                    </div>

                    <button type="submit" class="px-4 py-2 bg-sipega-navy text-white text-xs font-bold rounded-lg hover:bg-sipega-navy/90 transition">
                        Cari
                    </button>

                    <a href="{{ route('piket.replacements.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700 ml-auto">
                        Reset
                    </a>
                </form>
            </div>

            <!-- TABEL LOG PENGGANTIAN -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h3 class="text-base font-black text-slate-800">Catatan Pergantian Petugas Pelayanan</h3>
                    <p class="text-xs text-slate-500">Mencatat riwayat nama petugas awal, pengganti, alasan, dan pejabat berwenang yang menyetujui.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-black tracking-wider text-[10px]">
                            <tr>
                                <th class="py-3 px-4">Tanggal & Unit</th>
                                <th class="py-3 px-4">Petugas Awal</th>
                                <th class="py-3 px-4">Petugas Pengganti</th>
                                <th class="py-3 px-4">Tingkat Prioritas</th>
                                <th class="py-3 px-4">Alasan Pergantian</th>
                                <th class="py-3 px-4">Disetujui Oleh</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($replacements as $rep)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800">
                                            {{ $rep->assignment->date ? $rep->assignment->date->translatedFormat('d M Y') : '-' }}
                                        </div>
                                        <div class="mt-0.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ ($rep->assignment->unit->code ?? '') === 'ULT' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                                {{ $rep->assignment->unit->code ?? '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-700 line-through opacity-80">{{ $rep->originalOfficer->name ?? '-' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $rep->originalOfficer->nip ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-black text-sipega-navy flex items-center gap-1">
                                            <span>👉</span> {{ $rep->replacementOfficer->name ?? '-' }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono">{{ $rep->replacementOfficer->nip ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if($rep->priority_level === 'prioritas_1_cadangan')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-100 text-blue-800">
                                                🥇 Prioritas 1: Cadangan
                                            </span>
                                        @elseif($rep->priority_level === 'prioritas_2_tersedia')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">
                                                🥈 Prioritas 2: Rotasi Adil
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-purple-100 text-purple-800">
                                                🥉 Prioritas 3: Penunjukan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <div class="text-slate-700 font-medium italic">"{{ $rep->reason }}"</div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800">{{ $rep->approver->name ?? 'Sistem' }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $rep->approved_at ? $rep->approved_at->translatedFormat('d M Y H:i') : '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            ✓ Resmi Bertugas
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-1">📋</div>
                                        <div class="font-bold text-slate-600 text-sm">Belum ada riwayat penggantian petugas</div>
                                        <p class="text-xs text-slate-400 mt-1">Seluruh jadwal piket masih berjalan sesuai penugasan awal.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($replacements->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $replacements->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
