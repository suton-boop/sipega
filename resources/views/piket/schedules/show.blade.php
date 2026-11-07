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

            <!-- TOP BANNER JADWAL -->
            <div class="rounded-2xl p-6 shadow-md border border-blue-900/20 text-white space-y-4" style="background: linear-gradient(135deg, #003366 0%, #005a9c 100%);">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $schedule->status === 'published' ? 'bg-emerald-400 text-slate-950' : 'bg-amber-400 text-slate-950' }}">
                                {{ $schedule->status === 'published' ? 'RESMI DITERBITKAN' : 'DRAFT JADWAL' }}
                            </span>
                            <span class="text-xs text-blue-200">Revisi: {{ $schedule->revision_number }}</span>
                        </div>
                        <h1 class="text-2xl font-black uppercase text-white mt-1">{{ $schedule->title }}</h1>
                        <div class="text-xs text-blue-100/90 mt-1 flex items-center gap-2">
                            <span>Unit: <strong>{{ $schedule->unit->name }}</strong></span>
                            <span>•</span>
                            <span>Periode: {{ $schedule->start_date->translatedFormat('d F Y') }} s.d {{ $schedule->end_date->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Tombol Audit Konflik -->
                        <form method="POST" action="{{ route('piket.schedules.audit', $schedule->id) }}">
                            @csrf
                            <button type="submit" class="px-3.5 py-2 rounded-xl font-bold text-xs bg-white/20 hover:bg-white/30 text-white transition flex items-center gap-1.5 shadow-sm">
                                <span>🔍</span>
                                <span>Periksa Konflik ST/Cuti</span>
                            </button>
                        </form>

                        @if($schedule->status === 'published')
                            <a href="{{ route('piket.schedules.pdf', $schedule->id) }}" class="px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider bg-rose-600 hover:bg-rose-700 text-white transition flex items-center gap-1.5 shadow-md">
                                <span>📄</span>
                                <span>Unduh PDF Resmi</span>
                            </a>
                        @else
                            <a href="{{ route('piket.schedules.publish_form', $schedule->id) }}" class="px-4 py-2 rounded-xl font-black text-xs uppercase tracking-wider bg-amber-400 hover:bg-amber-500 text-slate-950 transition flex items-center gap-1.5 shadow-md">
                                <span>📜</span>
                                <span>Terbitkan Jadwal SK</span>
                            </a>
                        @endif

                        <a href="{{ route('piket.schedules.calendar', ['unit_id' => $schedule->piket_unit_id, 'year' => $schedule->year]) }}" class="px-3.5 py-2 rounded-xl font-bold text-xs bg-white text-slate-800 hover:bg-slate-100 transition shadow-sm">
                            Kalender Piket
                        </a>
                    </div>
                </div>

                <!-- STATISTIK RINGKAS JADWAL INI -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-3 border-t border-white/20 text-xs">
                    <div>
                        <span class="text-blue-200">Hari Layanan:</span>
                        <strong class="text-white block text-sm font-mono">{{ $totalDays }} Hari Kerja</strong>
                    </div>
                    <div>
                        <span class="text-blue-200">Total Shift Penugasan:</span>
                        <strong class="text-white block text-sm font-mono">{{ $schedule->assignments()->count() }} Shift</strong>
                    </div>
                    <div>
                        <span class="text-blue-200">Terdeteksi Konflik ST/Cuti:</span>
                        <strong class="block text-sm font-mono {{ $conflictsCount > 0 ? 'text-rose-300 font-black' : 'text-emerald-300' }}">{{ $conflictsCount }} Penugasan</strong>
                    </div>
                    <div>
                        <span class="text-blue-200">Petugas Digantikan:</span>
                        <strong class="text-amber-300 block text-sm font-mono">{{ $replacedCount }} Pergantian</strong>
                    </div>
                </div>
            </div>

            <!-- FILTER BULAN & STATUS -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                <form method="GET" action="{{ route('piket.schedules.show', $schedule->id) }}" class="flex flex-wrap items-center gap-3 flex-1">
                    <select name="month" class="text-xs rounded-xl border-slate-300 font-bold py-1.5 focus:border-blue-600" onchange="this.form.submit()">
                        <option value="">-- Semua Bulan --</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $monthFilter == $m ? 'selected' : '' }}>
                                Bulan {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>

                    <select name="status" class="text-xs rounded-xl border-slate-300 font-medium py-1.5 focus:border-blue-600" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="normal" {{ $statusFilter === 'normal' ? 'selected' : '' }}>Normal (Sesuai Jadwal)</option>
                        <option value="conflict" {{ $statusFilter === 'conflict' ? 'selected' : '' }}>Benturan / Konflik ST</option>
                        <option value="replaced" {{ $statusFilter === 'replaced' ? 'selected' : '' }}>Sudah Digantikan</option>
                        <option value="swapped" {{ $statusFilter === 'swapped' ? 'selected' : '' }}>Tukar Jadwal</option>
                    </select>

                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama petugas..." class="text-xs rounded-xl border-slate-300 py-1.5 px-3 min-w-[200px] focus:border-blue-600">

                    <button type="submit" class="px-3.5 py-1.5 bg-slate-800 text-white rounded-xl font-bold">Cari</button>

                    @if($monthFilter || $statusFilter || $search)
                        <a href="{{ route('piket.schedules.show', $schedule->id) }}" class="text-rose-600 hover:underline font-bold text-xs">Reset</a>
                    @endif
                </form>
            </div>

            <!-- TABEL PENUGASAN HARIAN -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="text-white text-xs font-bold" style="background-color: #003366;">
                            <tr>
                                <th class="px-3 py-3.5 text-center w-10 border-b border-blue-900">No.</th>
                                <th class="px-4 py-3.5 border-b border-blue-900">Tanggal Pelayanan</th>
                                <th class="px-4 py-3.5 border-b border-blue-900">Slot Shift</th>
                                <th class="px-4 py-3.5 border-b border-blue-900">Petugas Aktif Bertugas</th>
                                <th class="px-4 py-3.5 border-b border-blue-900">Petugas Cadangan Terdaftar</th>
                                <th class="px-3 py-3.5 border-b border-blue-900 text-center">Status</th>
                                <th class="px-4 py-3.5 border-b border-blue-900 text-center">Aksi & Pergantian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($assignments as $index => $item)
                                <tr class="hover:bg-slate-50 transition {{ $item->has_conflict && !$item->is_replaced ? 'bg-rose-50/60' : '' }}">
                                    <td class="px-3 py-3.5 text-center font-bold text-slate-400">
                                        {{ $assignments->firstItem() + $index }}.
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div class="font-extrabold text-slate-900 text-xs">{{ $item->date->translatedFormat('l, d F Y') }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-600">
                                        {{ $item->role_label }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="font-extrabold text-slate-900">{{ $item->activeOfficer->name }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono">NIP: {{ $item->activeOfficer->nip }}</div>
                                        @if($item->is_replaced)
                                            <div class="text-[9px] text-amber-800 bg-amber-50 px-2 py-0.5 rounded mt-0.5 border border-amber-200">
                                                Menggantikan: <strong>{{ $item->primaryOfficer->name }}</strong>
                                            </div>
                                        @endif
                                        @if($item->has_conflict && !$item->is_replaced)
                                            <div class="text-[10px] text-rose-700 font-semibold mt-1 bg-rose-100 px-2 py-0.5 rounded border border-rose-200">
                                                ⚠️ Benturan: {{ $item->conflict_description }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if($item->backupOfficer)
                                            <div class="font-semibold text-slate-800">{{ $item->backupOfficer->name }}</div>
                                            <div class="text-[10px] text-slate-400">Cadangan Resmi</div>
                                        @else
                                            <span class="text-slate-400 text-xs italic">Belum terpasang</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase
                                            @if($item->status === 'normal' || $item->status === 'completed') bg-emerald-100 text-emerald-800 border border-emerald-300
                                            @elseif($item->status === 'conflict') bg-rose-100 text-rose-800 border border-rose-300
                                            @elseif($item->status === 'replaced') bg-blue-100 text-blue-800 border border-blue-300
                                            @elseif($item->status === 'swapped') bg-purple-100 text-purple-800 border border-purple-300
                                            @else bg-slate-100 text-slate-700 @endif">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            @if($item->has_conflict && !$item->is_replaced)
                                                <a href="{{ route('piket.replacements.create', $item->id) }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs shadow-md transition flex items-center gap-1">
                                                    <span>⚡</span>
                                                    <span>Pilih Pengganti (3 Prioritas)</span>
                                                </a>
                                            @else
                                                <a href="{{ route('piket.replacements.create', $item->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-blue-50 text-blue-800 font-bold rounded-lg text-[10px] border border-slate-300 transition">
                                                    Ganti Petugas
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-slate-400 italic">
                                        Tidak ada data shift penugasan yang sesuai filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($assignments->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $assignments->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
