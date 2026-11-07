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

            <!-- HEADER -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🗓️</span>
                        <h1 class="text-xl font-black text-slate-900 uppercase">Pengaturan Jadwal Tahunan & Bulanan</h1>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Daftar paket jadwal operasional pelayanan piket di ULT dan Perpustakaan BPMP Kalimantan Timur.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('piket.schedules.calendar') }}" class="px-3.5 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 transition flex items-center gap-1.5">
                        <span>📅</span>
                        <span>Buka Kalender</span>
                    </a>
                    <a href="{{ route('piket.schedules.create') }}" class="px-4 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider text-white shadow-md transition flex items-center gap-2" style="background-color: #003366;">
                        <span>⚡</span>
                        <span>Generate Jadwal Otomatis</span>
                    </a>
                </div>
            </div>

            <!-- FILTER BAR -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs">
                <form method="GET" action="{{ route('piket.schedules.index') }}" class="flex flex-wrap items-center gap-3">
                    <select name="year" class="text-xs rounded-xl border-slate-300 font-bold py-1.5 focus:border-blue-600" onchange="this.form.submit()">
                        @for($y = date('Y') - 1; $y <= date('Y') + 2; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endfor
                    </select>

                    <select name="unit_id" class="text-xs rounded-xl border-slate-300 font-medium py-1.5 focus:border-blue-600" onchange="this.form.submit()">
                        <option value="">-- Semua Unit Pelayanan --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>

                    @if($unitId)
                        <a href="{{ route('piket.schedules.index', ['year' => $year]) }}" class="text-rose-600 hover:underline font-bold text-xs">Reset Unit</a>
                    @endif
                </form>
            </div>

            <!-- DAFTAR JADWAL -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="text-white text-xs font-bold" style="background-color: #003366;">
                            <tr>
                                <th class="px-4 py-3.5 border-b border-blue-900">Judul Paket Jadwal</th>
                                <th class="px-4 py-3.5 border-b border-blue-900">Unit Pelayanan</th>
                                <th class="px-4 py-3.5 border-b border-blue-900 text-center">Periode Waktu</th>
                                <th class="px-4 py-3.5 border-b border-blue-900 text-center">Status Penerbitan</th>
                                <th class="px-4 py-3.5 border-b border-blue-900">Disusun Oleh</th>
                                <th class="px-4 py-3.5 border-b border-blue-900 text-center">Aksi & Rincian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($schedules as $sch)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-4">
                                        <div class="font-extrabold text-sm text-[#003366]">{{ $sch->title }}</div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">
                                            Revisi: <span class="font-bold text-slate-700 font-mono">{{ $sch->revision_number }}</span> • {{ $sch->period_type === 'tahunan' ? 'Tahunan Penuh' : 'Bulanan' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-black {{ $sch->unit->code === 'ULT' ? 'bg-blue-100 text-blue-900' : 'bg-emerald-100 text-emerald-900' }}">
                                            {{ $sch->unit->name }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center whitespace-nowrap font-medium text-slate-700">
                                        {{ $sch->start_date->translatedFormat('d M Y') }} - {{ $sch->end_date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        @if($sch->status === 'published')
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                ✓ RESMI DITERBITKAN
                                            </span>
                                            @if($sch->published_at)
                                                <div class="text-[9px] text-slate-400 mt-0.5">{{ $sch->published_at->translatedFormat('d M Y H:i') }}</div>
                                            @endif
                                        @elseif($sch->status === 'revised')
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                                REVISI ({{ $sch->revision_number }})
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-slate-200 text-slate-700">
                                                DRAFT (Belum Diterbitkan)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-xs text-slate-800 font-semibold">{{ $sch->creator->name ?? 'Sistem' }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $sch->created_at->translatedFormat('d M Y') }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('piket.schedules.show', $sch->id) }}" class="px-3 py-1.5 bg-[#003366] hover:bg-blue-900 text-white font-bold rounded-lg text-xs shadow-sm transition">
                                                Buka Rincian →
                                            </a>
                                            @if($sch->status === 'published')
                                                <a href="{{ route('piket.schedules.pdf', $sch->id) }}" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs shadow-sm transition" title="Unduh PDF Resmi">
                                                    📄 PDF
                                                </a>
                                            @else
                                                <a href="{{ route('piket.schedules.publish_form', $sch->id) }}" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-lg text-xs shadow-sm transition">
                                                    Terbitkan SK
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-16 text-center text-slate-400 italic">
                                        Belum ada jadwal piket yang dibuat untuk tahun {{ $year }}. Klik tombol "+ Generate Jadwal Otomatis" di atas untuk menyusun jadwal.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($schedules->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $schedules->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
