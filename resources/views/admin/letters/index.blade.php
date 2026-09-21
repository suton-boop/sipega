<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight uppercase tracking-wider flex items-center gap-2">
                    <span class="p-2 bg-sipega-orange/10 text-sipega-orange rounded-xl">📋</span>
                    <span>Modul Surat Tugas (Kasubag)</span>
                </h2>
                <p class="text-xs text-gray-500 font-bold mt-1">Pembuatan, Persetujuan (Approval), dan Cetak PDF 5 Model Surat Tugas BPMP Kalimantan Timur</p>
            </div>
            @if(in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator']))
            <a href="{{ route('letters.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-sipega-orange to-amber-500 hover:from-amber-600 hover:to-orange-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-xl shadow-orange-500/20 transition-all transform hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Surat Tugas Baru
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 shadow-sm animate-fade-in">
                <svg class="w-6 h-6 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="font-bold text-sm">{{ session('success') }}</div>
            </div>
            @endif

            @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3 text-red-800 shadow-sm animate-fade-in">
                <svg class="w-6 h-6 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="font-bold text-sm">{{ session('error') }}</div>
            </div>
            @endif

            <!-- STATISTIK KATEGORI & REKAP PENUGASAN -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition">
                    <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Total Surat</span>
                    <span class="text-2xl font-black text-sipega-navy mt-1 block">{{ $stats['total'] }}</span>
                    <span class="text-[10px] font-bold text-gray-400 mt-1 block">Semua Dokumen</span>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-white p-5 rounded-3xl border border-blue-100 shadow-sm hover:shadow-md transition">
                    <span class="text-[10px] font-black uppercase tracking-wider text-blue-600 block">🏢 DLK (Kantor)</span>
                    <span class="text-2xl font-black text-blue-900 mt-1 block">{{ $stats['dlk'] }}</span>
                    <span class="text-[10px] font-bold text-blue-400 mt-1 block">Kegiatan Kantor</span>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-white p-5 rounded-3xl border border-purple-100 shadow-sm hover:shadow-md transition">
                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-600 block">🏛️ DLP (Pusat)</span>
                    <span class="text-2xl font-black text-purple-900 mt-1 block">{{ $stats['dlp'] }}</span>
                    <span class="text-[10px] font-bold text-purple-400 mt-1 block">Kegiatan Pusat</span>
                </div>
                <div class="bg-gradient-to-br from-emerald-50 to-white p-5 rounded-3xl border border-emerald-100 shadow-sm hover:shadow-md transition">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 block">🤝 DLN (Kemitraan)</span>
                    <span class="text-2xl font-black text-emerald-900 mt-1 block">{{ $stats['dln'] }}</span>
                    <span class="text-[10px] font-bold text-emerald-400 mt-1 block">Non-APBN / Mitra</span>
                </div>
                <div class="bg-gradient-to-br from-amber-50 to-white p-5 rounded-3xl border border-amber-100 shadow-sm hover:shadow-md transition">
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 block">⏳ Draft</span>
                    <span class="text-2xl font-black text-amber-900 mt-1 block">{{ $stats['draft'] }}</span>
                    <span class="text-[10px] font-bold text-amber-500 mt-1 block">Menunggu Approval</span>
                </div>
                <div class="bg-gradient-to-br from-teal-50 to-white p-5 rounded-3xl border border-teal-100 shadow-sm hover:shadow-md transition">
                    <span class="text-[10px] font-black uppercase tracking-wider text-teal-600 block">✅ Approved</span>
                    <span class="text-2xl font-black text-teal-900 mt-1 block">{{ $stats['approved'] }}</span>
                    <span class="text-[10px] font-bold text-teal-500 mt-1 block">Masuk Rekap Tugas</span>
                </div>
            </div>

            <!-- FILTER & PENCARIAN -->
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <form action="{{ route('letters.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Kategori Kegiatan</label>
                        <select name="category" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange">
                            <option value="">Semua Kategori</option>
                            <option value="DLK" {{ request('category') == 'DLK' ? 'selected' : '' }}>DLK (Kegiatan Kantor)</option>
                            <option value="DLP" {{ request('category') == 'DLP' ? 'selected' : '' }}>DLP (Kegiatan Pusat)</option>
                            <option value="DLN" {{ request('category') == 'DLN' ? 'selected' : '' }}>DLN (Kegiatan Kemitraan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Model Surat</label>
                        <select name="st_model" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange">
                            <option value="">Semua Model</option>
                            <option value="model_1" {{ request('st_model') == 'model_1' ? 'selected' : '' }}>Model 1 (1 Orang)</option>
                            <option value="model_2" {{ request('st_model') == 'model_2' ? 'selected' : '' }}>Model 2 (>1 Orang)</option>
                            <option value="model_3" {{ request('st_model') == 'model_3' ? 'selected' : '' }}>Model 3 (Daring)</option>
                            <option value="model_4" {{ request('st_model') == 'model_4' ? 'selected' : '' }}>Model 4 (Lampiran Matriks)</option>
                            <option value="model_5" {{ request('st_model') == 'model_5' ? 'selected' : '' }}>Model 5 (Kolom Keterangan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Cari Surat</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor / Perihal / Tempat..." class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-sipega-navy hover:bg-black text-white text-xs font-bold rounded-2xl transition shadow">
                            Filter
                        </button>
                        <a href="{{ route('letters.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-2xl transition text-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- TABEL DAFTAR SURAT TUGAS -->
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-100 text-[10px] font-black uppercase tracking-wider text-gray-400">
                                <th class="py-4 px-6">Kategori & Model</th>
                                <th class="py-4 px-6">Nomor & Tanggal</th>
                                <th class="py-4 px-6">Dasar Undangan & Perihal</th>
                                <th class="py-4 px-6 text-center">Personil Ditugaskan</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Aksi Dokumen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs font-medium">
                            @forelse ($letters as $letter)
                            <tr class="hover:bg-orange-50/20 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex flex-col gap-1.5">
                                        @php
                                            $catBadge = match($letter->category) {
                                                'DLK' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                'DLP' => 'bg-purple-100 text-purple-700 border-purple-200',
                                                'DLN' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                default => 'bg-gray-100 text-gray-700 border-gray-200'
                                            };
                                        @endphp
                                        <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border tracking-wider {{ $catBadge }}">
                                            {{ $letter->category_label }}
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-500 bg-gray-50 px-2 py-0.5 rounded border border-gray-100 w-fit">
                                            {{ $letter->model_label }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-black text-sipega-navy text-sm block">{{ $letter->number ?? 'DRAFT-XXXX' }}</span>
                                    <span class="text-[10px] font-bold text-gray-400 mt-1 block">
                                        📅 {{ \Carbon\Carbon::parse($letter->date_start)->translatedFormat('d M Y') }}
                                        @if($letter->date_end && $letter->date_end != $letter->date_start)
                                            s.d {{ \Carbon\Carbon::parse($letter->date_end)->translatedFormat('d M Y') }}
                                        @endif
                                    </span>
                                </td>
                                <td class="py-4 px-6 max-w-sm">
                                    <p class="font-black text-gray-800 line-clamp-2">{{ $letter->title }}</p>
                                    @if($letter->invitation_from)
                                    <p class="text-[10px] text-gray-400 font-bold mt-1 truncate">
                                        🏛️ {{ $letter->invitation_from }}
                                    </p>
                                    @endif
                                    <p class="text-[10px] text-gray-400 font-bold truncate">
                                        📍 {{ $letter->location }}
                                    </p>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center -space-x-2">
                                        @foreach($letter->users->take(4) as $u)
                                        <div class="w-8 h-8 rounded-full bg-sipega-navy text-white flex items-center justify-center font-black text-[10px] border-2 border-white shadow-sm" title="{{ $u->name }} ({{ $u->position }})">
                                            {{ substr($u->name, 0, 2) }}
                                        </div>
                                        @endforeach
                                        @if($letter->users->count() > 4)
                                        <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center font-black text-[10px] border-2 border-white shadow-sm">
                                            +{{ $letter->users->count() - 4 }}
                                        </div>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-bold text-gray-400 mt-1 block">{{ $letter->users->count() }} Pegawai</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($letter->status === 'Approved')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-800 font-black text-[10px] rounded-full border border-emerald-200">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> APPROVED
                                        </span>
                                    @elseif($letter->status === 'Pending')
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-100 text-amber-800 font-black text-[10px] rounded-full border border-amber-200">
                                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span> PENDING
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 font-black text-[10px] rounded-full border border-gray-200">
                                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> DRAFT
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- CETAK PDF -->
                                        <a href="{{ route('letters.pdf_st', $letter->id) }}" target="_blank" class="p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl transition border border-red-200 shadow-sm" title="Unduh / Cetak PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </a>

                                        @if(in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
                                            <!-- APPROVE / BATALKAN APPROVE BUTTON -->
                                            @if($letter->status === 'Approved')
                                                <form action="{{ route('letters.unapprove', $letter->id) }}" method="POST" class="inline" onsubmit="return confirm('⚠️ BATALKAN PERSETUJUAN:\n\nApakah Anda yakin ingin MEMBATALKAN persetujuan Surat Tugas ini?\n\n• Status akan kembali menjadi DRAFT.\n• Hitungan pada Rekap Dinas Luar otomatis dikurangi agar tetap sinkron.\n• Tanda Dinas Luar pada kalender pegawai akan disesuaikan.')">
                                                    @csrf
                                                    <button type="submit" class="p-2 bg-amber-50 hover:bg-amber-100 text-amber-600 rounded-xl transition border border-amber-200 shadow-sm" title="Batalkan Persetujuan (Kembalikan ke Draft & Sinkronkan Rekap)">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('letters.approve', $letter->id) }}" method="POST" class="inline" onsubmit="return confirm('Setujui surat tugas ini? Setelah disetujui, otomatis terhitung penugasan pada Rekap Dinas Luar dan ditandai pada Kalender!')">
                                                    @csrf
                                                    <button type="submit" class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-xl transition border border-emerald-200 shadow-sm" title="Setujui (Approve)">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- EDIT -->
                                            <a href="{{ route('letters.edit', $letter->id) }}" class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-xl transition border border-blue-200 shadow-sm" title="Edit Surat Tugas">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>

                                            <!-- HAPUS -->
                                            <form action="{{ route('letters.destroy', $letter->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat tugas ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 bg-gray-50 hover:bg-gray-100 text-gray-500 rounded-xl transition border border-gray-200 shadow-sm" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    <div class="text-4xl mb-2">📭</div>
                                    <p class="font-bold">Belum ada Surat Tugas yang diterbitkan.</p>
                                    <p class="text-xs mt-1">Klik tombol <b>Buat Surat Tugas Baru</b> di atas untuk mulai membuat.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($letters->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $letters->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
