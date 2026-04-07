<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight font-sans tracking-wide">
            {{ __('Modul Kalkulator Tukin (Permendikdasmen 14/2025)') }} ⚖️
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Statistik & Kontrol -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-[2rem] shadow-xl border border-gray-100">
                    <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Total Pegawai</p>
                    <p class="text-3xl font-black text-sipega-navy">{{ count($users) }} <span class="text-sm font-bold text-gray-300">Orang</span></p>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-xl border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Rekapitulasi</p>
                        <p class="text-lg font-black text-sipega-navy">Export Excel (Monthly)</p>
                    </div>
                    <a href="{{ route('tukin.export') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white p-3 rounded-2xl shadow-lg transition transform hover:-translate-y-1">
                        📥 DOWNLOAD
                    </a>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-xl border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Konfigurasi</p>
                        <p class="text-lg font-black text-sipega-navy">Kelas Jabatan</p>
                    </div>
                    <a href="{{ route('tukin.classes') }}" class="bg-sipega-navy hover:bg-black text-white p-3 rounded-2xl shadow-lg transition transform hover:-translate-y-1 text-sm font-bold">
                        ⚙️ KELOLA
                    </a>
                </div>
            </div>

            <!-- Tabel Rekapitulasi Utama -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] border border-gray-100">
                <div class="p-10 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-xl font-black text-sipega-navy">Rekapitulasi Tinjauan Tukin</h3>
                        <p class="text-xs text-gray-400 font-bold tracking-tight">Sesuai Perhitungan Progresif TL/PSW & Predikat Kinerja 14/2025</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-sipega-navy text-white text-[10px] font-black uppercase tracking-widest">
                                <th class="px-6 py-4">Nama / NIP</th>
                                <th class="px-6 py-4">Kelas / Base</th>
                                <th class="px-6 py-4">Pot. Absen (%)</th>
                                <th class="px-6 py-4">Predikat / Pot. Kinerja</th>
                                <th class="px-6 py-4 text-right">Net Tukin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($users as $u)
                                @php $t = $u->calculateMonthlyTukin(); @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-5">
                                        <p class="font-black text-sipega-navy text-sm">{{ $u->name }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $u->nip ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="font-bold text-xs">{{ $t['job_class'] }}</p>
                                        <p class="text-[10px] text-emerald-600 font-black tracking-tighter">Rp {{ number_format($t['base_tukin'], 0, ',', '.') }}</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                                <div class="bg-red-500 h-full" style="width: {{ min($t['attendance_penalty_percent'] * 10, 100) }}%"></div>
                                            </div>
                                            <span class="text-[10px] font-black text-red-600">{{ $t['attendance_penalty_percent'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="bg-gray-100 text-[9px] px-2 py-1 rounded-full font-black uppercase tracking-widest">{{ $t['performance_predicate'] }}</span>
                                        <p class="text-[10px] text-orange-600 font-black mt-1">-{{ $t['performance_penalty_percent'] }}%</p>
                                    </td>
                                    <td class="px-6 py-5 text-right font-black text-sipega-navy italic">
                                        Rp {{ number_format($t['net_tukin'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
