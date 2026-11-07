<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight tracking-tight flex items-center gap-2">
                    <span class="text-rose-600">⚠️</span> Deteksi Konflik Jadwal Piket
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Sinkronisasi otomatis dengan Surat Tugas, Perjalanan Dinas, Cuti Pegawai, dan Izin Keluar Kantor
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('piket.conflicts.dinas') }}" class="px-3.5 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg text-xs font-bold transition flex items-center gap-1.5 border border-indigo-200">
                    <span>📋</span> Pantau Agenda Kedinasan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @include('piket.partials.nav')

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg shadow-sm text-sm font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg shadow-sm text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- STATISTIK KONFLIK -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Konflik Terdeteksi</div>
                        <div class="text-3xl font-black text-slate-800 mt-1">{{ $conflicts->total() }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Seluruh unit pelayanan</div>
                    </div>
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-xl">
                        🔍
                    </div>
                </div>

                <div class="bg-gradient-to-br from-rose-500 to-rose-600 text-white p-5 rounded-2xl shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-rose-100">Perlu Tindak Lanjut</div>
                        <div class="text-3xl font-black mt-1">{{ $pendingCount }}</div>
                        <div class="text-xs text-rose-100 mt-0.5">Belum diganti / kosong</div>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-2xl">
                        🚨
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Telah Diselesaikan</div>
                        <div class="text-3xl font-black text-emerald-600 mt-1">{{ $resolvedCount }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">Sudah dialihkan ke pengganti</div>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                        ✅
                    </div>
                </div>
            </div>

            <!-- FILTER & PENCARIAN -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('piket.conflicts.index') }}" class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Status:</label>
                        <select name="status" onchange="this.form.submit()" class="text-xs rounded-lg border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium">
                            <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>🚨 Perlu Pengganti (Pending)</option>
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="resolved_cadangan" {{ $status == 'resolved_cadangan' ? 'selected' : '' }}>Selesai - Cadangan</option>
                            <option value="resolved_tersedia" {{ $status == 'resolved_tersedia' ? 'selected' : '' }}>Selesai - Tersedia</option>
                            <option value="resolved_manual" {{ $status == 'resolved_manual' ? 'selected' : '' }}>Selesai - Penunjukan</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Unit Pelayanan:</label>
                        <select name="unit_id" onchange="this.form.submit()" class="text-xs rounded-lg border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium">
                            <option value="">Semua Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <a href="{{ route('piket.conflicts.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700 ml-auto">
                        Reset Filter
                    </a>
                </form>
            </div>

            <!-- DAFTAR KONFLIK -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-800">Daftar Benturan Jadwal Layanan</h3>
                        <p class="text-xs text-slate-500">Sistem membandingkan jadwal piket dengan data penugasan luar, surat tugas, dan cuti pegawai secara realtime.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-black tracking-wider text-[10px]">
                            <tr>
                                <th class="py-3 px-4">Tanggal Piket</th>
                                <th class="py-3 px-4">Unit Pelayanan</th>
                                <th class="py-3 px-4">Petugas Piket</th>
                                <th class="py-3 px-4">Penyebab Benturan (Kegiatan Dinas / Cuti)</th>
                                <th class="py-3 px-4">Cadangan Terdaftar</th>
                                <th class="py-3 px-4">Status & Pengganti</th>
                                <th class="py-3 px-4 text-center">Aksi Penggantian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($conflicts as $c)
                                <tr class="hover:bg-slate-50/80 transition {{ $c->status === 'pending' ? 'bg-rose-50/20' : '' }}">
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800">{{ $c->conflict_date->translatedFormat('l, d M Y') }}</div>
                                        <div class="text-[10px] text-slate-400">Jadwal: {{ $c->assignment->schedule->title ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $c->assignment->unit->code === 'ULT' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $c->assignment->unit->code }}
                                        </span>
                                        <div class="text-[10px] text-slate-500 mt-0.5">{{ $c->assignment->role_label }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800">{{ $c->user->name }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono">{{ $c->user->nip ?? 'Non-NIP' }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $c->user->unit_kerja ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <div class="font-bold text-rose-700 flex items-center gap-1">
                                            <span>⚡</span> {{ strtoupper(str_replace('_', ' ', $c->conflict_source)) }}
                                        </div>
                                        <div class="text-[11px] text-slate-700 mt-0.5 font-semibold">{{ $c->description }}</div>
                                        @if($c->conflict_ref_number)
                                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">No: {{ $c->conflict_ref_number }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($c->assignment->backupOfficer)
                                            <div class="font-bold text-slate-700">{{ $c->assignment->backupOfficer->name }}</div>
                                            <div class="text-[10px] text-emerald-600 font-semibold">Prioritas 1 (Cadangan)</div>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">Tidak ada cadangan</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($c->status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-700 animate-pulse">
                                                🚨 Butuh Pengganti
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ Selesai
                                            </span>
                                            @if($c->assignment->is_replaced && $c->assignment->activeOfficer)
                                                <div class="text-[10px] text-slate-600 mt-1">
                                                    Digantikan: <strong class="text-sipega-navy">{{ $c->assignment->activeOfficer->name }}</strong>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        @if($c->status === 'pending')
                                            <a href="{{ route('piket.replacements.create', $c->piket_schedule_assignment_id) }}" class="inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-sm transition gap-1">
                                                <span>🔄</span> Ganti Petugas
                                            </a>
                                        @else
                                            <a href="{{ route('piket.schedules.show', $c->assignment->piket_schedule_id) }}" class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold transition">
                                                Lihat Jadwal
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <div class="text-4xl mb-2">🎉</div>
                                        <div class="font-bold text-slate-600 text-sm">Tidak ada benturan jadwal piket!</div>
                                        <p class="text-xs text-slate-400 mt-1">Seluruh petugas piket yang terjadwal dalam kondisi siap melayani (bebas dari penugasan luar & cuti).</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($conflicts->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $conflicts->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
