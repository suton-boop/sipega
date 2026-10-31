<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-amber-500 text-white shadow-sm">
                        ⚙️ PUSAT PENGATURAN SISTEM
                    </span>
                    <span class="text-xs text-gray-400 font-bold">•</span>
                    <span class="text-xs font-bold text-gray-500">Konfigurasi Parameter & Regulasi</span>
                </div>
                <h2 class="font-black text-2xl text-sipega-navy mt-1 tracking-tight">
                    Pengaturan Aturan & Regulasi Tukin
                </h2>
                <p class="text-xs text-gray-400 font-medium mt-0.5">
                    Ubah parameter persentase pemotongan, batas waktu kehadiran, dan plafon tukin. Perubahan langsung berlaku ke seluruh kalkulasi sistem.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('tukin.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white hover:bg-gray-50 text-sipega-navy text-xs font-black rounded-xl border border-gray-200 shadow-sm transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>Ke Menu Hitung Tukin</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ activeTab: window.location.hash === '#grade' ? 'grade' : (window.location.hash === '#general' ? 'general' : 'tukin') }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Notifikasi Flash -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border-2 border-emerald-300 rounded-2xl flex items-center justify-between text-emerald-900 shadow-sm animate-pulse">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">✅</span>
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider">Perubahan Berhasil Disimpan</p>
                            <p class="text-xs font-semibold mt-0.5">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-black text-xs px-3 py-1.5 bg-emerald-100 hover:bg-emerald-200 rounded-lg">
                        Tutup
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border-2 border-rose-300 rounded-2xl flex items-center justify-between text-rose-900 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <p class="text-xs font-black uppercase tracking-wider">Terjadi Kesalahan</p>
                            <p class="text-xs font-semibold mt-0.5">{{ session('error') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900 font-black text-xs px-3 py-1.5 bg-rose-100 hover:bg-rose-200 rounded-lg">
                        Tutup
                    </button>
                </div>
            @endif

            <!-- TAB NAVIGASI UTAMA -->
            <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100 flex flex-wrap gap-2">
                <button type="button" 
                        @click="activeTab = 'tukin'; window.location.hash = 'tukin'" 
                        :class="activeTab === 'tukin' ? 'bg-sipega-navy text-white shadow-md' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-5 py-3 rounded-xl font-black text-xs transition flex items-center gap-2 cursor-pointer">
                    <span>📜</span>
                    <span>1. Aturan Pemotongan Tukin (Permendikdasmen 14/2025)</span>
                </button>
                <button type="button" 
                        @click="activeTab = 'grade'; window.location.hash = 'grade'" 
                        :class="activeTab === 'grade' ? 'bg-sipega-navy text-white shadow-md' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-5 py-3 rounded-xl font-black text-xs transition flex items-center gap-2 cursor-pointer">
                    <span>💰</span>
                    <span>2. Plafon Tarif Grade (Perpres 18/2025)</span>
                </button>
                <button type="button" 
                        @click="activeTab = 'general'; window.location.hash = 'general'" 
                        :class="activeTab === 'general' ? 'bg-sipega-navy text-white shadow-md' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-5 py-3 rounded-xl font-black text-xs transition flex items-center gap-2 cursor-pointer">
                    <span>⚙️</span>
                    <span>3. Pengaturan Umum Sistem</span>
                </button>
            </div>

            <!-- FORM UTAMA PENGATURAN -->
            <form action="{{ route('settings.tukin.update') }}" method="POST" id="settingsForm">
                @csrf

                <!-- ========================================== -->
                <!-- TAB 1: ATURAN PEMOTONGAN TUKIN             -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'tukin'" class="space-y-6">

                    <!-- Banner Info Aturan -->
                    <div class="bg-gradient-to-r from-blue-900 via-indigo-950 to-slate-900 text-white p-6 rounded-[2rem] shadow-md border border-blue-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <span class="px-2.5 py-0.5 bg-amber-400 text-black text-[9px] font-black rounded-full uppercase tracking-wider">
                                Aturan Pemotongan Aktif
                            </span>
                            <h3 class="font-black text-lg text-white mt-1">
                                {{ $tukinRules['regulation_name'] }}
                            </h3>
                            <p class="text-xs text-slate-300 font-medium">
                                Ubah persentase atau parameter di bawah ini. Rumus pemotongan kehadiran, alpa, evaluasi kinerja triwulan, dan modal tabel acuan akan langsung menyesuaikan secara otomatis.
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" onclick="document.getElementById('resetModal').classList.remove('hidden')" class="px-4 py-2.5 bg-rose-600/80 hover:bg-rose-700 text-white text-xs font-black rounded-xl border border-rose-500/50 shadow-sm transition">
                                ↺ Reset ke Standar Permendikdasmen 14/2025
                            </button>
                        </div>
                    </div>

                    <!-- KARTU 0: INFORMASI DASAR REGULASI -->
                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center font-black text-base">
                                📋
                            </div>
                            <div>
                                <h4 class="font-black text-sm text-sipega-navy uppercase tracking-wider">
                                    Dokumen & Regulasi Acuan Resmi
                                </h4>
                                <p class="text-xs text-gray-400 font-medium">Judul dan keterangan peraturan yang tercantum pada kop pedoman & laporan</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1">
                                    Nama / Nomor Peraturan Pemotongan
                                </label>
                                <input type="text" name="tukin_regulation_name" value="{{ $tukinRules['regulation_name'] }}" required
                                       class="w-full text-xs font-bold rounded-xl border-gray-300 focus:border-sipega-navy focus:ring-sipega-navy shadow-sm">
                                <p class="text-[10px] text-gray-400 mt-1">Contoh: Permendikdasmen Nomor 14 Tahun 2025</p>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1">
                                    Keterangan / Sub-Judul Tabel Acuan
                                </label>
                                <input type="text" name="tukin_regulation_desc" value="{{ $tukinRules['regulation_desc'] }}" required
                                       class="w-full text-xs font-bold rounded-xl border-gray-300 focus:border-sipega-navy focus:ring-sipega-navy shadow-sm">
                                <p class="text-[10px] text-gray-400 mt-1">Contoh: Tabel Acuan Pemotongan & Pengurangan Tunjangan Kinerja Pegawai</p>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 1: PENGURANGAN JAM KERJA (PASAL 16 & LAMPIRAN III) -->
                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center font-black text-base">
                                ⏱️
                            </div>
                            <div>
                                <h4 class="font-black text-sm text-sipega-navy uppercase tracking-wider">
                                    1. Pengurangan Jam Kerja (Pasal 16 & Lampiran III)
                                </h4>
                                <p class="text-xs text-gray-400 font-medium">Besar persentase pemotongan keterlambatan (TL) dan pulang sebelum waktunya (PSW) per hari</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <!-- TL1 -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[9px] font-black rounded uppercase">TL1 / PSW1</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">1 s.d. 30 Menit</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_tl1_pct" value="{{ $tukinRules['tl1_pct'] }}" required
                                           class="w-full text-sm font-black text-rose-600 rounded-xl border-gray-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 0,25%</p>
                            </div>

                            <!-- TL2 -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[9px] font-black rounded uppercase">TL2 / PSW2</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">31 s.d. 60 Menit</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_tl2_pct" value="{{ $tukinRules['tl2_pct'] }}" required
                                           class="w-full text-sm font-black text-rose-600 rounded-xl border-gray-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 0,50%</p>
                            </div>

                            <!-- TL3 -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[9px] font-black rounded uppercase">TL3 / PSW3</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">61 s.d. 90 Menit</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_tl3_pct" value="{{ $tukinRules['tl3_pct'] }}" required
                                           class="w-full text-sm font-black text-rose-600 rounded-xl border-gray-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 0,75%</p>
                            </div>

                            <!-- TL4 -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[9px] font-black rounded uppercase">TL4 / PSW4</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">91 s.d. 120 Menit</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_tl4_pct" value="{{ $tukinRules['tl4_pct'] }}" required
                                           class="w-full text-sm font-black text-rose-600 rounded-xl border-gray-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 1,00%</p>
                            </div>

                            <!-- TL5 -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[9px] font-black rounded uppercase">TL5 / PSW5</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">&gt; 120 Menit</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_tl5_pct" value="{{ $tukinRules['tl5_pct'] }}" required
                                           class="w-full text-sm font-black text-rose-600 rounded-xl border-gray-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 1,25%</p>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 2: TIDAK HADIR & REKAM KEHADIRAN (PASAL 18 & 19) -->
                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-black text-base">
                                🚫
                            </div>
                            <div>
                                <h4 class="font-black text-sm text-sipega-navy uppercase tracking-wider">
                                    2. Tidak Hadir & Rekam Kehadiran Elektronik (Pasal 18 & 19)
                                </h4>
                                <p class="text-xs text-gray-400 font-medium">Ketidakhadiran tanpa alasan sah, absen rekam datang/pulang, dan akumulasi keterlambatan</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- TKD -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[9px] font-black rounded uppercase">Kode: TKD</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">Tidak Rekam Hadir Datang</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_tkd_pct" value="{{ $tukinRules['tkd_pct'] }}" required
                                           class="w-full text-sm font-black text-rose-600 rounded-xl border-gray-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 1,25%</p>
                            </div>

                            <!-- TKP -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[9px] font-black rounded uppercase">Kode: TKP</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">Tidak Rekam Hadir Pulang</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_tkp_pct" value="{{ $tukinRules['tkp_pct'] }}" required
                                           class="w-full text-sm font-black text-rose-600 rounded-xl border-gray-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 1,25%</p>
                            </div>

                            <!-- ALPA / TK -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-rose-100 text-rose-800 text-[9px] font-black rounded uppercase">Kode: ALPA / TK</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">Alpa / Tanpa Alasan Sah</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_alpa_pct" value="{{ $tukinRules['alpa_pct'] }}" required
                                           class="w-full text-sm font-black text-rose-600 rounded-xl border-gray-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">% / hari</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 2,50% per hari</p>
                            </div>

                            <!-- AKUMULASI KETERLAMBATAN -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-purple-100 text-purple-800 text-[9px] font-black rounded uppercase">Pasal 17</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">Akumulasi Keterlambatan</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_accum_penalty_pct" value="{{ $tukinRules['accum_penalty_pct'] }}" required
                                           class="w-full text-sm font-black text-rose-600 rounded-xl border-gray-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <div class="mt-1 flex items-center gap-1 text-[10px] text-gray-400">
                                    <span>Ambang batas:</span>
                                    <input type="number" step="0.5" min="1" max="100" name="tukin_accum_hours_threshold" value="{{ $tukinRules['accum_hours_threshold'] }}" class="w-12 py-0.5 px-1 text-[10px] font-bold rounded border-gray-300">
                                    <span>jam</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 3: EVALUASI KINERJA PERIODIK (PASAL 12) -->
                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-black text-base">
                                📊
                            </div>
                            <div>
                                <h4 class="font-black text-sm text-sipega-navy uppercase tracking-wider">
                                    3. Pemotongan Hasil Evaluasi Kinerja Triwulan (Pasal 12)
                                </h4>
                                <p class="text-xs text-gray-400 font-medium">Persentase potongan tukin yang diterapkan pada triwulan berikutnya sesuai predikat SKP</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- BAIK -->
                            <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200">
                                <span class="px-2 py-0.5 bg-emerald-200 text-emerald-900 text-[9px] font-black rounded uppercase">Predikat</span>
                                <p class="text-xs font-black text-emerald-800 mt-1.5">Baik / Sangat Baik</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_perf_baik_pct" value="{{ $tukinRules['perf_baik_pct'] }}" required
                                           class="w-full text-sm font-black text-emerald-700 rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 0% (Dibayar 100%)</p>
                            </div>

                            <!-- BUTUH PERBAIKAN -->
                            <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200">
                                <span class="px-2 py-0.5 bg-amber-200 text-amber-900 text-[9px] font-black rounded uppercase">Predikat</span>
                                <p class="text-xs font-black text-amber-800 mt-1.5">Butuh Perbaikan</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_perf_cukup_pct" value="{{ $tukinRules['perf_cukup_pct'] }}" required
                                           class="w-full text-sm font-black text-amber-700 rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 20% (Dibayar 80%)</p>
                            </div>

                            <!-- KURANG -->
                            <div class="p-4 rounded-2xl bg-orange-50/50 border border-orange-200">
                                <span class="px-2 py-0.5 bg-orange-200 text-orange-900 text-[9px] font-black rounded uppercase">Predikat</span>
                                <p class="text-xs font-black text-orange-800 mt-1.5">Kurang (Misconduct)</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_perf_kurang_pct" value="{{ $tukinRules['perf_kurang_pct'] }}" required
                                           class="w-full text-sm font-black text-orange-700 rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 40% (Dibayar 60%)</p>
                            </div>

                            <!-- SANGAT KURANG -->
                            <div class="p-4 rounded-2xl bg-red-50/50 border border-red-200">
                                <span class="px-2 py-0.5 bg-red-200 text-red-900 text-[9px] font-black rounded uppercase">Predikat</span>
                                <p class="text-xs font-black text-red-800 mt-1.5">Sangat Kurang</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_perf_sangat_kurang_pct" value="{{ $tukinRules['perf_sangat_kurang_pct'] }}" required
                                           class="w-full text-sm font-black text-red-700 rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 60% (Dibayar 40%)</p>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 4: TARIF PAJAK PPH 21 -->
                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-teal-100 text-teal-800 flex items-center justify-center font-black text-base">
                                💵
                            </div>
                            <div>
                                <h4 class="font-black text-sm text-sipega-navy uppercase tracking-wider">
                                    4. Tarif Pajak Penghasilan (PPh 21 Berdasarkan Golongan ASN)
                                </h4>
                                <p class="text-xs text-gray-400 font-medium">Besar tarif pajak PPh 21 final atas penerimaan tunjangan kinerja</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- GOLONGAN IV -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-teal-100 text-teal-800 text-[9px] font-black rounded uppercase">ASN Golongan IV</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">Gol. IV/a s.d. IV/e</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_tax_gol_4" value="{{ $tukinRules['tax_gol_4'] }}" required
                                           class="w-full text-sm font-black text-teal-700 rounded-xl border-gray-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 15%</p>
                            </div>

                            <!-- GOLONGAN III & PPPK TINGKAT TINGGI -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-teal-100 text-teal-800 text-[9px] font-black rounded uppercase">ASN Golongan III</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">Gol. III/a - III/d & PPPK IX+</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_tax_gol_3" value="{{ $tukinRules['tax_gol_3'] }}" required
                                           class="w-full text-sm font-black text-teal-700 rounded-xl border-gray-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 5%</p>
                            </div>

                            <!-- GOLONGAN I & II -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200">
                                <span class="px-2 py-0.5 bg-teal-100 text-teal-800 text-[9px] font-black rounded uppercase">ASN Golongan I & II</span>
                                <p class="text-xs font-bold text-gray-700 mt-1.5">Gol. I/a - II/d</p>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="number" step="0.01" min="0" max="100" name="tukin_tax_gol_1_2" value="{{ $tukinRules['tax_gol_1_2'] }}" required
                                           class="w-full text-sm font-black text-teal-700 rounded-xl border-gray-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm">
                                    <span class="text-xs font-black text-gray-500">%</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Standar: 0%</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ========================================== -->
                <!-- TAB 2: PLAFON TARIF TUKIN PER GRADE        -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'grade'" class="space-y-6" style="display: none;">
                    <div class="bg-gradient-to-r from-sipega-navy via-slate-900 to-indigo-950 text-white p-6 rounded-[2rem] shadow-md border border-slate-800">
                        <span class="px-2.5 py-0.5 bg-amber-400 text-black text-[9px] font-black rounded-full uppercase tracking-wider">
                            Standar Plafon Nominal Tukin
                        </span>
                        <h3 class="font-black text-lg text-white mt-1">
                            Perpres Nomor 18 Tahun 2025 (KJ 1 s.d. 17)
                        </h3>
                        <p class="text-xs text-slate-300 font-medium">
                            Jika suatu saat pemerintah menerbitkan Perpres kenaikan tunjangan kinerja baru, Anda dapat langsung mengupdate nominal rupiah per kelas jabatan di sini tanpa perlu mengubah kode program.
                        </p>
                    </div>

                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50 text-[10px] font-black uppercase text-gray-600 border-b border-gray-200">
                                    <tr>
                                        <th class="p-3">Kelas Jabatan (Grade)</th>
                                        <th class="p-3">Default Resmi (Perpres 18/2025)</th>
                                        <th class="p-3">Plafon Nominal Aktif (Rp)</th>
                                        <th class="p-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @for($g = 17; $g >= 1; $g--)
                                        @php
                                            $defRate = $officialRates[(string)$g] ?? 0;
                                            $activeRate = $rates[(string)$g] ?? $defRate;
                                            $isCustom = ($activeRate != $defRate);
                                        @endphp
                                        <tr class="hover:bg-gray-50/60 transition">
                                            <td class="p-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-7 h-7 rounded-lg bg-sipega-navy text-white flex items-center justify-center font-black text-xs">
                                                        {{ $g }}
                                                    </span>
                                                    <span class="font-black text-gray-800">Grade {{ $g }} (KJ {{ $g }})</span>
                                                </div>
                                            </td>
                                            <td class="p-3 text-gray-500 font-bold">
                                                Rp {{ number_format($defRate, 0, ',', '.') }}
                                            </td>
                                            <td class="p-3">
                                                <div class="flex items-center gap-2 max-w-xs">
                                                    <span class="text-xs font-black text-gray-500">Rp</span>
                                                    <input type="number" name="tukin_grade_{{ $g }}" value="{{ (int)$activeRate }}" min="0" step="500" required
                                                           class="w-full text-xs font-black text-sipega-navy rounded-xl border-gray-300 focus:border-sipega-navy focus:ring-sipega-navy shadow-sm">
                                                </div>
                                            </td>
                                            <td class="p-3 text-center">
                                                @if($isCustom)
                                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[9px] font-black rounded-full uppercase">Kustom</span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[9px] font-black rounded-full uppercase">Standar</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- TAB 3: PENGATURAN UMUM SISTEM              -->
                <!-- ========================================== -->
                <div x-show="activeTab === 'general'" class="space-y-6" style="display: none;">
                    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-800 flex items-center justify-center font-black text-base">
                                🏢
                            </div>
                            <div>
                                <h4 class="font-black text-sm text-sipega-navy uppercase tracking-wider">
                                    Identitas Satuan Kerja & Parameter Umum
                                </h4>
                                <p class="text-xs text-gray-400 font-medium">Nama instansi dan toggle modul global aplikasi</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-1">
                                    Nama Satuan Kerja / Instansi
                                </label>
                                <input type="text" name="institution_name" value="{{ $generalSettings['institution_name'] }}" required
                                       class="w-full text-xs font-bold rounded-xl border-gray-300 focus:border-sipega-navy focus:ring-sipega-navy shadow-sm">
                            </div>

                            <div class="pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- SWITCH 1: TOMBOL & KARTU SLIP TUKIN -->
                                <div class="p-4 rounded-2xl bg-emerald-50/60 border-2 {{ $generalSettings['show_slip_tukin'] == '1' ? 'border-emerald-300' : 'border-gray-200' }} flex flex-col justify-between gap-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <span class="text-base">🎫</span>
                                                <p class="text-xs font-black text-sipega-navy">Menu & Tombol Slip Tukin</p>
                                            </div>
                                            <p class="text-[10px] text-gray-500 leading-relaxed">
                                                Atur apakah kartu Slip Tukin di Pusat Dokumen, tombol "Unduh PDF", dan menu navbar dimunculkan atau disembunyikan (hidden).
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-emerald-200/50">
                                        <span class="text-[10px] font-black {{ $generalSettings['show_slip_tukin'] == '1' ? 'text-emerald-700 bg-emerald-100' : 'text-gray-500 bg-gray-100' }} px-2 py-0.5 rounded-full uppercase tracking-wider">
                                            {{ $generalSettings['show_slip_tukin'] == '1' ? 'Tampil (Muncul)' : 'Disembunyikan (Hidden)' }}
                                        </span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="show_slip_tukin" value="1" {{ $generalSettings['show_slip_tukin'] == '1' ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                        </label>
                                    </div>
                                </div>

                                <!-- SWITCH 2: MASTER MODUL TUKIN -->
                                <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200 flex flex-col justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="text-base">📊</span>
                                            <p class="text-xs font-black text-gray-800">Modul Perhitungan Tukin</p>
                                        </div>
                                        <p class="text-[10px] text-gray-500 leading-relaxed">
                                            Akses utama modul perhitungan dan slip tunjangan kinerja untuk pegawai umum.
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                        <span class="text-[10px] font-black {{ $generalSettings['is_tukin_active'] == '1' ? 'text-emerald-700 bg-emerald-100' : 'text-gray-500 bg-gray-100' }} px-2 py-0.5 rounded-full uppercase tracking-wider">
                                            {{ $generalSettings['is_tukin_active'] == '1' ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_tukin_active" value="1" {{ $generalSettings['is_tukin_active'] == '1' ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                        </label>
                                    </div>
                                </div>

                                <!-- SWITCH 3: REWARD CENTER -->
                                <div class="p-4 rounded-2xl bg-slate-50 border border-gray-200 flex flex-col justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="text-base">🏆</span>
                                            <p class="text-xs font-black text-gray-800">Reward Center (Wall of Fame)</p>
                                        </div>
                                        <p class="text-[10px] text-gray-500 leading-relaxed">
                                            Tampilkan peringkat ASN BerAKHLAK dan rekap capaian kinerja di beranda.
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                        <span class="text-[10px] font-black {{ $generalSettings['is_reward_active'] == '1' ? 'text-emerald-700 bg-emerald-100' : 'text-gray-500 bg-gray-100' }} px-2 py-0.5 rounded-full uppercase tracking-wider">
                                            {{ $generalSettings['is_reward_active'] == '1' ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_reward_active" value="1" {{ $generalSettings['is_reward_active'] == '1' ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARIS TOMBOL AKSI STICKY BOTTOM -->
                <div class="bg-white/95 backdrop-blur-md p-4 rounded-2xl shadow-lg border border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-3 sticky bottom-4 z-20">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <p class="text-xs font-bold text-gray-600">
                            Pastikan data aturan sudah benar sebelum menyimpan.
                        </p>
                    </div>

                    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                        <a href="{{ route('tukin.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-black rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-md transition transform hover:-translate-y-0.5 cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Simpan Seluruh Perubahan Aturan</span>
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>

    <!-- MODAL KONFIRMASI RESET STANDAR -->
    <div id="resetModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] shadow-2xl max-w-md w-full p-6 border border-gray-100 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl font-black shrink-0">
                    ⚠️
                </div>
                <div>
                    <h4 class="font-black text-base text-sipega-navy">Konfirmasi Reset Aturan</h4>
                    <p class="text-xs text-gray-400 font-medium">Kembalikan ke standar resmi Permendikdasmen 14/2025</p>
                </div>
            </div>

            <p class="text-xs text-gray-600 leading-relaxed">
                Tindakan ini akan mengembalikan seluruh persentase pemotongan jam kerja (TL 1-5), ketidakhadiran (TKD/TKP/Alpa), evaluasi kinerja, pajak, dan nominal plafon tukin grade 1-17 ke standar asli bawaan <strong>Permendikdasmen No. 14 Tahun 2025</strong> dan <strong>Perpres No. 18 Tahun 2025</strong>.
            </p>

            <form action="{{ route('settings.tukin.reset') }}" method="POST" class="pt-2 flex justify-end gap-2">
                @csrf
                <button type="button" onclick="document.getElementById('resetModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-black rounded-xl">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black rounded-xl shadow-md transition">
                    Ya, Reset ke Standar Resmi
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
