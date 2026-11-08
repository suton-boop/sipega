@props([
    'allPegawai' => null,
    'currentUserId' => auth()->id(),
    'defaultMonth' => now('Asia/Makassar')->format('Y-m')
])

<div x-data="{
    tab: 'harian',
    period: '{{ $defaultMonth }}',
    userId: '{{ $currentUserId }}',
    setPeriod(val) {
        this.period = val;
    }
}" class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] p-8 md:p-10 border border-gray-100 relative group transition-all">
    
    <!-- Background Watermark -->
    <div class="absolute top-0 right-0 p-6 opacity-5 pointer-events-none text-8xl font-black rotate-12 select-none">
        REKAP
    </div>

    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 pb-6 border-b border-gray-100 relative z-10">
        <div>
            <div class="inline-flex items-center gap-2 bg-gradient-to-r from-sky-50 to-emerald-50 text-sipega-navy px-3.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest mb-2 border border-sky-100">
                <span>📑 Pusat Rekapitulasi & Dokumen Resmi</span>
                <span class="w-1.5 h-1.5 bg-sipega-orange rounded-full"></span>
                <span class="text-sipega-orange">Filter Periode Bebas</span>
            </div>
            <h3 class="text-2xl md:text-3xl font-black text-sipega-navy tracking-tight">Pilih Dokumen & Unduh Laporan</h3>
            <p class="text-xs text-gray-500 font-medium mt-1">Gunakan tombol pilihan di bawah untuk berganti antara <strong>Laporan Harian Presensi</strong> dan <strong>Slip Tukin</strong> dengan filter bulan dan tahun.</p>
        </div>

        <!-- Tombol Pilihan (Switch Pills) -->
        <div class="flex flex-wrap items-center gap-2 p-1.5 bg-slate-100 rounded-2xl border border-slate-200/60 shadow-inner shrink-0">
            <button type="button" 
                    @click="tab = 'harian'" 
                    :class="tab === 'harian' ? 'bg-sipega-navy text-white shadow-lg' : 'text-slate-600 hover:text-sipega-navy hover:bg-white/80'" 
                    class="px-5 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition-all flex items-center gap-2">
                <span>📄 Laporan Harian</span>
                <span x-show="tab === 'harian'" class="text-[8px] bg-sipega-orange text-white px-2 py-0.5 rounded-full font-black animate-pulse">PILIHAN</span>
            </button>

            @if(\App\Models\Setting::get('show_slip_tukin', '1') == '1' || \App\Models\User::find(auth()->id())?->canManageTukin())
            <button type="button" 
                    @click="tab = 'slip'" 
                    :class="tab === 'slip' ? 'bg-emerald-600 text-white shadow-lg' : 'text-slate-600 hover:text-emerald-800 hover:bg-white/80'" 
                    class="px-5 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition-all flex items-center gap-2">
                <span>💰 Tukin</span>
                <span x-show="tab === 'slip'" class="text-[8px] bg-white text-emerald-800 px-2 py-0.5 rounded-full font-black animate-pulse">PILIHAN</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Filter Bar (Bulan, Tahun & Pegawai) -->
    <div class="py-6 border-b border-gray-100 grid grid-cols-1 md:grid-cols-12 gap-4 items-end relative z-10">
        
        @if(isset($allPegawai) && count($allPegawai) > 1 && in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator']))
        <!-- Pilihan Pegawai (Khusus Admin / Kasubag / Pimpinan) -->
        <div class="md:col-span-5">
            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">Pilih Pegawai Satker</label>
            <select x-model="userId" class="w-full text-xs font-bold rounded-2xl border-gray-200 shadow-sm focus:border-sipega-orange focus:ring-sipega-orange bg-slate-50 py-3">
                <option value="{{ auth()->id() }}">-- Saya Sendiri ({{ auth()->user()->name }}) --</option>
                @foreach($allPegawai as $p)
                    @if($p->id !== auth()->id())
                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->nip ?? 'No NIP' }})</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="md:col-span-4">
        @else
        <div class="md:col-span-6">
        @endif
            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1.5">Filter Bulan & Tahun</label>
            <div class="flex items-center gap-2">
                <input type="month" 
                       x-model="period" 
                       class="w-full text-xs font-black rounded-2xl border-gray-200 shadow-sm focus:border-sipega-orange focus:ring-sipega-orange bg-slate-50 py-3">
            </div>
        </div>

        <!-- Preset Cepat Bulan -->
        <div class="md:col-span-3 flex items-center gap-2 pb-1">
            <button type="button" @click="setPeriod('{{ now('Asia/Makassar')->format('Y-m') }}')" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[10px] font-bold uppercase tracking-wider transition">
                Bulan Ini
            </button>
            <button type="button" @click="setPeriod('{{ now('Asia/Makassar')->subMonth()->format('Y-m') }}')" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[10px] font-bold uppercase tracking-wider transition">
                Bulan Lalu
            </button>
        </div>
    </div>

    <!-- Active Mode Details & Action Buttons -->
    <div class="pt-6 relative z-10">
        <!-- MODE 1: LAPORAN HARIAN PRESENSI -->
        <div x-show="tab === 'harian'" x-transition class="space-y-4">
            <div class="p-5 rounded-2xl bg-sky-50/70 border border-sky-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-sky-600 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                        📄
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-sipega-navy">Laporan Harian Presensi Pegawai</h4>
                        <p class="text-xs text-sky-800 font-medium">Rekapitulasi absensi harian, jam masuk (check-in), pulang (check-out), menit terlambat (TL), menit pulang cepat (PSW), dan status kedinasan/ST.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0 w-full md:w-auto">
                    <!-- Tombol Lihat Data Harian -->
                    <a :href="'{{ url('/attendance/recap') }}/' + userId + '?month=' + period" 
                       class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-5 py-3 bg-white hover:bg-slate-100 text-sipega-navy border border-gray-200 rounded-xl font-black text-xs uppercase tracking-wider shadow-sm transition">
                        <span>Lihat Data Harian</span>
                        <svg class="w-3.5 h-3.5 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>

                    <!-- Tombol Unduh PDF Laporan Harian -->
                    <a :href="'{{ url('/attendance/recap-pdf') }}/' + userId + '?month=' + period" 
                       class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-6 py-3 bg-sky-700 hover:bg-sky-800 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow-lg shadow-sky-700/20 transition transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Unduh Laporan Harian (PDF)</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- MODE 2: SLIP TUKIN PEGAWAI -->
        <div x-show="tab === 'slip'" x-transition class="space-y-4" style="display: none;">
            <div class="p-5 rounded-2xl bg-emerald-50/70 border border-emerald-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-md shrink-0">
                        💰
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-emerald-950">Slip Tunjangan Kinerja (Tukin)</h4>
                        <p class="text-xs text-emerald-800 font-medium">Kalkulasi take-home pay, komponen hak grade jabatan, potongan progresif presensi & capaian kinerja sesuai regulasi Permendikdasmen 14/2025.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0 w-full md:w-auto">
                    <!-- Tombol Buka Perhitungan & Slip Tukin -->
                    <a :href="'{{ route('tukin.pegawai') }}?month=' + period + '&period=' + period" 
                       class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow-lg shadow-emerald-600/20 transition transform hover:-translate-y-0.5">
                        <span>Buka Perhitungan & Slip Tukin</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
