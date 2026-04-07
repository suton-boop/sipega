<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight font-sans tracking-wide">
            {{ __('SK & Surat Tugas') }} 📑
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-2xl relative mb-4 font-bold text-sm shadow-sm" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-2xl relative mb-4 font-bold text-sm shadow-sm" role="alert">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Pengajuan ST Section -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl border-t-[10px] border-sipega-navy p-8">
                <div class="flex flex-col md:flex-row gap-8 items-center mb-10">
                    <div class="md:w-1/3">
                        <h3 class="text-3xl font-extrabold mb-2 text-sipega-navy flex items-center gap-2">📂 Kelola LHDL</h3>
                        <p class="text-gray-500 text-sm italic">Laporan Hasil Dinas Luar (LHDL) wajib diunggah maksimal <span class="font-bold text-red-600">3x24 jam</span>.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse($myAssignments as $st)
                    <div class="p-6 bg-gray-50 border border-gray-100 rounded-[2rem] flex flex-col md:flex-row justify-between items-center group hover:bg-orange-50 transition-colors gap-6 shadow-sm">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-16 rounded-3xl bg-sipega-navy flex items-center justify-center text-white text-3xl shadow-lg">📜</div>
                            <div>
                                <h4 class="font-black text-sipega-navy text-xl">{{ \Illuminate\Support\Str::limit($st->title, 40) }}</h4>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">{{ \Carbon\Carbon::parse($st->date)->format('d M Y') }} • No: {{ $st->letter_number }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('assign.pdf', $st->id) }}" class="bg-white border-2 border-blue-600 text-blue-600 text-xs px-6 py-2.5 rounded-full font-black hover:bg-blue-600 hover:text-white transition shadow-sm">Cetak PDF 🖨️</a>
                            @if($st->report_path)
                                <span class="bg-green-100 text-green-700 text-xs px-6 py-2.5 rounded-full font-black italic tracking-wider">Tuntas ✅</span>
                            @else
                                <button class="bg-sipega-orange text-white text-xs px-6 py-2.5 rounded-full font-black hover:bg-orange-600 transition shadow-lg transform active:scale-95">Unggah LHDL 📤</button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-100">
                        <p class="text-gray-400 italic text-sm font-medium">Anda belum memiliki riwayat penugasan.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- New ST Form (Self-Service) -->
            <div class="bg-gradient-to-r from-sipega-navy to-blue-900 overflow-hidden shadow-2xl sm:rounded-3xl p-10 text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <h3 class="text-3xl font-black mb-4 flex items-center gap-3">✨ Pengajuan Mandiri</h3>
                    <p class="text-blue-100 mb-8 max-w-lg">Butuh Surat Tugas mendadak untuk kegiatan internal? Gunakan sistem penerbitan mandiri SIPEGA.</p>
                    <button class="bg-sipega-orange hover:bg-white hover:text-sipega-orange text-white font-black py-4 px-10 rounded-full shadow-2xl transition-all transform hover:scale-105 active:scale-95 text-lg uppercase tracking-tight">
                        Buat Pengajuan Baru ✍️
                    </button>
                </div>
                <div class="absolute -right-20 -bottom-20 opacity-10 text-9xl transform rotate-12 group-hover:scale-110 transition-transform">📄</div>
            </div>

        </div>
    </div>
</x-app-layout>
