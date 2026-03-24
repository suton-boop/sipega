<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight">
            {{ __('Administrator Dashboard - SIPEGA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border-t-8 border-sipega-navy">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-4">🚀 Parameter Validasi Data Absen</h3>
                    <p class="mb-4 text-lg">Total Pegawai Saat Ini: <span class="font-bold text-sipega-orange text-2xl">{{ $totalUsers }}</span> Orang</p>
                    <div class="p-6 bg-gray-50 rounded-2xl border border-gray-200">
                        <p class="text-gray-600 mb-4">Lakukan sinkronisasi atau pemantauan persetujuan lupa absen harian untuk memastikan *Engine Performa* menghitung secara adil.</p>
                        <button class="bg-sipega-orange hover:bg-orange-600 text-white font-extrabold py-3 px-8 rounded-full shadow-xl transition-all hover:-translate-y-1 hover:shadow-2xl">
                            Validasi Cepat Lupa Absen ({{ $today }}) ➡️
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Top 5 Highest -->
                <div class="bg-white overflow-hidden shadow-md sm:rounded-3xl p-6">
                    <h3 class="text-xl font-bold mb-4 text-green-700">🏆 Top 5 Skor (Tertinggi)</h3>
                    <ul class="divide-y divide-gray-100">
                        @foreach($top5Highest as $user)
                        <li class="py-3 flex justify-between items-center group hover:bg-gray-50 px-2 rounded-xl transition">
                            <span class="font-medium">{{ $user->name }}</span>
                            <span class="bg-sipega-navy text-white font-bold py-1 px-4 rounded-full shadow-sm">{{ $user->performance_score }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <!-- Top 5 Lowest -->
                <div class="bg-white overflow-hidden shadow-md sm:rounded-3xl p-6">
                    <h3 class="text-xl font-bold mb-4 text-red-600">⚠️ Top 5 Penanganan (Terendah)</h3>
                    <ul class="divide-y divide-gray-100">
                        @foreach($top5Lowest as $user)
                        <li class="py-3 flex justify-between items-center group hover:bg-gray-50 px-2 rounded-xl transition">
                            <span class="font-medium text-gray-700">{{ $user->name }}</span>
                            <span class="bg-red-100 text-red-700 font-bold py-1 px-4 rounded-full border border-red-200">{{ $user->performance_score }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- SIPEGA-Assign Modul -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl border-t-[10px] border-sipega-navy mt-8">
                <div class="p-8">
                    <h3 class="text-3xl font-extrabold mb-2 text-sipega-navy flex items-center gap-2">📄 SIPEGA-Assign: Terbitkan Surat Tugas</h3>
                    <p class="text-gray-500 mb-6">Filter pintar akan otomatis menolak pegawai yang jadwalnya bentrok (Anti-Double Tugas).</p>
                    
                    @if(session('success'))
                        <div class="bg-green-100 text-green-800 p-4 rounded-xl mb-4 font-bold border border-green-300">✔️ {{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="bg-red-100 text-red-800 p-4 rounded-xl mb-4 font-bold border border-red-300">❌ {{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('assign.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-3xl border border-gray-200">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="font-bold text-gray-700 block mb-1">Judul / Keperluan Dinas</label>
                                <input type="text" name="title" required class="w-full rounded-xl border-gray-300 focus:ring-sipega-navy focus:border-sipega-navy" placeholder="Contoh: Rapat Koordinasi Dapodik...">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="font-bold text-gray-700 block mb-1">Tanggal Mulai</label>
                                    <input type="date" name="date" required class="w-full rounded-xl border-gray-300 focus:ring-sipega-navy focus:border-sipega-navy">
                                </div>
                                <div>
                                    <label class="font-bold text-gray-700 block mb-1">Tipe Surat</label>
                                    <select name="type" class="w-full rounded-xl border-gray-300 focus:ring-sipega-navy focus:border-sipega-navy">
                                        <option value="Individu">Perorangan</option>
                                        <option value="Kolektif">Kolektif (Group)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-red-50 p-4 rounded-xl border border-red-100">
                                <input type="hidden" name="is_private" value="0">
                                <input type="checkbox" name="is_private" value="1" class="rounded text-red-600 focus:ring-red-500 w-5 h-5">
                                <label class="font-bold text-red-800">Ubah Tipe Menjadi "Dinas Khusus" (Tertutup) 🔒</label>
                            </div>
                        </div>

                        <div class="space-y-4 flex flex-col">
                            <div class="flex-grow">
                                <label class="font-bold text-gray-700 block mb-2">Pilih Personil Pegawai (Multi-select)</label>
                                <div class="bg-white border border-gray-300 rounded-xl p-4 h-48 overflow-y-auto shadow-inner space-y-2">
                                    @foreach($allPegawai as $p)
                                        <label class="flex items-center gap-3 p-2 hover:bg-blue-50 rounded-lg transition cursor-pointer">
                                            <input type="checkbox" name="assigned_users[]" value="{{ $p->id }}" class="rounded border-gray-300 text-sipega-navy focus:ring-sipega-navy w-5 h-5">
                                            <span class="font-semibold text-gray-800">{{ $p->name }}</span>
                                            <span class="ml-auto text-xs font-bold text-{{ strtolower($p->performance_color) }}-700 bg-{{ strtolower($p->performance_color) }}-100 px-2 py-1 rounded-full">{{ $p->performance_color }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <button type="submit" class="bg-sipega-navy hover:bg-[#002244] text-white font-extrabold py-4 px-8 rounded-full shadow-xl transition-all hover:-translate-y-1 hover:shadow-2xl flex items-center justify-center gap-2">
                                📜 TERBITKAN SURAT TUGAS
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
