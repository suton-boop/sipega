<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-extrabold text-2xl text-sipega-navy leading-none tracking-tighter uppercase">
                Agenda Individu
            </h2>
            <p class="text-[10px] font-bold text-sipega-orange uppercase tracking-[0.3em] mt-1 italic">Manajemen Jadwal & Rencana Kerja Mandiri</p>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if(session('success'))
                <div class="bg-green-50 text-green-700 p-6 rounded-3xl mb-8 font-bold border border-green-100 flex items-center gap-3">
                    <span class="bg-green-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]">✓</span> 
                    {{ session('success') }}
                </div>
            @endif

            <!-- Form Penjadwalan -->
            <div class="bg-white overflow-hidden shadow-2xl rounded-[40px] border border-gray-100 p-10 lg:p-12">
                <h3 class="text-2xl font-black text-sipega-navy uppercase mb-8">Tambah Jadwal Baru</h3>
                <form action="{{ route('schedules.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Nama Kegiatan</label>
                            <input type="text" name="title" required class="w-full rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:border-sipega-navy p-4 font-bold text-gray-700" placeholder="Misal: Monitoring Lapangan, Rapat Internal...">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Tanggal</label>
                            <input type="date" name="date" required class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 font-bold text-gray-700">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Jam (Opsional)</label>
                            <input type="time" name="start_time" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 font-bold text-gray-700">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Lokasi</label>
                            <input type="text" name="location" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 font-bold text-gray-700" placeholder="Lokasi kegiatan...">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Keterangan</label>
                            <input type="text" name="remark" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 font-bold text-gray-700" placeholder="Catatan singkat...">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-sipega-navy hover:bg-black text-white font-black py-4 rounded-2xl shadow-xl transition-all uppercase tracking-widest">
                        Simpan ke Kalender Saya
                    </button>
                </form>
            </div>

            <!-- Agenda Dinas Luar Resmi (Surat Tugas Approved) -->
            <div class="bg-white overflow-hidden shadow-2xl rounded-[40px] border border-orange-200 p-8 lg:p-10">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center font-black">
                            <span class="text-xl">✈️</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-sipega-navy uppercase">Agenda Dinas Luar Resmi (ST)</h3>
                            <p class="text-[10px] font-bold text-orange-600 uppercase tracking-widest">Surat Tugas Resmi yang Telah Disetujui (Approved)</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.calendar.index') }}" class="px-4 py-2 bg-orange-50 hover:bg-orange-100 text-orange-600 font-black text-[10px] uppercase tracking-wider rounded-xl border border-orange-200 transition">
                        Buka Kalender Kerja &rarr;
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($dutyLetters as $dl)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 bg-orange-50/50 rounded-2xl border border-orange-200/70 hover:border-orange-400 transition-all gap-4">
                            <div class="flex items-start sm:items-center gap-4">
                                <div class="bg-orange-500 text-white p-3 rounded-2xl text-center min-w-[70px] shrink-0 shadow-md shadow-orange-500/20">
                                    <span class="block text-[9px] font-black uppercase tracking-wider opacity-90">{{ \Carbon\Carbon::parse($dl->date_start)->format('M') }}</span>
                                    <span class="block text-xl font-black">{{ \Carbon\Carbon::parse($dl->date_start)->format('d') }}</span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[8px] font-extrabold bg-orange-200 text-orange-800 uppercase tracking-wider">{{ $dl->category ?? 'DL' }}</span>
                                        <span class="text-[10px] font-mono text-orange-700 font-bold">No: {{ $dl->number ?? '-' }}</span>
                                    </div>
                                    <h4 class="text-base font-black text-sipega-navy leading-tight mt-1">{{ $dl->title }}</h4>
                                    <div class="flex flex-wrap items-center gap-3 mt-1 text-[11px] text-gray-500 font-medium">
                                        <span>📍 {{ $dl->location ?? 'Lokasi Penugasan' }}</span>
                                        <span class="text-gray-300">•</span>
                                        <span>📅 {{ \Carbon\Carbon::parse($dl->date_start)->translatedFormat('d M Y') }}{{ $dl->date_end ? ' s.d ' . \Carbon\Carbon::parse($dl->date_end)->translatedFormat('d M Y') : '' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-end sm:self-center">
                                <a href="{{ route('letters.show', $dl->id) }}" class="px-3.5 py-1.5 bg-white text-orange-600 hover:bg-orange-600 hover:text-white rounded-xl border border-orange-300 text-xs font-bold transition shadow-sm">
                                    Detail Surat
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-400 text-xs font-bold uppercase tracking-wider bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            Tidak ada jadwal Dinas Luar aktif dalam periode berjalan
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Daftar Agenda -->
            <div class="bg-white overflow-hidden shadow-2xl rounded-[40px] border border-gray-100 p-10 lg:p-12">
                <h3 class="text-2xl font-black text-sipega-navy uppercase mb-8">Daftar Agenda Mandiri Saya</h3>
                <div class="space-y-4">
                    @forelse($schedules as $s)
                        <div class="flex items-center justify-between p-6 bg-gray-50 rounded-3xl border border-gray-100 hover:border-sipega-orange transition-all group">
                            <div class="flex items-center gap-6">
                                <div class="bg-sipega-navy text-white p-4 rounded-2xl text-center min-w-[80px]">
                                    <span class="block text-[10px] font-black uppercase opacity-60">{{ \Carbon\Carbon::parse($s->date)->format('M') }}</span>
                                    <span class="block text-2xl font-black">{{ \Carbon\Carbon::parse($s->date)->format('d') }}</span>
                                </div>
                                <div>
                                    <h4 class="text-lg font-black text-sipega-navy leading-tight">{{ $s->title }}</h4>
                                    <div class="flex gap-4 mt-1">
                                        @if($s->start_time) <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">⏱ {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}</span> @endif
                                        @if($s->location) <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-4 border-l border-gray-200">📍 {{ $s->location }}</span> @endif
                                    </div>
                                </div>
                            </div>
                            <form action="{{ route('schedules.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus agenda ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-300 font-bold uppercase tracking-widest">Belum ada agenda individu</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
