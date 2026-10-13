<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Raport Capaian Pegawai 📊
                </h2>
                <p class="text-[10px] font-black text-sipega-orange uppercase tracking-[0.3em] mt-1">
                    CONSOLE MANAJEMEN &bull; REKAPITULASI SKOR & DISTRIBUSI WARNA KINERJA
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <!-- KALKULASI ULANG SKOR -->
                <form action="{{ route('performance.recalculate') }}" method="POST" onsubmit="return confirm('Kalkulasi ulang skor seluruh pegawai hari ini berdasarkan data absensi, agenda harian, dan surat tugas/laporan?')">
                    @csrf
                    <button type="submit" class="bg-sipega-navy hover:bg-black text-white font-black py-3 px-5 rounded-2xl shadow-xl transition-all hover:-translate-y-0.5 flex items-center gap-2 uppercase text-[10px] tracking-widest">
                        <span>🔄</span> Hitung Ulang Skor
                    </button>
                </form>

                <!-- UNDUH PDF -->
                <a href="{{ route('performance.pdf', request()->query()) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white font-black py-3 px-5 rounded-2xl shadow-xl transition-all hover:-translate-y-0.5 flex items-center gap-2 uppercase text-[10px] tracking-widest">
                    <span>🖨️</span> Cetak Rekap PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Success Message -->
            @if(session('success'))
            <div class="p-4 bg-emerald-500 text-white rounded-2xl shadow-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-xl">✅</span>
                    <span class="text-xs font-bold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold text-sm">&times;</button>
            </div>
            @endif

            <!-- 4 KARTU DISTRIBUSI WARNA CAPAIAN -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- BIRU -->
                <div class="bg-gradient-to-br from-blue-600 to-indigo-800 p-6 rounded-3xl text-white shadow-xl relative overflow-hidden group hover:scale-[1.02] transition-transform">
                    <div class="absolute -right-4 -bottom-4 text-white/10 text-7xl font-black">🔵</div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-blue-200">Kategori Biru</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-300 animate-pulse"></span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-white">{{ $stats['biru']['count'] }}</span>
                        <span class="text-xs font-bold text-blue-200">({{ $stats['biru']['pct'] }}%)</span>
                    </div>
                    <p class="text-[10px] font-bold text-blue-100 mt-2 uppercase tracking-wider">Sangat Baik (Skor > 90)</p>
                </div>

                <!-- HIJAU -->
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 p-6 rounded-3xl text-white shadow-xl relative overflow-hidden group hover:scale-[1.02] transition-transform">
                    <div class="absolute -right-4 -bottom-4 text-white/10 text-7xl font-black">🟢</div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-100">Kategori Hijau</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-200 animate-pulse"></span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-white">{{ $stats['hijau']['count'] }}</span>
                        <span class="text-xs font-bold text-emerald-100">({{ $stats['hijau']['pct'] }}%)</span>
                    </div>
                    <p class="text-[10px] font-bold text-emerald-50 mt-2 uppercase tracking-wider">Baik (Skor 76 - 90)</p>
                </div>

                <!-- KUNING -->
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 p-6 rounded-3xl text-white shadow-xl relative overflow-hidden group hover:scale-[1.02] transition-transform">
                    <div class="absolute -right-4 -bottom-4 text-white/10 text-7xl font-black">🟡</div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-amber-100">Kategori Kuning</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-200"></span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-white">{{ $stats['kuning']['count'] }}</span>
                        <span class="text-xs font-bold text-amber-100">({{ $stats['kuning']['pct'] }}%)</span>
                    </div>
                    <p class="text-[10px] font-bold text-amber-50 mt-2 uppercase tracking-wider">Cukup (Skor 61 - 75)</p>
                </div>

                <!-- MERAH -->
                <div class="bg-gradient-to-br from-rose-600 to-red-800 p-6 rounded-3xl text-white shadow-xl relative overflow-hidden group hover:scale-[1.02] transition-transform">
                    <div class="absolute -right-4 -bottom-4 text-white/10 text-7xl font-black">🔴</div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-rose-200">Kategori Merah</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-300 animate-ping"></span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-white">{{ $stats['merah']['count'] }}</span>
                        <span class="text-xs font-bold text-rose-200">({{ $stats['merah']['pct'] }}%)</span>
                    </div>
                    <p class="text-[10px] font-bold text-rose-100 mt-2 uppercase tracking-wider">Perlu Pembinaan (≤ 60)</p>
                </div>

                <!-- RATA-RATA & TOTAL -->
                <div class="bg-gradient-to-br from-slate-900 to-sipega-navy p-6 rounded-3xl text-white shadow-xl relative overflow-hidden sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-sipega-orange">Rata-Rata Lembaga</span>
                        <span class="text-xs">⭐</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-white">{{ $stats['avg_score'] }}</span>
                        <span class="text-xs font-bold text-gray-400">/ 100</span>
                    </div>
                    <p class="text-[10px] font-bold text-gray-400 mt-2 uppercase tracking-wider">
                        Total {{ $stats['total'] }} Pegawai Terdata
                    </p>
                </div>
            </div>

            <!-- FILTER & PENCARIAN -->
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <form action="{{ route('performance.recap') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <!-- Filter Warna -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Warna Capaian</label>
                        <select name="color" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange">
                            <option value="">Semua Warna</option>
                            <option value="Biru" {{ request('color') == 'Biru' ? 'selected' : '' }}>🔵 Biru (Sangat Baik)</option>
                            <option value="Hijau" {{ request('color') == 'Hijau' ? 'selected' : '' }}>🟢 Hijau (Baik)</option>
                            <option value="Kuning" {{ request('color') == 'Kuning' ? 'selected' : '' }}>🟡 Kuning (Cukup)</option>
                            <option value="Merah" {{ request('color') == 'Merah' ? 'selected' : '' }}>🔴 Merah (Perlu Pembinaan)</option>
                        </select>
                    </div>

                    <!-- Filter Gugus Mutu -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Gugus Mutu</label>
                        <select name="gugus_mutu" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange">
                            <option value="">Semua Gugus Mutu</option>
                            @foreach($gugusMutuList as $gm)
                                <option value="{{ $gm }}" {{ request('gugus_mutu') == $gm ? 'selected' : '' }}>{{ $gm }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Urutan</label>
                        <select name="sort" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange">
                            <option value="highest" {{ request('sort', 'highest') == 'highest' ? 'selected' : '' }}>🏆 Skor Tertinggi (Rank #1)</option>
                            <option value="lowest" {{ request('sort') == 'lowest' ? 'selected' : '' }}>📉 Skor Terendah</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>🔤 Nama Pegawai (A-Z)</option>
                        </select>
                    </div>

                    <!-- Pencarian -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Cari Pegawai</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIP / Jabatan..." class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange">
                    </div>

                    <!-- Tombol -->
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-sipega-navy hover:bg-black text-white text-xs font-bold rounded-2xl transition shadow">
                            Filter
                        </button>
                        <a href="{{ route('performance.recap') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-2xl transition text-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- TABEL RAPORT PEGAWAI -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-extrabold text-base text-sipega-navy uppercase tracking-tight">
                            📋 Raport Kinerja Seluruh Pegawai
                        </h3>
                        <p class="text-xs text-gray-400 font-medium">
                            Skor dihitung otomatis berdasarkan kombinasi Kehadiran (40%), Agenda Harian (30%), dan Penugasan/Laporan (30%).
                        </p>
                    </div>
                    <span class="text-xs font-bold text-gray-500 bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-sm">
                        Menampilkan {{ $employees->total() }} Pegawai
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50/80 border-b border-gray-100 text-[10px] font-black uppercase text-gray-400 tracking-wider">
                            <tr>
                                <th class="py-4 px-6 text-center w-16">Rank</th>
                                <th class="py-4 px-6">Identitas Pegawai</th>
                                <th class="py-4 px-6">Jabatan & Gugus</th>
                                <th class="py-4 px-6 text-center">Warna Capaian</th>
                                <th class="py-4 px-6 text-center">Skor Capaian</th>
                                <th class="py-4 px-6 text-center">Predikat</th>
                                <th class="py-4 px-6 text-center">Rincian Bobot</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($employees as $index => $emp)
                            @php
                                $rank = $employees->firstItem() + $index;
                                $score = (float)($emp->performance_score ?? 0);
                                $color = $emp->performance_color ?? 'Merah';

                                // Predikat label
                                $predicate = match($color) {
                                    'Biru' => 'SANGAT BAIK',
                                    'Hijau' => 'BAIK',
                                    'Kuning' => 'CUKUP',
                                    default => 'PERLU PEMBINAAN'
                                };

                                // Styling badge warna
                                $colorBadgeClass = match($color) {
                                    'Biru' => 'bg-blue-100 text-blue-800 border-blue-300 ring-4 ring-blue-50',
                                    'Hijau' => 'bg-emerald-100 text-emerald-800 border-emerald-300 ring-4 ring-emerald-50',
                                    'Kuning' => 'bg-amber-100 text-amber-800 border-amber-300 ring-4 ring-amber-50',
                                    default => 'bg-rose-100 text-rose-800 border-rose-300 ring-4 ring-rose-50'
                                };

                                $barColor = match($color) {
                                    'Biru' => 'from-blue-500 to-indigo-600',
                                    'Hijau' => 'from-emerald-400 to-teal-600',
                                    'Kuning' => 'from-amber-400 to-orange-500',
                                    default => 'from-red-500 to-rose-600'
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition group">
                                <!-- RANK -->
                                <td class="py-4 px-6 text-center">
                                    @if($rank === 1)
                                        <div class="w-9 h-9 rounded-2xl bg-amber-400 text-white flex items-center justify-center font-black text-xs shadow-md mx-auto" title="Peringkat 1">
                                            🥇 1
                                        </div>
                                    @elseif($rank === 2)
                                        <div class="w-9 h-9 rounded-2xl bg-slate-400 text-white flex items-center justify-center font-black text-xs shadow-md mx-auto" title="Peringkat 2">
                                            🥈 2
                                        </div>
                                    @elseif($rank === 3)
                                        <div class="w-9 h-9 rounded-2xl bg-amber-700 text-white flex items-center justify-center font-black text-xs shadow-md mx-auto" title="Peringkat 3">
                                            🥉 3
                                        </div>
                                    @else
                                        <span class="font-extrabold text-sm text-gray-400">#{{ $rank }}</span>
                                    @endif
                                </td>

                                <!-- NAMA & IDENTITAS -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3.5">
                                        <div class="relative shrink-0">
                                            @if($emp->profile_photo_path)
                                                <img src="{{ asset('storage/' . $emp->profile_photo_path) }}" class="w-11 h-11 rounded-2xl object-cover shadow border border-gray-200">
                                            @else
                                                <div class="w-11 h-11 rounded-2xl bg-sipega-navy text-white flex items-center justify-center font-black text-sm shadow">
                                                    {{ substr($emp->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <!-- Dot Warna Capaian -->
                                            <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white 
                                                {{ $color === 'Biru' ? 'bg-blue-600' : ($color === 'Hijau' ? 'bg-emerald-500' : ($color === 'Kuning' ? 'bg-amber-400' : 'bg-red-500')) }}">
                                            </span>
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-sm text-sipega-navy group-hover:text-sipega-orange transition">
                                                {{ $emp->name }}
                                            </div>
                                            <div class="text-[11px] text-gray-500 font-medium mt-0.5">
                                                NIP: {{ $emp->nip ?: '-' }}
                                            </div>
                                            @if($emp->golongan)
                                                <span class="inline-block mt-1 px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[9px] font-bold">
                                                    {{ \App\Models\User::formatPangkatGolongan($emp->golongan) ?: $emp->golongan }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- JABATAN & GUGUS MUTU -->
                                <td class="py-4 px-6">
                                    <div class="font-bold text-gray-800">
                                        {{ $emp->position ?: 'Fungsional Umum' }}
                                    </div>
                                    <div class="mt-1">
                                        @if($emp->gugus_mutu)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-black rounded-full border border-blue-100">
                                                🏷️ {{ $emp->gugus_mutu }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-gray-400 font-semibold">-</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- WARNA CAPAIAN (VISUAL TEGAS) -->
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full font-black text-[11px] border shadow-sm {{ $colorBadgeClass }}">
                                        @if($color === 'Biru') 🔵
                                        @elseif($color === 'Hijau') 🟢
                                        @elseif($color === 'Kuning') 🟡
                                        @else 🔴
                                        @endif
                                        {{ strtoupper($color) }}
                                    </span>
                                </td>

                                <!-- SKOR CAPAIAN & PROGRESS BAR -->
                                <td class="py-4 px-6 text-center">
                                    <div class="font-black text-lg text-sipega-navy leading-none">
                                        {{ number_format($score, 1) }}
                                        <span class="text-[10px] text-gray-400 font-bold uppercase">pts</span>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="w-28 mx-auto bg-gray-100 rounded-full h-2 mt-2 overflow-hidden shadow-inner">
                                        <div class="bg-gradient-to-r {{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ min(100, max(5, $score)) }}%;"></div>
                                    </div>
                                </td>

                                <!-- PREDIKAT -->
                                <td class="py-4 px-6 text-center">
                                    <span class="font-black text-[10px] uppercase tracking-wider
                                        {{ $color === 'Biru' ? 'text-blue-700' : ($color === 'Hijau' ? 'text-emerald-700' : ($color === 'Kuning' ? 'text-amber-700' : 'text-red-700')) }}">
                                        {{ $predicate }}
                                    </span>
                                </td>

                                <!-- RINCIAN BOBOT (40% Hadir, 30% Agenda, 30% Tugas) -->
                                <td class="py-4 px-6 text-center">
                                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-gray-50 rounded-xl border border-gray-100 text-[10px] font-bold text-gray-600" title="Komposisi penilaian kinerja">
                                        <span title="Absensi: Bobot 40%">⏱️ 40%</span>
                                        <span class="text-gray-300">&bull;</span>
                                        <span title="Agenda Harian: Bobot 30%">📋 30%</span>
                                        <span class="text-gray-300">&bull;</span>
                                        <span title="Tugas / Laporan Perjadin: Bobot 30%">📜 30%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-16 text-center text-gray-400">
                                    <div class="text-5xl mb-3">🔍</div>
                                    <p class="font-extrabold text-base text-gray-600">Tidak Ada Pegawai Yang Sesuai Filter</p>
                                    <p class="text-xs text-gray-400 mt-1">Coba sesuaikan filter warna, gugus mutu, atau kata kunci pencarian Anda.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($employees->hasPages())
                <div class="p-6 border-t border-gray-100">
                    {{ $employees->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
