<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight font-sans tracking-wide">
            {{ __('Halo Pegawai, ') }} <span class="text-sipega-orange">{{ $user->name }}</span> 👋
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Skor Warna Pribadi -->
            @php
                $colorCode = '#003366'; $bgClass='bg-blue-50'; $borderClass='border-blue-200';
                if ($user->performance_color == 'Merah') { $colorCode = '#dc2626'; $bgClass='bg-red-50'; $borderClass='border-red-200'; }
                elseif ($user->performance_color == 'Kuning') { $colorCode = '#eab308'; $bgClass='bg-yellow-50'; $borderClass='border-yellow-200'; }
                elseif ($user->performance_color == 'Hijau') { $colorCode = '#16a34a'; $bgClass='bg-green-50'; $borderClass='border-green-200'; }
            @endphp
            
            <div class="bg-white shadow-xl sm:rounded-3xl border-l-[12px] relative overflow-hidden transition-all hover:scale-[1.01]" style="border-color: {{ $colorCode }}">
                <div class="p-8 flex justify-between items-center z-10 relative">
                    <div>
                        <h3 class="text-gray-500 font-extrabold uppercase tracking-widest text-sm mb-1">Status Performa Anda</h3>
                        <p class="text-5xl font-black mt-1 text-gray-800">{{ number_format($user->performance_score, 1) }} <span class="text-xl font-medium text-gray-400">/ 100</span></p>
                    </div>
                    <div class="text-right">
                        <span class="text-3xl font-black uppercase tracking-widest" style="color: {{ $colorCode }}">{{ $user->performance_color }}</span>
                        <p class="text-xs mt-2 text-gray-400 font-bold bg-gray-100 px-3 py-1 rounded-full text-center inline-block">Update: 00:01 WITA</p>
                    </div>
                </div>
            </div>

            <!-- Agenda Harian -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl border-t-[10px] border-sipega-orange flex flex-col">
                <div class="p-8 flex-grow">
                    <h3 class="text-3xl font-extrabold mb-6 text-sipega-navy flex items-center gap-3">📝 Agenda Harian Anda</h3>
                    @if($myAgendaToday)
                        <div class="bg-green-50 text-green-800 p-6 rounded-2xl border-2 border-green-200 shadow-inner">
                            <strong class="text-xl flex items-center gap-2 mb-2">✔️ Telah Tertaut!</strong>
                            <p class="text-gray-700 leading-relaxed">{{ $myAgendaToday->activity_plan }}</p>
                            <p class="text-xs text-gray-400 mt-4 italic font-bold">Direkam pada: {{ \Carbon\Carbon::parse($myAgendaToday->created_at)->format('H:i') }} WITA</p>
                        </div>
                    @else
                        <div class="bg-red-50 text-red-800 p-4 rounded-2xl mb-6 border-l-4 border-red-500 shadow-sm flex items-start gap-4">
                            <div class="text-2xl mt-1">⚠️</div>
                            <div>
                                <h4 class="font-bold text-lg">Peringatan: Agenda Kosong!</h4>
                                <p class="text-sm mt-1">Anda belum menginput Rencana Kerja untuk hari ini. Batas waktu (*deadline*) normal adalah Pukul 17:00. Keterlambatan akan memotong Skor Warna Anda secara otomatis malam nanti.</p>
                            </div>
                        </div>
                        <form action="{{ route('agenda.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4 bg-gray-50 border border-gray-200 rounded-3xl p-6 shadow-inner">
                            @csrf
                            @if(session('error'))
                                <div class="bg-red-100 text-red-700 p-3 rounded-lg text-sm font-bold">{{ session('error') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="bg-red-100 text-red-700 p-3 rounded-lg text-sm font-bold">Terjadi kesalahan input data Anda. Pastikan ada tulisan (min 5 kata) dan foto.</div>
                            @endif

                            <label class="font-bold text-sipega-navy text-lg block mb-1">Rincian Rencana Kegiatan</label>
                            <textarea name="activity_plan" required minlength="5" class="w-full border border-gray-300 focus:border-sipega-orange focus:ring-sipega-orange rounded-2xl shadow-sm p-4 h-32 text-gray-700" placeholder="Ketikkan apa yang akan Anda kerjakan di BPMP hari ini..."></textarea>
                            
                            <label class="font-bold text-sipega-navy text-lg block mt-2 mb-1">Unggah Bukti Dokumen/Foto</label>
                            <input type="file" name="proof_file" accept=".jpg,.png,.jpeg" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-orange-50 file:text-sipega-orange hover:file:bg-orange-100 transition-all cursor-pointer"/>

                            <button type="submit" class="bg-sipega-orange hover:bg-[#E67E22] text-white font-extrabold py-4 px-8 rounded-full shadow-xl transition-all hover:scale-105 hover:shadow-2xl mt-4 flex items-center justify-center gap-2 text-lg">
                                🚀 SUBMIT AGENDA
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- SIPEGA Drive & Reward Panel -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- SIPEGA-Drive -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white overflow-hidden shadow-xl sm:rounded-3xl p-8 relative hover:-translate-y-1 transition-transform">
                    <h3 class="text-2xl font-extrabold mb-2 flex items-center gap-2">☁️ SIPEGA-Drive</h3>
                    <p class="text-blue-100 mb-6 text-sm">Akses langsung ke loker arsip cloud Anda (G-Drive) tanpa batasan ruang penyimpanan.</p>
                    
                    @if($user->drive_folder_url)
                        <a href="{{ $user->drive_folder_url }}" target="_blank" class="bg-white text-blue-700 font-bold py-3 px-6 rounded-full shadow-lg hover:shadow-xl hover:bg-gray-50 transition w-full block text-center mt-auto">
                            Buka Folder Pribadi ↗
                        </a>
                    @else
                        <button disabled class="bg-white/20 text-white/50 font-bold py-3 px-6 rounded-full w-full block text-center cursor-not-allowed border border-white/20 mt-auto">
                            Menunggu Konfigurasi Admin ⏳
                        </button>
                    @endif
                </div>

                <!-- SIPEGA-Reward -->
                <div class="bg-white border-t-[8px] border-yellow-400 overflow-hidden shadow-xl sm:rounded-3xl p-8 hover:-translate-y-1 transition-transform relative">
                    <h3 class="text-2xl font-extrabold mb-2 text-gray-800 flex items-center gap-2">🎁 SIPEGA-Reward</h3>
                    <p class="text-gray-500 mb-6 text-sm">Berpartisipasi dalam Voting Teladan (Pemilihan Rekan Kerja Terbaik Semester Ini).</p>
                    <button class="bg-yellow-400 text-yellow-900 font-bold py-3 px-6 rounded-full shadow hover:bg-yellow-500 transition w-full mt-auto block text-center">
                        Ikuti Voting Sekarang 🗳️
                    </button>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
