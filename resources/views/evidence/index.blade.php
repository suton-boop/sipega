<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Pusat Bukti Fisik 📁
                </h2>
                <p class="text-[10px] font-black text-sipega-orange uppercase tracking-[0.3em] mt-1">SIPEGA SKP-READY &bull; REKAPITULASI DOKUMENTASI</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('evidence.download', ['date' => $date]) }}" class="bg-red-600 hover:bg-black text-white font-black py-3 px-8 rounded-2xl shadow-xl transition-all hover:-translate-y-1 flex items-center gap-2 uppercase text-[10px] tracking-widest">
                    <span>🖨️</span> Cetak Laporan Realisasi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Filter & Stats -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Date Filter -->
                <div class="lg:col-span-1 bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col justify-center">
                    <label class="text-[9px] font-black text-gray-400 uppercase mb-2 tracking-widest">Pilih Tanggal:</label>
                    <form action="{{ route('evidence.index') }}" method="GET">
                        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="w-full border-none bg-gray-50 rounded-2xl p-4 font-black text-sipega-navy text-sm focus:ring-sipega-orange transition-all">
                    </form>
                </div>

                <!-- Stats Cards -->
                <div class="lg:col-span-3 grid grid-cols-3 gap-4">
                    <div class="bg-sipega-navy p-6 rounded-[2.5rem] shadow-xl relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 text-white/5 text-6xl group-hover:scale-110 transition-transform">📋</div>
                        <p class="text-[9px] font-black text-white/50 uppercase tracking-widest mb-1">Total Kegiatan</p>
                        <p class="text-3xl font-black text-white">{{ $stats['total'] }}</p>
                    </div>
                    <div class="bg-emerald-500 p-6 rounded-[2.5rem] shadow-xl relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 text-white/5 text-6xl group-hover:scale-110 transition-transform">✅</div>
                        <p class="text-[9px] font-black text-white/50 uppercase tracking-widest mb-1">Terealisasi</p>
                        <p class="text-3xl font-black text-white">{{ $stats['completed'] }}</p>
                    </div>
                    <div class="bg-sipega-orange p-6 rounded-[2.5rem] shadow-xl relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 text-white/5 text-6xl group-hover:scale-110 transition-transform">⏳</div>
                        <p class="text-[9px] font-black text-white/50 uppercase tracking-widest mb-1">Pending</p>
                        <p class="text-3xl font-black text-white">{{ $stats['total'] - $stats['completed'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Evidence List -->
            <div class="bg-white rounded-[3rem] shadow-2xl overflow-hidden border border-gray-100">
                <div class="p-10 border-b border-gray-50 bg-gray-50/30">
                    <h3 class="text-xl font-black text-sipega-navy italic uppercase tracking-tighter">📦 Daftar Kegiatan Harian</h3>
                    <p class="text-xs text-gray-400 font-bold">Unggah bukti fisik berupa narasi dan foto untuk setiap kegiatan yang sudah direncanakan.</p>
                </div>

                <div class="divide-y divide-gray-100">
                    @if($agenda && $agenda->items->count() > 0)
                        @foreach($agenda->items as $item)
                            <div class="p-10 hover:bg-gray-50/50 transition-colors group">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                                    <div class="lg:col-span-4">
                                        <div class="flex items-center gap-3 mb-4">
                                            <span class="w-8 h-8 rounded-full bg-sipega-navy text-white flex items-center justify-center text-[10px] font-black">{{ $loop->iteration }}</span>
                                            <span class="text-[10px] px-3 py-1 rounded-full font-black uppercase tracking-widest 
                                                {{ $item->status == 'completed' ? 'bg-emerald-100 text-emerald-700' : ($item->status == 'progress' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-400') }}">
                                                {{ $item->status }}
                                            </span>
                                        </div>
                                        <h4 class="text-lg font-black text-sipega-navy leading-tight mb-4 group-hover:text-sipega-orange transition-colors">{{ $item->plan_description }}</h4>
                                        
                                        @if($item->proof_file_path)
                                            <div class="relative w-full aspect-video rounded-3xl overflow-hidden shadow-lg mb-4 border-2 border-white">
                                                <img src="{{ asset('storage/' . $item->proof_file_path) }}" class="w-full h-full object-cover">
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                    <a href="{{ asset('storage/' . $item->proof_file_path) }}" target="_blank" class="bg-white text-sipega-navy font-black text-[9px] px-4 py-2 rounded-full uppercase scale-90 group-hover:scale-100 transition-transform">🔍 Lihat Full</a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="w-full aspect-video rounded-3xl bg-gray-100 border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-300">
                                                <span class="text-4xl mb-2">📸</span>
                                                <p class="text-[10px] font-black uppercase tracking-widest">Belum Ada Foto</p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="lg:col-span-8">
                                        <form action="{{ route('evidence.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                                            @csrf
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                <div class="space-y-2">
                                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block ml-2">Siklus Tahapan</label>
                                                    <select name="workflow_phase" class="w-full border-none bg-orange-50/50 rounded-2xl p-4 font-black text-xs text-sipega-orange focus:ring-sipega-orange">
                                                        @foreach(['Tujuan', 'Rencana', 'Prioritas', 'Kerja', 'Pantau', 'Evaluasi', 'Perbaiki'] as $phase)
                                                            <option value="{{ $phase }}" {{ $item->workflow_phase == $phase ? 'selected' : '' }}>{{ $loop->iteration }}. {{ strtoupper($phase) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="space-y-2">
                                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block ml-2">Pembaruan Status</label>
                                                    <select name="status" class="w-full border-none bg-gray-50 rounded-2xl p-4 font-bold text-xs focus:ring-sipega-orange uppercase">
                                                        <option value="completed" {{ $item->status == 'completed' ? 'selected' : '' }}>✅ SELESAI</option>
                                                        <option value="progress" {{ $item->status == 'progress' ? 'selected' : '' }}>⏳ PROSES</option>
                                                        <option value="changed" {{ $item->status == 'changed' ? 'selected' : '' }}>⚠️ GANTI</option>
                                                    </select>
                                                </div>
                                                <div class="space-y-2">
                                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block ml-2">Bukti Foto</label>
                                                    <input type="file" name="proof_file" class="block w-full text-[9px] text-gray-400 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:text-[9px] file:font-black file:bg-sipega-navy file:text-white file:uppercase file:tracking-widest cursor-pointer bg-gray-50 rounded-2xl p-1.5">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div class="space-y-2">
                                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block ml-2">Narasi Bukti Fisik / SKP</label>
                                                    <textarea name="proof_text" rows="3" class="w-full border-none bg-gray-50 rounded-3xl p-6 font-medium text-sm focus:ring-sipega-orange placeholder:text-gray-300" placeholder="Tuliskan detail pekerjaan...">{{ $item->proof_text }}</textarea>
                                                </div>
                                                <div class="space-y-2">
                                                    <label class="text-[10px] font-black text-sipega-orange uppercase tracking-widest block ml-2">Evaluasi & Rencana Perbaikan</label>
                                                    <textarea name="evaluation_notes" rows="3" class="w-full border-none bg-orange-50/30 rounded-3xl p-6 font-medium text-sm focus:ring-sipega-orange placeholder:text-gray-300" placeholder="Evaluasi hasil & cara memperbaiki ke depan...">{{ $item->evaluation_notes }}</textarea>
                                                </div>
                                            </div>

                                            <div class="flex justify-end">
                                                <button type="submit" class="bg-sipega-navy hover:bg-black text-white font-black py-4 px-10 rounded-[2rem] shadow-xl transition-all hover:-translate-y-1 uppercase text-[10px] tracking-widest">
                                                    Update Bukti Fisik 💾
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="p-20 text-center flex flex-col items-center">
                            <span class="text-7xl mb-6">🏜️</span>
                            <h5 class="text-xl font-black text-sipega-navy italic uppercase">Data Kosong</h5>
                            <p class="text-gray-400 font-medium mb-8">Anda belum menyusun rencana kegiatan untuk tanggal ini.</p>
                            <a href="{{ route('agenda.index') }}" class="bg-sipega-orange hover:bg-orange-600 text-white font-black py-4 px-10 rounded-full shadow-lg transition-all uppercase text-[10px] tracking-widest">
                                Susun Agenda Pagi
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Supporting Info -->
            <div class="bg-sipega-navy p-10 rounded-[3rem] shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center gap-8 border-b-8 border-sipega-orange">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32"></div>
                <div class="w-20 h-20 bg-white/10 rounded-3xl flex items-center justify-center text-4xl shrink-0">💡</div>
                <div>
                    <h4 class="text-xl font-black text-white italic uppercase mb-2">Tips Bukti Fisik SIPEGA</h4>
                    <p class="text-white/60 text-sm leading-relaxed font-medium">Laporan realisasi ini diekspor dalam format PDF yang siap dilampirkan ke sistem **E-KINERJA / SKP**. Pastikan foto yang diunggah menunjukkan objek pekerjaan atau bukti dokumentasi rapat yang relevan.</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
