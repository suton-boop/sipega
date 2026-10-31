<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-sipega-navy text-white">
                        Level Kepegawaian & Pimpinan
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                        Permendikdasmen No. 14 / 2025
                    </span>
                </div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight mt-1">
                    Penghitungan Tunjangan Kinerja Pegawai ⚖️
                </h2>
                <p class="text-xs text-gray-500 font-medium">
                    Kalkulator, Rekapitulasi & Pembayaran Tukin Berdasarkan Kelas Jabatan dan Rekam Presensi Resmi.
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <!-- Tombol Download Template Excel -->
                <a href="{{ route('tukin.template') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 hover:text-emerald-700 hover:border-emerald-300 hover:bg-emerald-50 text-xs font-black rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Download Template
                </a>

                <!-- Tombol Buka Modal Import Excel -->
                <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-md transition transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    Import Format Excel
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Notifikasi Flash -->
            @if(session('success'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-2xl shadow-sm flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="text-xs text-emerald-900 font-bold leading-relaxed">
                    {{ session('success') }}
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-2xl shadow-sm flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="text-xs text-red-900 font-bold leading-relaxed">
                    {{ session('error') }}
                </div>
            </div>
            @endif

            <!-- BAR FILTER PERIODE & AKSI -->
            <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <!-- Filter Periode -->
                <form action="{{ route('tukin.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <label class="text-xs font-black uppercase text-gray-500 tracking-wider">Periode Perhitungan:</label>
                    <div class="relative">
                        <input type="month" name="period" value="{{ $period }}" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 text-sipega-navy text-xs font-black rounded-xl px-3 py-2 focus:ring-sipega-navy focus:border-sipega-navy shadow-inner cursor-pointer">
                    </div>

                    @if(!empty($savedPeriods))
                    <div class="flex items-center gap-1.5 overflow-x-auto py-1">
                        <span class="text-[10px] text-gray-400 font-bold">Riwayat:</span>
                        @foreach(array_slice($savedPeriods, 0, 4) as $p)
                        <a href="{{ route('tukin.index', ['period' => $p]) }}" class="px-2.5 py-1 text-[10px] font-black rounded-lg transition {{ $p === $period ? 'bg-sipega-navy text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            {{ \Carbon\Carbon::parse($p . '-01')->translatedFormat('M Y') }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </form>

                <!-- Tombol Aksi Tambahan -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-end">
                    <!-- Tombol Hitung Ulang dari Presensi Sistem -->
                    <form action="{{ route('tukin.recalculate') }}" method="POST" onsubmit="return confirm('Kalkulasi ulang seluruh data Tukin pegawai bulan {{ \Carbon\Carbon::parse($period . '-01')->translatedFormat('F Y') }} berdasarkan log absensi SIPEGA?')">
                        @csrf
                        <input type="hidden" name="period" value="{{ $period }}">
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-50 text-blue-800 hover:bg-blue-100 text-xs font-black rounded-xl border border-blue-200 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Kalkulasi dari Absensi
                        </button>
                    </form>

                    <!-- Export Excel -->
                    <a href="{{ route('tukin.export', ['period' => $period]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 text-xs font-black rounded-xl border border-emerald-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Export Excel
                    </a>

                    <!-- Cetak PDF Pembayaran -->
                    <a href="{{ route('tukin.download_payment_list', ['period' => $period]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 text-rose-800 hover:bg-rose-100 text-xs font-black rounded-xl border border-rose-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        PDF Daftar Bayar
                    </a>

                    <!-- Cetak PDF Rekapitulasi Lengkap -->
                    <a href="{{ route('tukin.download_recap', ['period' => $period]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 bg-purple-50 text-purple-800 hover:bg-purple-100 text-xs font-black rounded-xl border border-purple-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        PDF Rekap (A3)
                    </a>

                    <!-- Panduan Regulasi -->
                    <button type="button" onclick="document.getElementById('regulasiModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-black rounded-xl transition">
                        📘 Pedoman 14/2025
                    </button>
                </div>
            </div>

            <!-- PANEL CEPAT: IMPORT FILE EXCEL & HITUNG TUKIN -->
            <div class="bg-gradient-to-r from-emerald-50 via-teal-50/50 to-white p-6 rounded-[2rem] shadow-sm border border-emerald-200/80">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-xl shadow-md">
                            📊
                        </div>
                        <div>
                            <h3 class="font-black text-sm text-sipega-navy uppercase tracking-wider">
                                Import Format Excel & Kalkulasi Tukin Pegawai
                            </h3>
                            <p class="text-xs text-gray-500 font-medium">
                                Unggah file Excel (.xlsx / .xls / .csv) daftar kehadiran atau daftar pembayaran tukin. Sistem akan menghitung potongan kehadiran dan netto otomatis.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('tukin.template') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white text-emerald-800 hover:bg-emerald-100 text-xs font-black rounded-xl border border-emerald-300 shadow-sm transition">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Unduh Template Excel
                        </a>
                    </div>
                </div>

                <form action="{{ route('tukin.import_excel') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    @csrf
                    <div class="md:col-span-3">
                        <label class="block text-[10px] font-black uppercase text-gray-500 tracking-wider mb-1">
                            Bulan & Tahun Perhitungan:
                        </label>
                        <input type="month" name="period" value="{{ $period }}" required class="w-full bg-white border border-gray-300 text-sipega-navy text-xs font-black rounded-xl px-3 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">
                    </div>

                    <div class="md:col-span-6">
                        <label class="block text-[10px] font-black uppercase text-gray-500 tracking-wider mb-1">
                            Pilih File Excel / CSV (.xlsx, .xls, .csv):
                        </label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv,.txt" required class="w-full bg-white border border-gray-300 text-xs text-gray-600 rounded-xl px-2 py-1.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-black file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer shadow-sm">
                    </div>

                    <div class="md:col-span-3">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-md transition transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Upload & Hitung Tukin
                        </button>
                    </div>
                </form>
            </div>

            <!-- KARTU STATISTIK EKSEKUTIF -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Total Pegawai -->
                <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Total Pegawai</p>
                    <p class="text-2xl font-black text-sipega-navy mt-1">
                        {{ $summary['total_pegawai'] }} <span class="text-xs font-bold text-gray-400">Pegawai</span>
                    </p>
                    <p class="text-[10px] text-gray-500 font-medium mt-1">
                        {{ $summary['pegawai_terpotong'] }} pegawai dikenai potongan
                    </p>
                </div>

                <!-- Total Plafon Bruto -->
                <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Plafon Tukin Bruto</p>
                    <p class="text-xl font-black text-sipega-navy mt-1">
                        Rp {{ number_format($summary['total_base'], 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] text-emerald-600 font-bold mt-1">Standar Perpres 18/2025</p>
                </div>

                <!-- Total Faktor Pengurang -->
                <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Total Faktor Pengurang</p>
                    <p class="text-xl font-black text-rose-600 mt-1">
                        - Rp {{ number_format($summary['total_deduction'], 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] text-rose-500 font-bold mt-1">
                        Presensi: Rp {{ number_format($summary['total_potongan_absen'], 0, ',', '.') }}
                    </p>
                </div>

                <!-- Estimasi PPh 21 -->
                <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[9px] font-black uppercase text-gray-400 tracking-widest">Estimasi PPh 21</p>
                    <p class="text-xl font-black text-amber-600 mt-1">
                        Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] text-gray-400 font-medium mt-1">Gol IV (15%), III (5%), II (0%)</p>
                </div>

                <!-- Total Netto Dibayarkan -->
                <div class="bg-gradient-to-br from-sipega-navy to-slate-900 text-white p-5 rounded-[2rem] shadow-md border border-slate-800">
                    <p class="text-[9px] font-black uppercase text-amber-400 tracking-widest">Total Netto Dibayarkan</p>
                    <p class="text-xl font-black text-white mt-1">
                        Rp {{ number_format($summary['total_netto'], 0, ',', '.') }}
                    </p>
                    <p class="text-[10px] text-slate-300 font-medium mt-1">Siap Pengajuan SPM / SP2D</p>
                </div>
            </div>

            <!-- TABEL DAFTAR PEMBAYARAN TUNJANGAN KINERJA -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-[2.5rem] border border-gray-100">
                <div class="p-6 md:p-8 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-50/40">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-black text-sipega-navy">
                                Rekapitulasi Daftar Pembayaran Tukin ({{ \Carbon\Carbon::parse($period . '-01')->translatedFormat('F Y') }})
                            </h3>
                            <span class="px-2 py-0.5 bg-gray-200 text-gray-700 text-[10px] font-black rounded-full">
                                {{ $calculations->count() }} Data
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">
                            Menampilkan rincian persentase pemotongan kehadiran, nilai faktor pengurang, dan jumlah netto sesuai format resmi instansi.
                        </p>
                    </div>

                    @if($hasData)
                    <form action="{{ route('tukin.delete_period', ['period' => $period]) }}" method="POST" onsubmit="return confirm('Hapus seluruh hasil kalkulasi periode {{ $period }}? Anda dapat menghitung ulang kapan saja.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700 hover:underline">
                            Hapus Data Periode Ini
                        </button>
                    </form>
                    @endif
                </div>

                @if($calculations->isEmpty())
                <!-- Tampilan Jika Belum Ada Data -->
                <div class="p-12 text-center space-y-4">
                    <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-3xl mx-auto flex items-center justify-center text-3xl shadow-inner">
                        📋
                    </div>
                    <div class="max-w-md mx-auto">
                        <h4 class="font-black text-sipega-navy text-base">Belum Ada Perhitungan untuk Periode Ini</h4>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Pilih salah satu metode di bawah ini untuk memulai penghitungan Tukin pegawai pada bulan <strong>{{ \Carbon\Carbon::parse($period . '-01')->translatedFormat('F Y') }}</strong>:
                        </p>
                    </div>
                    <div class="flex flex-wrap justify-center items-center gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-md transition">
                            📥 Upload File Excel (.xlsx / .csv)
                        </button>
                        <form action="{{ route('tukin.recalculate') }}" method="POST">
                            @csrf
                            <input type="hidden" name="period" value="{{ $period }}">
                            <button type="submit" class="px-5 py-2.5 bg-sipega-navy hover:bg-black text-white text-xs font-black rounded-xl shadow-md transition">
                                ⚡ Hitung Otomatis dari Log Absensi
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <!-- Tabel Hasil Perhitungan -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-sipega-navy text-white text-[9px] font-black uppercase tracking-wider">
                                <th class="px-4 py-3.5 text-center w-10">No</th>
                                <th class="px-3 py-3.5 text-center w-14">Grade</th>
                                <th class="px-4 py-3.5">Nama Pegawai & NIP</th>
                                <th class="px-4 py-3.5">Jabatan</th>
                                <th class="px-3 py-3.5 text-center w-16">Gol.</th>
                                <th class="px-4 py-3.5 text-right">Tukin Dasar</th>
                                <th class="px-4 py-3.5 text-center">Rincian Pengurang (TL / PSW / TK)</th>
                                <th class="px-3 py-3.5 text-center">% Pot.</th>
                                <th class="px-4 py-3.5 text-right">Faktor Pengurang</th>
                                <th class="px-4 py-3.5 text-right font-black bg-slate-800 text-amber-300">Netto Diterima</th>
                                <th class="px-3 py-3.5 text-center w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($calculations as $idx => $c)
                            <tr class="hover:bg-amber-50/40 transition-colors {{ $c->total_penalty_percent > 0 ? 'bg-rose-50/20' : '' }}">
                                <td class="px-4 py-3.5 text-center text-gray-500 font-bold">{{ $idx + 1 }}</td>
                                <td class="px-3 py-3.5 text-center">
                                    <span class="inline-block px-2.5 py-1 bg-gray-100 text-sipega-navy font-black text-xs rounded-lg">
                                        {{ $c->grade }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="font-black text-sipega-navy text-xs leading-tight">{{ $c->name }}</p>
                                    <p class="text-[10px] text-gray-400 font-mono tracking-tighter mt-0.5">{{ $c->nip }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-gray-600 font-medium">
                                    {{ $c->position ?? '-' }}
                                </td>
                                <td class="px-3 py-3.5 text-center font-bold text-gray-700">
                                    {{ $c->golongan ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-black text-gray-900">
                                    Rp {{ number_format($c->base_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <!-- Rincian Pelanggaran Singkat -->
                                    <div class="flex flex-wrap justify-center items-center gap-1 max-w-xs mx-auto">
                                        @if($c->tl1_count > 0)
                                            <span class="px-1.5 py-0.5 bg-amber-100 text-amber-800 rounded text-[9px] font-bold" title="Terlambat 1-30 menit: {{ $c->tl1_count }}x">TL1: {{ $c->tl1_count }}</span>
                                        @endif
                                        @if($c->tl2_count > 0)
                                            <span class="px-1.5 py-0.5 bg-amber-200 text-amber-900 rounded text-[9px] font-bold" title="Terlambat 31-60 menit: {{ $c->tl2_count }}x">TL2: {{ $c->tl2_count }}</span>
                                        @endif
                                        @if($c->tl3_count > 0)
                                            <span class="px-1.5 py-0.5 bg-orange-200 text-orange-900 rounded text-[9px] font-bold" title="Terlambat 61-90 menit: {{ $c->tl3_count }}x">TL3: {{ $c->tl3_count }}</span>
                                        @endif
                                        @if($c->tl4_count > 0 || $c->tl5_count > 0)
                                            <span class="px-1.5 py-0.5 bg-red-200 text-red-900 rounded text-[9px] font-bold" title="Terlambat Berat: {{ $c->tl4_count + $c->tl5_count }}x">TL4/5: {{ $c->tl4_count + $c->tl5_count }}</span>
                                        @endif
                                        @if($c->psw1_count > 0 || $c->psw2_count > 0 || $c->psw3_count > 0 || $c->psw4_count > 0 || $c->psw5_count > 0)
                                            <span class="px-1.5 py-0.5 bg-sky-100 text-sky-800 rounded text-[9px] font-bold" title="Pulang Sebelum Waktunya">PSW</span>
                                        @endif
                                        @if($c->tkd_count > 0)
                                            <span class="px-1.5 py-0.5 bg-rose-100 text-rose-800 rounded text-[9px] font-bold" title="Tidak Absen Datang (TKD): {{ $c->tkd_count }}x">TKD: {{ $c->tkd_count }}</span>
                                        @endif
                                        @if($c->tkp_count > 0)
                                            <span class="px-1.5 py-0.5 bg-rose-100 text-rose-800 rounded text-[9px] font-bold" title="Tidak Absen Pulang (TKP): {{ $c->tkp_count }}x">TKP: {{ $c->tkp_count }}</span>
                                        @endif
                                        @if($c->alpa_count > 0)
                                            <span class="px-1.5 py-0.5 bg-red-600 text-white rounded text-[9px] font-black" title="Tanpa Keterangan / Alpa: {{ $c->alpa_count }} hari">ALPA: {{ $c->alpa_count }}</span>
                                        @endif
                                        @if($c->total_penalty_percent == 0)
                                            <span class="text-[10px] text-emerald-600 font-bold">Nihil Pelanggaran ✨</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    @if($c->total_penalty_percent > 0)
                                        <span class="inline-block px-2 py-0.5 bg-rose-100 text-rose-800 font-black rounded-full text-[10px]">
                                            {{ number_format($c->total_penalty_percent, 2) }}%
                                        </span>
                                    @else
                                        <span class="text-gray-400 font-bold">0%</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold {{ $c->total_deduction > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                                    {{ $c->total_deduction > 0 ? 'Rp ' . number_format($c->total_deduction, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-black text-sipega-navy bg-slate-50">
                                    Rp {{ number_format($c->netto_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <!-- Tombol Edit Modal -->
                                        <button type="button" 
                                            onclick="openEditModal({{ json_encode($c) }})"
                                            class="p-1.5 text-gray-500 hover:text-sipega-navy hover:bg-gray-100 rounded-lg transition" 
                                            title="Edit Potongan / Catatan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>

                                        <!-- Download Slip PDF -->
                                        @if($c->user_id)
                                        <a href="{{ route('tukin.download_slip', ['id' => $c->user_id, 'period' => $period]) }}" 
                                            target="_blank" 
                                            class="p-1.5 text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 rounded-lg transition" 
                                            title="Cetak Slip Tukin Pegawai">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100/80 font-black text-sipega-navy border-t-2 border-gray-200">
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center uppercase tracking-widest text-xs">TOTAL KESELURUHAN</td>
                                <td class="px-4 py-4 text-right">Rp {{ number_format($summary['total_base'], 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-center text-[10px] text-gray-500">-</td>
                                <td class="px-3 py-4 text-center">-</td>
                                <td class="px-4 py-4 text-right text-rose-600">Rp {{ number_format($summary['total_deduction'], 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right text-emerald-800 bg-emerald-50/70 text-sm">
                                    Rp {{ number_format($summary['total_netto'], 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>

            <!-- TABEL TARIF RESMI KELAS JABATAN PERPRES 18/2025 -->
            <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h4 class="font-black text-sipega-navy text-sm uppercase tracking-wider">
                            Besaran Nominal Tukin Berdasarkan Kelas Jabatan (Perpres No. 18 / 2025)
                        </h4>
                        <p class="text-xs text-gray-400 font-medium">Acuan dasar perhitungan tunjangan kinerja pegawai di lingkungan Kementerian Pendidikan Dasar dan Menengah.</p>
                    </div>
                    <a href="{{ route('tukin.classes') }}" class="text-xs font-black text-sipega-navy hover:underline">
                        Kelola Kelas Jabatan &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-9 gap-2.5">
                    @foreach($officialRates as $gr => $rate)
                    <div class="p-3 bg-gray-50 hover:bg-amber-50/50 rounded-2xl border border-gray-100 text-center transition">
                        <span class="block text-[10px] font-black uppercase text-gray-400 tracking-wider">Grade {{ $gr }}</span>
                        <span class="block text-xs font-black text-sipega-navy mt-0.5">
                            Rp {{ number_format($rate / 1000, 0, ',', '.') }}k
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL IMPORT FORMAT EXCEL -->
    <div id="importModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-lg w-full p-8 border border-gray-100 space-y-6 relative animate-in fade-in zoom-in-95 duration-200">
            <div class="flex justify-between items-start">
                <div>
                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-black rounded-full uppercase tracking-wider">
                        Import Excel / CSV
                    </span>
                    <h3 class="font-black text-xl text-sipega-navy mt-1">
                        Import Data & Hitung Tukin
                    </h3>
                    <p class="text-xs text-gray-400 font-medium mt-0.5">
                        Mendukung format Rekapitulasi Hadir, Log Harian, atau Daftar Pembayaran Tukin.
                    </p>
                </div>
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('tukin.import_excel') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Periode Target -->
                <div>
                    <label class="block text-xs font-black uppercase text-gray-500 tracking-wider mb-1">
                        Pilih Periode Bulan & Tahun:
                    </label>
                    <input type="month" name="period" value="{{ $period }}" required class="w-full bg-gray-50 border border-gray-200 text-sipega-navy text-xs font-bold rounded-xl px-3 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                    <p class="text-[10px] text-gray-400 mt-1">Data hasil impor akan disimpan pada periode ini.</p>
                </div>

                <!-- Input File -->
                <div>
                    <label class="block text-xs font-black uppercase text-gray-500 tracking-wider mb-1">
                        Pilih File Excel / CSV:
                    </label>
                    <div class="border-2 border-dashed border-gray-200 hover:border-emerald-500 rounded-2xl p-6 text-center cursor-pointer transition bg-gray-50/50 hover:bg-emerald-50/30">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv,.txt" required class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                        <p class="text-[10px] text-gray-400 mt-2">Format: .xlsx, .xls, atau .csv (Maks. 20 MB)</p>
                    </div>
                </div>

                <!-- Info Panduan Format File -->
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-1.5 text-[11px] text-gray-600">
                    <p class="font-bold text-sipega-navy flex items-center gap-1">
                        <span>💡</span> Format yang Didukung Otomatis:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-gray-500">
                        <li><strong>Format Rekapitulasi Manual</strong>: Kolom NIP, Nama, Grade, TL1-TL5, PSW1-PSW5, TKD, TKP, Alpa, Cuti.</li>
                        <li><strong>Format Log Harian</strong>: Kolom NIP, Tanggal, Kedatangan, Kepulangan, Keterangan (DL/TL1/TKP/dll).</li>
                        <li><strong>Format Daftar Pembayaran</strong>: Kolom Grade, Nama, Plafon, % Pengurangan Kehadiran.</li>
                    </ul>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('tukin.template') }}" class="text-xs font-black text-emerald-700 hover:underline flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Template Standar
                    </a>

                    <div class="flex gap-2">
                        <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-black rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-md transition">
                            Mulai Import & Hitung
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT KOREKSI MANUAL PEGAWAI -->
    <div id="editModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-md w-full p-8 border border-gray-100 space-y-5 relative">
            <div class="flex justify-between items-start">
                <div>
                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-black rounded-full uppercase tracking-wider">
                        Koreksi / Dispensasi
                    </span>
                    <h3 class="font-black text-lg text-sipega-navy mt-1" id="editEmployeeName">
                        Edit Perhitungan Tukin
                    </h3>
                    <p class="text-xs text-gray-400 font-mono" id="editEmployeeNip"></p>
                </div>
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-black uppercase text-gray-500 tracking-wider mb-1">Grade / Kelas Jabatan:</label>
                    <select name="grade" id="editGrade" class="w-full bg-gray-50 border border-gray-200 text-sipega-navy text-xs font-bold rounded-xl px-3 py-2">
                        @for($g = 17; $g >= 1; $g--)
                            <option value="{{ $g }}">Grade {{ $g }} (Rp {{ number_format(App\Services\TukinCalculatorService::OFFICIAL_RATES[$g] ?? 0, 0, ',', '.') }})</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-gray-500 tracking-wider mb-1">Total % Pengurangan Kehadiran:</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" name="attendance_penalty_percent" id="editPenaltyPercent" required class="w-full bg-gray-50 border border-gray-200 text-sipega-navy text-xs font-black rounded-xl px-3 py-2 pr-8">
                        <span class="absolute right-3 top-2 text-xs font-black text-gray-400">%</span>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">Ubah persentase jika pegawai mendapat nota dinas atau dispensasi izin atasan.</p>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-gray-500 tracking-wider mb-1">Predikat Evaluasi Kinerja (Triwulan):</label>
                    <select name="performance_predicate" id="editPredicate" class="w-full bg-gray-50 border border-gray-200 text-sipega-navy text-xs font-bold rounded-xl px-3 py-2">
                        <option value="Sangat Baik">Sangat Baik (Potongan 0%)</option>
                        <option value="Baik">Baik (Potongan 0%)</option>
                        <option value="Butuh Perbaikan">Butuh Perbaikan (Potongan 20%)</option>
                        <option value="Kurang">Kurang (Potongan 40%)</option>
                        <option value="Sangat Kurang">Sangat Kurang (Potongan 60%)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-gray-500 tracking-wider mb-1">Catatan Dispensasi / Keterangan:</label>
                    <textarea name="notes" id="editNotes" rows="2" class="w-full bg-gray-50 border border-gray-200 text-sipega-navy text-xs font-medium rounded-xl p-3" placeholder="Alasan perubahan atau dispensasi..."></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-black rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-sipega-navy hover:bg-black text-white text-xs font-black rounded-xl shadow-md transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PEDOMAN PERMENDIKDASMEN NO. 14 TAHUN 2025 -->
    <div id="regulasiModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] shadow-2xl max-w-3xl w-full p-8 border border-gray-100 space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start">
                <div>
                    <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-black rounded-full uppercase tracking-wider">
                        Aturan & Pedoman Resmi
                    </span>
                    <h3 class="font-black text-xl text-sipega-navy mt-1">
                        Permendikdasmen Nomor 14 Tahun 2025
                    </h3>
                    <p class="text-xs text-gray-400 font-medium">Tabel Acuan Pemotongan & Pengurangan Tunjangan Kinerja Pegawai</p>
                </div>
                <button type="button" onclick="document.getElementById('regulasiModal').classList.add('hidden')" class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Tabel Keterlambatan -->
            <div class="space-y-2">
                <h4 class="font-black text-xs text-sipega-navy uppercase tracking-wider">
                    1. Pengurangan Jam Kerja (Pasal 16 & Lampiran III)
                </h4>
                <div class="overflow-x-auto border border-gray-200 rounded-2xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-[10px] font-black uppercase text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="p-2.5">Rentang Waktu Pelanggaran</th>
                                <th class="p-2.5">Kode</th>
                                <th class="p-2.5 text-center">Besar Pengurangan</th>
                                <th class="p-2.5">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="p-2.5">1 s.d. 30 menit</td>
                                <td class="p-2.5 font-bold">TL1 / PSW1 / TBTK1</td>
                                <td class="p-2.5 text-center font-bold text-rose-600">0,25%</td>
                                <td class="p-2.5 text-gray-500">Per hari</td>
                            </tr>
                            <tr>
                                <td class="p-2.5">31 s.d. 60 menit</td>
                                <td class="p-2.5 font-bold">TL2 / PSW2 / TBTK2</td>
                                <td class="p-2.5 text-center font-bold text-rose-600">0,50%</td>
                                <td class="p-2.5 text-gray-500">Per hari</td>
                            </tr>
                            <tr>
                                <td class="p-2.5">61 s.d. 90 menit</td>
                                <td class="p-2.5 font-bold">TL3 / PSW3 / TBTK3</td>
                                <td class="p-2.5 text-center font-bold text-rose-600">0,75%</td>
                                <td class="p-2.5 text-gray-500">Per hari</td>
                            </tr>
                            <tr>
                                <td class="p-2.5">91 s.d. 120 menit</td>
                                <td class="p-2.5 font-bold">TL4 / PSW4 / TBTK4</td>
                                <td class="p-2.5 text-center font-bold text-rose-600">1,00%</td>
                                <td class="p-2.5 text-gray-500">Per hari</td>
                            </tr>
                            <tr>
                                <td class="p-2.5">Lebih dari 120 menit</td>
                                <td class="p-2.5 font-bold">TL5 / PSW5 / TBTK5</td>
                                <td class="p-2.5 text-center font-bold text-rose-600">1,25%</td>
                                <td class="p-2.5 text-gray-500">Per hari</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabel Ketidakhadiran & Absen Elektronik -->
            <div class="space-y-2">
                <h4 class="font-black text-xs text-sipega-navy uppercase tracking-wider">
                    2. Tidak Hadir & Rekam Kehadiran Elektronik (Pasal 18 & 19)
                </h4>
                <div class="overflow-x-auto border border-gray-200 rounded-2xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-[10px] font-black uppercase text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="p-2.5">Jenis Pelanggaran</th>
                                <th class="p-2.5">Kode</th>
                                <th class="p-2.5 text-center">Besar Pengurangan</th>
                                <th class="p-2.5">Ketentuan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="p-2.5">Tidak Rekam Hadir Datang</td>
                                <td class="p-2.5 font-bold">TKD</td>
                                <td class="p-2.5 text-center font-bold text-rose-600">1,25%</td>
                                <td class="p-2.5 text-gray-500">Dihitung terlambat 121 menit</td>
                            </tr>
                            <tr>
                                <td class="p-2.5">Tidak Rekam Hadir Pulang</td>
                                <td class="p-2.5 font-bold">TKP</td>
                                <td class="p-2.5 text-center font-bold text-rose-600">1,25%</td>
                                <td class="p-2.5 text-gray-500">Dihitung pulang cepat 121 menit</td>
                            </tr>
                            <tr>
                                <td class="p-2.5">Tidak Masuk Kerja Tanpa Alasan Sah</td>
                                <td class="p-2.5 font-bold">ALPA / TK</td>
                                <td class="p-2.5 text-center font-bold text-rose-600">2,50%</td>
                                <td class="p-2.5 text-gray-500">Per hari pada bulan berjalan</td>
                            </tr>
                            <tr>
                                <td class="p-2.5">Alpa Terus Menerus &ge; 10 Hari</td>
                                <td class="p-2.5 font-bold">-</td>
                                <td class="p-2.5 text-center font-bold text-red-600">100%</td>
                                <td class="p-2.5 text-gray-500">Tidak mendapat tunjangan kinerja</td>
                            </tr>
                            <tr>
                                <td class="p-2.5">Akumulasi Keterlambatan &ge; 7,5 Jam (Pasal 17)</td>
                                <td class="p-2.5 font-bold">-</td>
                                <td class="p-2.5 text-center font-bold text-rose-600">+2,50%</td>
                                <td class="p-2.5 text-gray-500">Dihitung 1 hari alpa tambahan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabel Evaluasi Kinerja Triwulan -->
            <div class="space-y-2">
                <h4 class="font-black text-xs text-sipega-navy uppercase tracking-wider">
                    3. Pemotongan Hasil Evaluasi Kinerja Periodik / Triwulan (Pasal 12)
                </h4>
                <div class="overflow-x-auto border border-gray-200 rounded-2xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-[10px] font-black uppercase text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="p-2.5">Predikat Kinerja</th>
                                <th class="p-2.5 text-center">Besar Potongan</th>
                                <th class="p-2.5">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="p-2.5 font-bold text-emerald-700">Baik / Sangat Baik</td>
                                <td class="p-2.5 text-center font-bold">0%</td>
                                <td class="p-2.5 text-gray-500">Tidak ada pemotongan (dibayarkan 100%)</td>
                            </tr>
                            <tr>
                                <td class="p-2.5 font-bold text-amber-700">Butuh Perbaikan</td>
                                <td class="p-2.5 text-center font-bold text-amber-600">20%</td>
                                <td class="p-2.5 text-gray-500">Dipotong pada triwulan berikutnya (dibayarkan 80%)</td>
                            </tr>
                            <tr>
                                <td class="p-2.5 font-bold text-orange-700">Kurang (Misconduct)</td>
                                <td class="p-2.5 text-center font-bold text-orange-600">40%</td>
                                <td class="p-2.5 text-gray-500">Dipotong pada triwulan berikutnya (dibayarkan 60%)</td>
                            </tr>
                            <tr>
                                <td class="p-2.5 font-bold text-red-700">Sangat Kurang</td>
                                <td class="p-2.5 text-center font-bold text-red-600">60%</td>
                                <td class="p-2.5 text-gray-500">Dipotong pada triwulan berikutnya (dibayarkan 40%)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" onclick="document.getElementById('regulasiModal').classList.add('hidden')" class="px-5 py-2.5 bg-sipega-navy text-white text-xs font-black rounded-xl">
                    Tutup Pedoman
                </button>
            </div>
        </div>
    </div>

    <!-- Script Modal Edit -->
    <script>
        function openEditModal(item) {
            document.getElementById('editEmployeeName').innerText = item.name;
            document.getElementById('editEmployeeNip').innerText = 'NIP: ' + item.nip;
            document.getElementById('editGrade').value = item.grade || 6;
            document.getElementById('editPenaltyPercent').value = item.attendance_penalty_percent;
            document.getElementById('editPredicate').value = item.performance_predicate || 'Baik';
            document.getElementById('editNotes').value = item.notes || '';
            document.getElementById('editForm').action = "{{ url('tukin/items') }}/" + item.id;
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</x-app-layout>
