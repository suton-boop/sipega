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

        </div>
    </div>
</x-app-layout>
