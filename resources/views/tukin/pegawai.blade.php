<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight font-sans tracking-wide">
            {{ __('Transparansi Tunjangan Kinerja') }} 💰
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Tombol Pilihan Mode & Filter Periode Bulan / Tahun -->
            <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] shadow-xl border border-gray-100">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 pb-6 border-b border-gray-100">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 text-amber-800 text-[10px] font-black uppercase tracking-widest rounded-full mb-2">
                            <span>💰 Slip & Laporan Periode</span>
                        </div>
                        <h3 class="text-xl font-black text-sipega-navy">Pusat Slip Tukin & Laporan Harian</h3>
                        <p class="text-xs text-gray-500 font-medium">Lihat rincian kalkulasi Permendikdasmen 14/2025 atau unduh slip resmi PDF sesuai bulan & tahun.</p>
                    </div>

                    <!-- Tombol Pilihan -->
                    <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-2xl border border-slate-200/60 shadow-inner">
                        <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md">
                            <span>💰 Slip Tukin</span>
                            <span class="text-[8px] bg-white/20 text-white px-2 py-0.5 rounded-full font-black">AKTIF</span>
                        </span>

                        <a href="{{ route('attendance.recap', ['id' => $user->id, 'month' => $month ?? now()->format('Y-m')]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-slate-700 hover:text-sipega-navy hover:bg-white text-xs font-bold uppercase tracking-wider rounded-xl transition">
                            <span>📄 Buka Laporan Harian</span>
                        </a>
                    </div>
                </div>

                <div class="pt-6 flex flex-wrap justify-between items-center gap-4">
                    <!-- Form Filter Bulan & Tahun -->
                    <form method="GET" action="{{ route('tukin.index') }}" class="flex items-center gap-3">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1">Filter Periode Bulan & Tahun</label>
                            <div class="flex items-center gap-2">
                                <input type="month" name="month" value="{{ $month ?? now()->format('Y-m') }}" onchange="this.form.submit()" class="text-xs font-black rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <span class="text-xs font-bold text-gray-500">({{ \Carbon\Carbon::parse(($month ?? now()->format('Y-m')) . '-01')->locale('id')->isoFormat('MMMM Y') }})</span>
                            </div>
                        </div>
                    </form>

                    <!-- Tombol Unduh PDF Cepat -->
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('tukin.download_slip', ['id' => $user->id, 'period' => $month ?? now()->format('Y-m'), 'month' => $month ?? now()->format('Y-m')]) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow-md shadow-emerald-600/20 transition transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Unduh PDF Slip Tukin ({{ $month ?? now()->format('Y-m') }})</span>
                        </a>

                        <a href="{{ route('attendance.download_pdf', ['id' => $user->id, 'month' => $month ?? now()->format('Y-m')]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-700 hover:bg-sky-800 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow-md shadow-sky-700/20 transition transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Unduh PDF Laporan Harian</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Detail Tukin Pegawai -->
            <div class="bg-gradient-to-br from-sipega-navy to-[#001a33] p-12 rounded-[3.5rem] shadow-2xl text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <h3 class="text-xs font-black uppercase text-sipega-orange tracking-[.4em] mb-4">Estimasi Bersih (Take Home Pay)</h3>
                    <p class="text-6xl font-black mb-8">Rp {{ number_format($tukin['net_tukin'], 0, ',', '.') }}</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-white/10">
                        <div>
                            <p class="text-[10px] font-black uppercase opacity-50 tracking-widest mb-2 text-sipega-orange">Komponen Hak</p>
                            <div class="flex justify-between items-center bg-white/5 p-4 rounded-2xl border border-white/10">
                                <span class="text-sm font-bold">Base Tukin ({{ $tukin['job_class'] }})</span>
                                <span class="font-black">Rp {{ number_format($tukin['base_tukin'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase opacity-50 tracking-widest mb-2 text-sipega-orange">Potongan Kewajiban</p>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center bg-red-500/10 p-4 rounded-2xl border border-red-500/20">
                                    <span class="text-sm font-bold">Total Potongan ({{ $tukin['attendance_penalty_percent'] + $tukin['performance_penalty_percent'] }}%)</span>
                                    <span class="font-black text-red-400">-Rp {{ number_format($tukin['attendance_penalty_amount'] + $tukin['performance_penalty_amount'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Background Decoration -->
                <div class="absolute top-0 right-0 p-4 opacity-10 pointer-events-none text-9xl font-black rotate-12 -mr-16 -mt-16">PAYDAY</div>
            </div>

            <!-- Rincian Analisis (Permendikdasmen 14/2025) -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] p-10 border border-gray-100">
                <h3 class="text-2xl font-black text-sipega-navy mb-8 flex items-center gap-3">📊 Analisis Perhitungan Progresif</h3>
                
                <div class="space-y-6">
                    <!-- Section: Kehadiran -->
                    <div class="p-6 bg-gray-50 rounded-[2rem] border border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <p class="font-black text-sipega-navy uppercase text-xs tracking-widest">⚡ Kedisiplinan (Presensi)</p>
                            <span class="text-xl font-black text-red-600">-{{ $tukin['attendance_penalty_percent'] }}%</span>
                        </div>
                        <p class="text-xs text-gray-500 font-medium leading-relaxed">Sesuai aturan 14/2025, keterlambatan (TL) dan pulang cepat (PSW) dihitung progresif (0,25% - 1,25%) per kejadian sesuai durasi menit.</p>
                    </div>

                    <!-- Section: Kinerja -->
                    <div class="p-6 bg-gray-50 rounded-[2rem] border border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <p class="font-black text-sipega-navy uppercase text-xs tracking-widest">🏆 Kualitas Kinerja</p>
                            <span class="text-xl font-black {{ $tukin['performance_penalty_percent'] > 0 ? 'text-orange-600' : 'text-emerald-600' }}">
                                {{ $tukin['performance_penalty_percent'] > 0 ? '-'.$tukin['performance_penalty_percent'].'%' : 'Optimal (100%)' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="bg-sipega-navy text-white text-[10px] px-3 py-1 rounded-full font-black uppercase tracking-widest">{{ $tukin['performance_predicate'] }}</span>
                            <p class="text-xs text-gray-500 font-medium">Predikat kinerja Triwulan/Bulanan di bawah "Baik" akan memicu pemotongan Tukin (20%, 40%, 60%).</p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 pt-6 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest italic">Data Bulan Berjalan: {{ $tukin['month'] }}</p>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('tukin.download_slip', ['period' => $month ?? now()->format('Y-m')]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-xl shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Cetak Slip Tukin (PDF)
                        </a>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest italic">WITA (UTC+8)</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
