<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Pusat Dokumen Fisik 🗄️
                </h2>
                <p class="text-[10px] font-black text-sipega-orange uppercase tracking-[0.3em] mt-1">SIPEGA &bull; DOKUMEN HASIL & BUKTI FISIK PEGAWAI</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">

            <!-- Welcome Banner -->
            <div class="bg-sipega-navy p-10 rounded-[3rem] shadow-2xl relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-8 border-b-8 border-sipega-orange">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32"></div>
                <div class="flex items-center gap-6 relative z-10">
                    <div class="w-20 h-20 bg-white/10 rounded-3xl flex items-center justify-center text-4xl shrink-0">📄</div>
                    <div>
                        <h4 class="text-2xl font-black text-white italic uppercase mb-2">Dokumen Milik {{ $user->name }}</h4>
                        <p class="text-white/60 text-sm leading-relaxed font-medium">Halaman ini mengumpulkan seluruh dokumen bukti fisik Anda. Mulai dari Laporan Harian (SKP), Slip Tukin Bulanan, hingga Surat Tugas.</p>
                    </div>
                </div>
            </div>

            <!-- Three Columns Layout for Documents -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- COLUMN 1: SLIP TUKIN -->
                <div class="bg-white rounded-[2.5rem] shadow-xl overflow-hidden border border-gray-100 flex flex-col h-full">
                    <div class="p-8 border-b border-gray-50 bg-emerald-50/50 flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-500 rounded-2xl flex items-center justify-center text-white text-xl">💰</div>
                        <div>
                            <h3 class="text-lg font-black text-sipega-navy italic uppercase tracking-tighter">Slip Tukin</h3>
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Rekap Kinerja Bulanan</p>
                        </div>
                    </div>
                    <div class="p-8 flex-grow flex flex-col justify-center items-center text-center space-y-6">
                        <p class="text-gray-400 text-sm font-medium">Cetak slip/rekapitulasi tunjangan kinerja Anda untuk periode bulan berjalan sebagai bukti fisik perhitungan kinerja bulanan.</p>
                        <div class="w-full bg-gray-50 rounded-2xl p-4 mb-4">
                            <span class="text-xs font-black text-gray-500 uppercase">Periode:</span><br>
                            <span class="text-lg font-black text-emerald-600">{{ $currentMonth }}</span>
                        </div>
                        <a href="{{ route('tukin.download_slip') }}" class="w-full inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-8 rounded-full shadow-lg transition-all hover:-translate-y-1 uppercase text-[10px] tracking-widest text-center">
                            Unduh PDF Slip Tukin
                        </a>
                    </div>
                </div>

                <!-- COLUMN 2: LAPORAN HARIAN (SKP) -->
                <div class="bg-white rounded-[2.5rem] shadow-xl overflow-hidden border border-gray-100 flex flex-col h-full">
                    <div class="p-8 border-b border-gray-50 bg-orange-50/50 flex items-center gap-4">
                        <div class="w-12 h-12 bg-sipega-orange rounded-2xl flex items-center justify-center text-white text-xl">📝</div>
                        <div>
                            <h3 class="text-lg font-black text-sipega-navy italic uppercase tracking-tighter">Laporan Harian</h3>
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Bukti Fisik SKP</p>
                        </div>
                    </div>
                    <div class="p-8 flex-grow">
                        <form action="{{ route('evidence.download') }}" method="GET" class="space-y-6">
                            <p class="text-gray-400 text-sm font-medium">Pilih tanggal untuk mencetak Laporan Realisasi Harian Anda yang berisi detail pekerjaan dan foto bukti fisik.</p>
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block ml-2">Pilih Tanggal Laporan</label>
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full border-none bg-gray-50 rounded-2xl p-4 font-black text-sipega-navy text-sm focus:ring-sipega-orange transition-all" required>
                            </div>

                            <button type="submit" class="w-full inline-block bg-sipega-orange hover:bg-orange-600 text-white font-black py-4 px-8 rounded-full shadow-lg transition-all hover:-translate-y-1 uppercase text-[10px] tracking-widest text-center">
                                Cetak PDF Laporan
                            </button>
                        </form>
                        
                        <!-- Mini History list -->
                        @if($agendas->count() > 0)
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Laporan Terakhir:</h5>
                            <div class="space-y-2 max-h-32 overflow-y-auto pr-2">
                                @foreach($agendas->take(3) as $agenda)
                                <a href="{{ route('evidence.download', ['date' => $agenda->date]) }}" class="block p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs font-bold text-sipega-navy">{{ \Carbon\Carbon::parse($agenda->date)->translatedFormat('d M Y') }}</span>
                                        <span class="text-[9px] bg-white text-sipega-orange px-2 py-1 rounded uppercase font-black">Cetak</span>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- COLUMN 3: SURAT TUGAS -->
                <div class="bg-white rounded-[2.5rem] shadow-xl overflow-hidden border border-gray-100 flex flex-col h-full lg:col-span-3 xl:col-span-1">
                    <div class="p-8 border-b border-gray-50 bg-blue-50/50 flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-xl">✉️</div>
                        <div>
                            <h3 class="text-lg font-black text-sipega-navy italic uppercase tracking-tighter">Surat Tugas</h3>
                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Dokumen Penugasan</p>
                        </div>
                    </div>
                    <div class="p-8 flex-grow flex flex-col h-full">
                        <p class="text-gray-400 text-sm font-medium mb-6">Daftar Surat Tugas yang menugaskan Anda (SIPEGA-Assign).</p>
                        
                        @if($assignments->count() > 0)
                            <div class="space-y-3 overflow-y-auto flex-grow max-h-[300px] pr-2">
                                @foreach($assignments as $st)
                                    <div class="p-4 border border-gray-100 bg-gray-50 rounded-2xl hover:border-blue-200 transition-colors">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-[9px] font-black bg-blue-100 text-blue-800 px-2 py-1 rounded uppercase">{{ \Carbon\Carbon::parse($st->date)->translatedFormat('d M Y') }}</span>
                                        </div>
                                        <h5 class="text-sm font-bold text-sipega-navy leading-tight mb-1">{{ $st->title }}</h5>
                                        <p class="text-[10px] text-gray-400 mb-3 line-clamp-1">{{ $st->purpose ?? $st->description ?? '-' }}</p>
                                        <a href="{{ route('assign.pdf', $st->id) }}" class="inline-flex items-center gap-1 text-[10px] font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest">
                                            📄 Unduh PDF
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex-grow flex flex-col items-center justify-center text-center p-6 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                                <span class="text-3xl mb-2 text-gray-300">📭</span>
                                <p class="text-xs font-bold text-gray-400">Belum ada Surat Tugas</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
