<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight font-sans tracking-wide">
            {{ __('📊 Rekapitulasi Dinas Luar Tahunan - ') }} <span class="text-sipega-orange">{{ $year }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Filter Tahun -->
            <div class="bg-white p-8 rounded-[3rem] shadow-2xl border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h3 class="text-2xl font-black text-sipega-navy">Filter Tahun</h3>
                    <p class="text-gray-400 font-bold text-xs uppercase tracking-widest mt-1">Gunakan untuk memfilter rekapitulasi berdasarkan tahun anggaran</p>
                </div>
                <form action="{{ route('travel.recap') }}" method="GET" class="flex items-center gap-4">
                    <select name="year" class="rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange font-black text-sm px-6">
                        @for($i = date('Y'); $i >= 2024; $i--)
                            <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>Tahun {{ $i }}</option>
                        @endfor
                    </select>
                    <button type="submit" class="bg-sipega-navy text-white px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-black transition shadow-lg">
                        TAMPILKAN 📊
                    </button>
                </form>
            </div>

            <!-- SECTION 1: REKAPITULASI AKTIF (ADA DINAS LUAR) -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] border border-gray-100 relative group">
                <div class="p-8 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="text-xl font-black text-sipega-navy italic">🥇 Peringkat Mobilitas Pegawai (Aktif Mandat)</h3>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1">Daftar pegawai yang aktif menjalankan tugas luar (Formal & Internal) Periode {{ $year }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-sipega-navy text-white text-[10px] font-black uppercase tracking-widest">
                                <th class="px-8 py-5">Nama Pegawai</th>
                                <th class="px-8 py-5">Jabatan</th>
                                <th class="px-8 py-5 text-center">Dinas Formal (ST)</th>
                                <th class="px-8 py-5 text-center">Dinas Dalam (IA)</th>
                                <th class="px-8 py-5 text-center bg-sipega-orange">Total Penugasan</th>
                                <th class="px-8 py-5 text-right italic">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 font-bold">
                            @forelse($activeRecap->sortByDesc('total_trips') as $item)
                                <tr class="hover:bg-gray-50 transition anim-up">
                                    <td class="px-8 py-5 flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-{{ strtolower($item['user']->performance_color ?? 'gray') }}-100 flex items-center justify-center text-{{ strtolower($item['user']->performance_color ?? 'gray') }}-600 font-black text-xs border border-{{ strtolower($item['user']->performance_color ?? 'gray') }}-200">
                                            {{ substr($item['user']->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sipega-navy text-sm font-black">{{ $item['user']->name }}</p>
                                            <p class="text-[9px] font-bold text-gray-400 mt-0.5 tracking-tighter uppercase leading-none opacity-60">{{ $item['user']->nip ?? 'NIP: -' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-gray-500 font-bold text-xs uppercase">{{ $item['user']->position ?? '-' }}</td>
                                    <td class="px-8 py-5 text-center font-black text-emerald-600 text-lg">{{ $item['external_count'] }}</td>
                                    <td class="px-8 py-5 text-center font-black text-blue-600 text-lg">{{ $item['internal_count'] }}</td>
                                    <td class="px-8 py-5 text-center font-black text-white bg-sipega-orange/90 text-2xl shadow-inner">{{ $item['total_trips'] }}</td>
                                    <td class="px-8 py-5 text-right font-black italic text-[10px] text-sipega-navy opacity-40">TUGAS AKTIF ✅</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-8 py-20 text-center text-gray-400 italic">Belum ada data dinas luar terekam untuk tahun {{ $year }}.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 2: REKAPITULASI STANDBY (TIDAK PERNAH DINAS LUAR) -->
            <div class="bg-gray-50 overflow-hidden shadow-inner sm:rounded-[3rem] p-10 border-4 border-dashed border-gray-200">
                <div class="mb-10 text-center">
                    <h3 class="text-2xl font-black text-sipega-navy flex items-center justify-center gap-3">
                        🏢 Pegawai Standby / Dalam Kantor (Zero Task Out)
                    </h3>
                    <p class="text-gray-400 font-extrabold text-xs uppercase tracking-widest mt-2">Daftar Pegawai yang sama sekali tidak pernah ditugaskan keluar di Periode {{ $year }}</p>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    @forelse($zeroRecap as $z)
                        <div class="bg-white p-6 rounded-[2.5rem] shadow-xl border border-gray-100 flex flex-col items-center text-center group hover:scale-105 transition transform">
                            <div class="w-16 h-16 rounded-3xl bg-gray-50 border-2 border-gray-100 p-1 mb-4 flex items-center justify-center group-hover:bg-red-50 group-hover:border-red-100 transition">
                                <span class="text-2xl font-black text-gray-300 group-hover:text-red-400">0</span>
                            </div>
                            <h4 class="text-xs font-black text-sipega-navy uppercase leading-tight mb-1">{{ $z['user']->name }}</h4>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">{{ $z['user']->position ?? 'Pegawai' }}</p>
                            <div class="mt-4 pt-4 border-t border-gray-50 w-full">
                                <span class="text-[8px] font-black italic text-red-400 uppercase tracking-widest">OFFICE STANDBY ONLY</span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-sipega-orange font-black uppercase tracking-widest animate-pulse">
                            🎉 BRAVO! Semua Pegawai Telah Terjun Keluar Menjalankan Tugas.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Summary Footer -->
            <div class="bg-sipega-navy p-12 rounded-[4rem] shadow-2xl flex flex-col md:flex-row items-center justify-between gap-8 text-white relative overflow-hidden">
                <!-- Abstract Graphics -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-20 -mt-20 blur-3xl"></div>
                
                <div class="relative z-10 text-center md:text-left">
                    <h3 class="text-3xl font-black mb-2 flex items-center justify-center md:justify-start gap-4">
                        📊 Akumulasi {{ $year }}
                        <span class="text-sm bg-orange-500 px-3 py-1 rounded-full uppercase tracking-widest">Yearly Recap</span>
                    </h3>
                    <p class="text-white/50 text-xs font-black uppercase tracking-widest tracking-[0.2em]">Total Penugasan Berdasarkan Kedisplinan SIPEGA</p>
                </div>

                <div class="flex items-center gap-10">
                    <div class="text-center">
                        <p class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Mobilitas Aktif</p>
                        <p class="text-4xl font-black text-sipega-orange">{{ $activeRecap->count() }}</p>
                        <p class="text-[9px] font-bold text-white/50 italic capitalize mt-1">Pegawai Bertugas</p>
                    </div>
                    <div class="w-px h-16 bg-white/10"></div>
                    <div class="text-center">
                        <p class="text-[10px] font-black text-white/40 uppercase tracking-widest mb-1">Standby Hanya Kantor</p>
                        <p class="text-4xl font-black text-blue-400">{{ $zeroRecap->count() }}</p>
                        <p class="text-[9px] font-bold text-white/50 italic capitalize mt-1">Pegawai Di Kantor</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
