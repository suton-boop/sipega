<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight font-sans tracking-wide">
            {{ __('Transparansi Tunjangan Kinerja') }} 💰
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
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
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest italic">Zona Waktu: Asia/Makassar (WITA)</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
