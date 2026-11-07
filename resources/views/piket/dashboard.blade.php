<x-app-layout>
    <div class="bg-slate-100 min-h-screen py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- SUBNAVIGASI PIKET -->
            @include('piket.partials.nav')

            <!-- FLASH MESSAGE -->
            @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-xl text-xs text-emerald-900 font-bold shadow-sm">
                ✓ {{ session('success') }}
            </div>
            @endif

            <!-- TOP BANNER DASHBOARD PIKET -->
            <div class="rounded-2xl shadow-lg overflow-hidden border border-blue-900/20" style="background: linear-gradient(135deg, #003366 0%, #005a9c 50%, #0070ba 100%); color: #ffffff;">
                <div class="px-6 py-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black text-2xl text-white shadow-inner" style="background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.3);">
                            🏛️
                        </div>
                        <div>
                            <div class="text-xs font-bold text-blue-200 tracking-wider uppercase">SIM SPP • Modul Manajemen Piket</div>
                            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white uppercase drop-shadow-sm">
                                Dashboard Piket Tahunan ULT & Perpustakaan
                            </h1>
                            <div class="text-xs text-blue-100/90 mt-1">Balai Penjaminan Mutu Pendidikan (BPMP) Provinsi Kalimantan Timur</div>
                        </div>
                    </div>

                    <!-- FILTER TAHUN & UNIT -->
                    <form method="GET" action="{{ route('piket.dashboard') }}" class="flex flex-wrap items-center gap-2 bg-white/10 backdrop-blur-md p-2 rounded-2xl border border-white/20">
                        <div>
                            <select name="year" class="text-xs rounded-xl border-0 bg-white text-slate-800 font-bold py-1.5 focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
                                @for($y = date('Y') - 1; $y <= date('Y') + 2; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <select name="unit_id" class="text-xs rounded-xl border-0 bg-white text-slate-800 font-bold py-1.5 focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
                                <option value="">Semua Unit Pelayanan</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <a href="{{ route('piket.schedules.create') }}" class="px-3.5 py-1.5 rounded-xl font-black text-xs uppercase tracking-wider text-slate-900 shadow-md transition hover:scale-105" style="background-color: #FF8C00;">
                            + Buat Jadwal
                        </a>
                    </form>
                </div>

                <!-- SUB-INFO: PRINSIP UTAMA KESINAMBUNGAN PELAYANAN -->
                <div class="px-6 py-2.5 text-xs text-blue-50 flex items-center justify-between border-t border-white/20" style="background: rgba(0, 30, 65, 0.45);">
                    <span class="flex items-center gap-2">
                        <span class="text-amber-300">💡</span>
                        <span><strong>Prinsip Pelayanan Prima:</strong> Penjadwalan menjamin tidak ada kekosongan petugas di Unit Layanan Terpadu & Perpustakaan. Benturan tugas kedinasan dideteksi secara otomatis.</span>
                    </span>
                    <a href="{{ route('piket.schedules.calendar') }}" class="text-white hover:text-amber-200 font-bold text-xs underline">Buka Kalender Interaktif →</a>
                </div>
            </div>

            <!-- KARTU STATISTIK UTAMA (8 KARTU SESUAI SPESIFIKASI 4.A) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- 1. Petugas ULT -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Petugas ULT</div>
                        <span class="text-base">🏢</span>
                    </div>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ $ultOfficersCount }} Pegawai</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Daftar eligibel petugas ULT</div>
                </div>

                <!-- 2. Petugas Perpustakaan -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Petugas Perpustakaan</div>
                        <span class="text-base">📚</span>
                    </div>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ $perpusOfficersCount }} Pegawai</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Daftar eligibel Perpustakaan</div>
                </div>

                <!-- 3. Total Jadwal Harian -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Total Shift Piket</div>
                        <span class="text-base">🗓️</span>
                    </div>
                    <div class="text-2xl font-black text-blue-900 mt-1">{{ $totalAssignments }} Shift</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Tahun {{ $year }}</div>
                </div>

                <!-- 4. Jadwal Diterbitkan -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] font-bold text-emerald-600 uppercase">Jadwal Diterbitkan</div>
                        <span class="text-base">📜</span>
                    </div>
                    <div class="text-2xl font-black text-emerald-600 mt-1">{{ $publishedCount }} Jadwal Resmi</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Memiliki SK / Dokumen Resmi</div>
                </div>

                <!-- 5. Konflik Jadwal Aktif -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] font-bold text-rose-600 uppercase">Konflik Tugas Kedinasan</div>
                        <span class="text-base">⚠️</span>
                    </div>
                    <div class="text-2xl font-black text-rose-600 mt-1">{{ $activeConflictsCount }} Kasus</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Benturan ST, Cuti, atau Perjadin</div>
                </div>

                <!-- 6. Petugas Digantikan -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] font-bold text-amber-600 uppercase">Petugas Digantikan</div>
                        <span class="text-base">🔄</span>
                    </div>
                    <div class="text-2xl font-black text-amber-600 mt-1">{{ $replacedCount }} Pergantian</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Mekanisme 3 Prioritas Pengganti</div>
                </div>

                <!-- 7. Pengajuan Tukar Jadwal -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] font-bold text-indigo-600 uppercase">Pengajuan Tukar Jadwal</div>
                        <span class="text-base">🔀</span>
                    </div>
                    <div class="text-2xl font-black text-indigo-600 mt-1">{{ $swapRequestsCount }} Pengajuan</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Pertukaran dinas antarpegawai</div>
                </div>

                <!-- 8. Status Hari Ini -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-[11px] font-bold text-slate-500 uppercase">Petugas Bertugas Hari Ini</div>
                        <span class="text-base">🟢</span>
                    </div>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ $todayAssignments->count() }} Petugas Aktif</div>
                    <div class="text-[10px] text-emerald-600 font-bold mt-0.5">Pelayanan Aktif Berjalan</div>
                </div>
            </div>

            <!-- STATUS PELAYANAN HARI INI (LIVE MONITORING WIDGET) -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-200 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h2 class="text-base font-black text-slate-900 uppercase">Status Pelayanan Hari Ini ({{ \Carbon\Carbon::today('Asia/Makassar')->translatedFormat('l, d F Y') }})</h2>
                    </div>
                    <a href="{{ route('piket.monitoring.index') }}" class="text-xs text-blue-700 hover:underline font-bold">Buka Presensi & Catat Pelayanan →</a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($units as $u)
                        @php
                            $unitAssignments = $todayAssignments->where('piket_unit_id', $u->id);
                        @endphp
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <div class="font-black text-sm text-[#003366] flex items-center gap-2">
                                    <span>{{ $u->code === 'ULT' ? '🏢' : '📚' }}</span>
                                    <span>{{ $u->name }}</span>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $unitAssignments->isNotEmpty() ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $unitAssignments->isNotEmpty() ? 'Petugas Terjadwal' : 'Tidak Ada Shift / Libur' }}
                                </span>
                            </div>

                            @if($unitAssignments->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($unitAssignments as $asgn)
                                        <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm flex items-center justify-between text-xs">
                                            <div>
                                                <div class="font-extrabold text-slate-900">{{ $asgn->activeOfficer->name }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $asgn->role_label }} • NIP: {{ $asgn->activeOfficer->nip }}</div>
                                                @if($asgn->is_replaced)
                                                    <span class="text-[9px] text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded font-bold">Menggantikan: {{ $asgn->primaryOfficer->name }}</span>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $asgn->status === 'conflict' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                                    {{ strtoupper($asgn->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-xs text-slate-400 italic py-3 text-center">
                                    Tidak ada jadwal piket operasional untuk unit ini pada hari ini.
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- AKTIVITAS TERBARU (KONFLIK KEDINASAN & PERGANTIAN PETUGAS) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Tabel Konflik Terbaru -->
                <div class="bg-white rounded-2xl p-5 shadow-md border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-black uppercase text-rose-800 flex items-center gap-1.5">
                            <span>⚠️</span>
                            <span>Deteksi Benturan Tugas Kedinasan</span>
                        </h3>
                        <a href="{{ route('piket.conflicts.index') }}" class="text-[11px] text-blue-700 hover:underline font-bold">Lihat Semua →</a>
                    </div>

                    <div class="divide-y divide-slate-100 text-xs">
                        @forelse($recentConflicts as $cnf)
                            <div class="py-2.5 flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $cnf->user->name }}</div>
                                    <div class="text-[10px] text-rose-600 font-semibold">
                                        Benturan: {{ $cnf->activity_title }} ({{ $cnf->conflict_date->translatedFormat('d M Y') }})
                                    </div>
                                    <div class="text-[10px] text-slate-400">Unit: {{ $cnf->assignment->unit->name ?? '-' }}</div>
                                </div>
                                <div>
                                    @if($cnf->status === 'pending')
                                        <a href="{{ route('piket.replacements.create', $cnf->piket_schedule_assignment_id) }}" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-[10px] transition shadow">
                                            Pilih Pengganti
                                        </a>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Tertangani</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-slate-400 italic text-xs">
                                Tidak ada konflik penugasan dinas saat ini. Semua jadwal aman.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Tabel Riwayat Pergantian Terbaru -->
                <div class="bg-white rounded-2xl p-5 shadow-md border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-xs font-black uppercase text-amber-800 flex items-center gap-1.5">
                            <span>🔄</span>
                            <span>Riwayat Pergantian Petugas Piket</span>
                        </h3>
                        <a href="{{ route('piket.replacements.index') }}" class="text-[11px] text-blue-700 hover:underline font-bold">Lihat Semua →</a>
                    </div>

                    <div class="divide-y divide-slate-100 text-xs">
                        @forelse($recentReplacements as $rep)
                            <div class="py-2.5 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900">{{ $rep->originalOfficer->name }}</span>
                                    <span class="text-slate-400 text-[10px]">→ digantikan oleh</span>
                                    <span class="font-bold text-emerald-700">{{ $rep->replacementOfficer->name }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 flex items-center justify-between">
                                    <span>Alasan: {{ $rep->reason }}</span>
                                    <span class="bg-blue-50 text-blue-800 px-1.5 py-0.2 rounded font-mono font-bold">{{ strtoupper(str_replace('_', ' ', $rep->priority_level)) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-slate-400 italic text-xs">
                                Belum ada riwayat pergantian petugas tercatat.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
