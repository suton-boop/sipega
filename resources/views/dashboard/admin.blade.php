<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center whitespace-nowrap">
            <h2 class="font-semibold text-xl text-sipega-navy leading-tight">
                {{ auth()->user()->name }} - Administrator Center
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('rbac.index') }}" class="bg-sipega-navy hover:bg-[#002244] text-white font-bold py-2 px-4 rounded-full shadow-md text-sm transition hover:-translate-y-0.5 whitespace-nowrap overflow-hidden border border-orange-400">
                    🔐 MATRIKS HAK AKSES
                </a>
                <a href="{{ route('users.index') }}" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded-full shadow-md text-sm transition hover:-translate-y-0.5 whitespace-nowrap overflow-hidden">
                    👥 KELOLA PEGAWAI
                </a>
            </div>
        </div>
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

            <!-- SIPEGA-Check Modul -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl border-t-[10px] border-green-600 mt-8">
                <div class="p-8">
                    <h3 class="text-3xl font-extrabold mb-2 text-green-700 flex items-center gap-2">✅ SIPEGA-Check: Impor Excel Mesin Absen</h3>
                    <p class="text-gray-500 mb-6">Engine akan melebur data Excel dengan database ST. <span class="text-green-600 font-bold">Anti-Tertindih DL Aktif!</span></p>

                    @if(session('success_import'))
                        <div class="bg-green-100 text-green-800 p-4 rounded-xl mb-4 font-bold border border-green-300">✔️ {{ session('success_import') }}</div>
                    @endif

                    <form action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data" class="bg-green-50 p-6 rounded-3xl border border-green-200 flex flex-col md:flex-row items-center gap-6">
                        @csrf
                        <div class="w-full md:flex-grow">
                            <label class="font-bold text-green-800 block mb-2">Pilih File .xlsx / .csv</label>
                            <input type="file" name="excel_file" accept=".xlsx,.csv,.xls" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-green-600 file:text-white hover:file:bg-green-700 transition-all cursor-pointer"/>
                            <p class="text-xs text-green-700 mt-2">Pastikan kolom memuat format: <i>email, tanggal, status, tl_menit, psw_menit</i>.</p>
                        </div>
                        <div class="w-full md:w-auto">
                            <button type="submit" class="w-full md:w-auto bg-green-600 hover:bg-green-700 text-white font-extrabold py-4 px-8 rounded-full shadow-lg transition-all hover:scale-105 flex items-center justify-center gap-2">
                                🔄 SINKRONISASI DATA
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent Assignment Letters Modul -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl border-t-[10px] border-orange-500 mt-8">
                <div class="p-8">
                    <h3 class="text-3xl font-extrabold mb-2 text-orange-600 flex items-center gap-2">📜 Arsip Surat Tugas SIPEGA</h3>
                    <p class="text-gray-500 mb-6">Daftar publikasi surat tugas terbaru. Silakan unduh PDF resmi.</p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="py-4 px-4 font-bold text-gray-700">Nomor / Judul</th>
                                    <th class="py-4 px-4 font-bold text-gray-700">Tanggal</th>
                                    <th class="py-4 px-4 font-bold text-gray-700">Peserta</th>
                                    <th class="py-4 px-4 font-bold text-gray-700 text-center">Format</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @php
                                    $recentLetters = \App\Models\AssignmentLetter::with('users')->latest()->take(5)->get();
                                @endphp
                                @foreach($recentLetters as $rl)
                                <tr class="hover:bg-orange-50/30 transition">
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-sipega-navy">{{ $rl->letter_number }}</div>
                                        <div class="text-sm text-gray-400 capitalize">{{ $rl->title }}</div>
                                    </td>
                                    <td class="py-4 px-4 text-sm text-gray-600">
                                        {{ $rl->date->format('d/m/Y') }}
                                        @if($rl->is_private)
                                            <span class="ml-2 bg-red-100 text-red-600 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Private</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex -space-x-2">
                                            @foreach($rl->users->take(3) as $u)
                                                <div class="w-8 h-8 rounded-full bg-{{ strtolower($u->performance_color) }}-500 border-2 border-white flex items-center justify-center text-[10px] text-white font-bold" title="{{ $u->name }}">
                                                    {{ substr($u->name, 0, 1) }}
                                                </div>
                                            @endforeach
                                            @if($rl->users->count() > 3)
                                                <div class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-[10px] text-gray-600 font-bold">
                                                    +{{ $rl->users->count() - 3 }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <a href="{{ route('assign.pdf', $rl->id) }}" class="inline-flex items-center gap-2 bg-sipega-navy text-white text-xs font-bold py-2 px-4 rounded-full hover:bg-[#002244] transition shadow-md">
                                            📥 DOWNLOAD PDF
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
