<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight">
            {{ __('Dashboard Pimpinan SIPEGA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Wall of Fame -->
            <div class="bg-gradient-to-r from-sipega-navy to-[#002244] overflow-hidden shadow-2xl sm:rounded-3xl text-white relative">
                <img src="{{ asset('images/logo Sipega.png') }}" class="absolute -right-8 top-0 h-full opacity-10" />
                <div class="p-6 relative z-10">
                    <h3 class="text-3xl font-extrabold flex items-center gap-3">⭐ SIPEGA Wall of Fame</h3>
                    <p class="mt-1 text-gray-300 font-medium">Bintang Kinerja: 5 Personel Tertinggi Semester Ini</p>
                </div>
                <div class="bg-white text-gray-900 p-8 rounded-b-3xl z-10 relative">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                        @foreach($top5Highest as $index => $user)
                        <div class="bg-gray-50 p-6 text-center rounded-2xl shadow border border-gray-100 hover:-translate-y-2 hover:shadow-xl transition-all relative">
                            @if($index == 0)
                                <div class="absolute -top-4 -right-4 bg-sipega-orange text-white p-2 rounded-full shadow-lg">🥇</div>
                            @endif
                            <h4 class="text-4xl mb-3 font-extrabold {{ $index == 0 ? 'text-sipega-orange' : 'text-sipega-navy' }}">#{{ $index+1 }}</h4>
                            <p class="font-bold text-lg truncate">{{ $user->name }}</p>
                            <span class="bg-green-100 text-green-800 font-bold px-4 py-2 rounded-full text-md mt-4 inline-block shadow-sm">Score: {{ $user->performance_score }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Private Assignments -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border-t-8 border-sipega-orange">
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-4 text-gray-800">🕵️ Monitoring Dinas Khusus (Privat)</h3>
                    <div class="grid gap-4">
                        @foreach($privateAssignments as $pa)
                            <div class="bg-gray-50 p-5 rounded-2xl flex flex-col md:flex-row md:justify-between md:items-center border border-gray-200">
                                <div>
                                    <span class="font-extrabold block text-xl text-sipega-navy">{{ $pa->title }}</span>
                                    <span class="text-sm font-semibold text-gray-500 uppercase tracking-widest mt-1 block">Surat: {{ $pa->letter_number }} • {{ \Carbon\Carbon::parse($pa->date)->translatedFormat('d F Y') }}</span>
                                </div>
                                <span class="bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-xl shadow-sm text-sm font-bold mt-4 md:mt-0 flex items-center gap-2">
                                    🔒 Akses Tertutup
                                </span>
                            </div>
                        @endforeach
                        @if($privateAssignments->isEmpty())
                            <div class="text-center py-8 bg-gray-50 rounded-2xl border border-dashed border-gray-300">
                                <p class="text-gray-500 italic text-lg">Halaman bersih. Belum ada perintah dinas khusus yang diterbitkan.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
