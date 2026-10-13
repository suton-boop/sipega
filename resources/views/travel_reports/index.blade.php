<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Laporan Perjalanan Dinas 📄
                </h2>
                <p class="text-[10px] font-black text-sipega-orange uppercase tracking-[0.3em] mt-1">
                    SIPEGA &bull; PERTANGGUNGJAWABAN & HASIL DINAS LUAR
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('travel-reports.create') }}" class="bg-sipega-orange hover:bg-orange-600 text-white font-black py-3 px-6 rounded-2xl shadow-xl transition-all hover:-translate-y-0.5 flex items-center gap-2 uppercase text-[10px] tracking-widest">
                    <span>➕</span> Buat Laporan Baru
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

            <!-- STATS ROW -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-gradient-to-br from-sipega-navy to-slate-900 p-6 rounded-3xl text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 text-white/5 text-7xl font-black">📑</div>
                    <span class="text-[10px] font-black text-white/60 uppercase tracking-widest block">Total Laporan Terbit</span>
                    <span class="text-3xl font-black text-white mt-2 block">{{ $stats['total'] }}</span>
                    <span class="text-[10px] font-bold text-white/50 mt-1 block">Telah tersimpan dalam sistem</span>
                </div>
                <div class="bg-gradient-to-br from-teal-600 to-teal-800 p-6 rounded-3xl text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 text-white/5 text-7xl font-black">📅</div>
                    <span class="text-[10px] font-black text-white/70 uppercase tracking-widest block">Laporan Bulan Ini</span>
                    <span class="text-3xl font-black text-white mt-2 block">{{ $stats['this_month'] }}</span>
                    <span class="text-[10px] font-bold text-white/60 mt-1 block">Periode {{ now()->translatedFormat('F Y') }}</span>
                </div>
                <div class="bg-gradient-to-br from-amber-500 to-amber-700 p-6 rounded-3xl text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 text-white/5 text-7xl font-black">🏆</div>
                    <span class="text-[10px] font-black text-white/70 uppercase tracking-widest block">Laporan Tahun {{ now()->year }}</span>
                    <span class="text-3xl font-black text-white mt-2 block">{{ $stats['this_year'] }}</span>
                    <span class="text-[10px] font-bold text-white/60 mt-1 block">Siap diexport PDF & DOC</span>
                </div>
            </div>

            <!-- FILTER & PENCARIAN -->
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <form action="{{ route('travel-reports.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @if(in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Pegawai Pelaksana</label>
                        <select name="user_id" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange">
                            <option value="">Semua Pegawai</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->nip ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Tahun</label>
                        <select name="year" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange">
                            <option value="">Semua Tahun</option>
                            @for($y = date('Y'); $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="{{ in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']) ? '' : 'md:col-span-2' }}">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Cari Laporan</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor ST / Nama Kegiatan / Lokasi / Pelaksana..." class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-sipega-navy hover:bg-black text-white text-xs font-bold rounded-2xl transition shadow">
                            Filter
                        </button>
                        <a href="{{ route('travel-reports.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-2xl transition text-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- TABEL DAFTAR LAPORAN -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50/80 border-b border-gray-100 text-[10px] font-black uppercase text-gray-400 tracking-wider">
                            <tr>
                                <th class="py-4 px-6 text-center w-12">No</th>
                                <th class="py-4 px-6">Pelaksana & Surat Tugas</th>
                                <th class="py-4 px-6">Nama Kegiatan & Lokasi</th>
                                <th class="py-4 px-6">Tanggal Pelaksanaan</th>
                                <th class="py-4 px-6 text-center">Dokumentasi</th>
                                <th class="py-4 px-6 text-center">Aksi & Unduh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($reports as $index => $rep)
                            <tr class="hover:bg-orange-50/20 transition group">
                                <td class="py-4 px-6 text-center font-bold text-gray-400">
                                    {{ $reports->firstItem() + $index }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-sm text-sipega-navy group-hover:text-sipega-orange transition">
                                        {{ $rep->employee_name }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 font-medium mt-0.5">
                                        NIP: {{ $rep->employee_nip ?: '-' }} &bull; {{ $rep->employee_position ?: '-' }}
                                    </div>
                                    <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-[10px] font-black border border-blue-100">
                                        <span>📜</span> ST: {{ $rep->st_number }}
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <a href="{{ route('travel-reports.show', $rep->id) }}" class="font-bold text-gray-900 hover:text-sipega-orange transition block leading-snug">
                                        {{ $rep->activity_name }}
                                    </a>
                                    <div class="text-[11px] text-gray-500 mt-1 flex items-start gap-1">
                                        <span class="text-xs">📍</span>
                                        <span>{{ Str::limit($rep->location, 60) }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span class="font-bold text-gray-700 block">
                                        {{ $rep->formatted_date_range }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-medium">
                                        Dibuat: {{ $rep->created_at->translatedFormat('d M Y') }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @php
                                        $photoCount = is_array($rep->photos) ? count($rep->photos) : 0;
                                    @endphp
                                    @if($photoCount > 0)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 text-emerald-700 font-black text-[10px] rounded-full border border-emerald-200">
                                            <span>📷</span> {{ $photoCount }} Foto
                                        </span>
                                    @else
                                        <span class="text-[10px] text-gray-400 font-bold">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- LIHAT -->
                                        <a href="{{ route('travel-reports.show', $rep->id) }}" class="p-2 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-xl transition border border-gray-200 shadow-sm" title="Lihat Detail Laporan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>

                                        <!-- UNDUH PDF -->
                                        <a href="{{ route('travel-reports.pdf', $rep->id) }}" target="_blank" class="p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl transition border border-red-200 shadow-sm" title="Unduh Versi PDF">
                                            <span class="text-xs font-black">PDF</span>
                                        </a>

                                        <!-- UNDUH DOC (WORD) -->
                                        <a href="{{ route('travel-reports.doc', $rep->id) }}" class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl transition border border-blue-200 shadow-sm" title="Unduh Versi DOC (Microsoft Word)">
                                            <span class="text-xs font-black">DOC</span>
                                        </a>

                                        <!-- EDIT -->
                                        @if(auth()->user()->role !== 'Pegawai' || $rep->user_id === auth()->id())
                                        <a href="{{ route('travel-reports.edit', $rep->id) }}" class="p-2 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl transition border border-amber-200 shadow-sm" title="Edit Laporan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>

                                        <!-- HAPUS -->
                                        <form action="{{ route('travel-reports.destroy', $rep->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Laporan Perjalanan Dinas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-600 rounded-xl transition border border-gray-200 shadow-sm" title="Hapus Laporan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center text-gray-400">
                                    <div class="text-5xl mb-3">📭</div>
                                    <p class="font-extrabold text-base text-gray-600">Belum Ada Laporan Perjalanan Dinas</p>
                                    <p class="text-xs text-gray-400 mt-1 mb-4">Mulai buat laporan perjalanan dinas Anda untuk pertanggungjawaban tugas luar kantor.</p>
                                    <a href="{{ route('travel-reports.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sipega-orange hover:bg-orange-600 text-white text-xs font-bold rounded-2xl shadow transition">
                                        <span>➕</span> Buat Laporan Sekarang
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($reports->hasPages())
                <div class="p-6 border-t border-gray-100">
                    {{ $reports->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
