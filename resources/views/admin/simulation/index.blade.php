<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-widest text-sipega-orange bg-orange-50 px-2.5 py-0.5 rounded-full border border-orange-200">
                        CONSOLE MANAJEMEN
                    </span>
                    <span class="text-xs text-gray-400 font-bold">&bull;</span>
                    <span class="text-xs text-gray-400 font-bold">Perencanaan & Distribusi Mandat</span>
                </div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight uppercase tracking-wider flex items-center gap-2.5">
                    <span>🎯 Simulasi Surat Tugas</span>
                </h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">
                    Pemeriksaan ketersediaan pegawai, deteksi bentrok dinas aktif, dan pemetaan penugasan adil berdasarkan jabatan dan gugus mutu.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('letters.create') }}" class="px-5 py-2.5 bg-sipega-navy hover:bg-black text-white text-xs font-black uppercase tracking-wider rounded-2xl transition shadow-lg flex items-center gap-2">
                    <span>📝</span> Buat Surat Tugas Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="simulationApp()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 1. KPI STATS SUMMARY -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Total Terfilter -->
                <div class="bg-white p-5 rounded-[2rem] border border-gray-100 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Pegawai Tersaring</span>
                        <span class="text-lg">👥</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-sipega-navy">{{ $totalPegawaiFiltered }}</span>
                        <span class="text-[11px] font-bold text-gray-400">Orang</span>
                    </div>
                    <div class="mt-2 text-[10px] font-bold text-gray-500">
                        Sesuai filter jabatan & gugus mutu
                    </div>
                </div>

                <!-- Siap Ditugaskan (Tersedia) -->
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-5 rounded-[2rem] text-white shadow-lg shadow-emerald-500/20 relative overflow-hidden">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-100">Siap Ditugaskan</span>
                        <span class="text-lg">🟢</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-white">{{ $totalAvailable }}</span>
                        <span class="text-[11px] font-bold text-emerald-100">Pegawai</span>
                    </div>
                    <div class="mt-2 text-[10px] font-bold text-emerald-100/90 flex items-center gap-1">
                        <span>✓</span> Bebas tugas di rentang tanggal ini
                    </div>
                </div>

                <!-- Sedang Bertugas (Bentrok) -->
                <div class="bg-white p-5 rounded-[2rem] border {{ $totalBusy > 0 ? 'border-red-200 bg-red-50/30' : 'border-gray-100' }} shadow-sm relative overflow-hidden">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black uppercase tracking-widest {{ $totalBusy > 0 ? 'text-red-600' : 'text-gray-400' }}">Sedang Bertugas</span>
                        <span class="text-lg">{{ $totalBusy > 0 ? '🔴' : '⚪' }}</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-black {{ $totalBusy > 0 ? 'text-red-600' : 'text-gray-700' }}">{{ $totalBusy }}</span>
                        <span class="text-[11px] font-bold text-gray-400">Pegawai</span>
                    </div>
                    <div class="mt-2 text-[10px] font-bold {{ $totalBusy > 0 ? 'text-red-700' : 'text-gray-400' }}">
                        {{ $totalBusy > 0 ? 'Memiliki jadwal ST dinas aktif' : 'Tidak ada bentrok jadwal' }}
                    </div>
                </div>

                <!-- Rasio Ketersediaan -->
                <div class="bg-white p-5 rounded-[2rem] border border-gray-100 shadow-sm relative overflow-hidden">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Rasio Ketersediaan</span>
                        <span class="text-lg">📊</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-black {{ $availabilityRate >= 70 ? 'text-emerald-600' : ($availabilityRate >= 40 ? 'text-amber-600' : 'text-red-600') }}">{{ $availabilityRate }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 h-1.5 rounded-full mt-3 overflow-hidden">
                        <div class="h-full {{ $availabilityRate >= 70 ? 'bg-emerald-500' : ($availabilityRate >= 40 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $availabilityRate }}%"></div>
                    </div>
                </div>
            </div>

            <!-- 2. FILTER CARD: TANGGAL, JABATAN, GUGUS MUTU, STATUS -->
            <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] border border-gray-100 shadow-xl space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-gray-100 pb-5">
                    <div>
                        <h3 class="text-lg font-black text-sipega-navy flex items-center gap-2">
                            <span>🔍 Parameter Simulasi & Filter Data</span>
                        </h3>
                        <p class="text-xs text-gray-400 font-bold mt-0.5">Tentukan rentang tanggal penugasan dan kriteria personil yang ingin diikutsertakan</p>
                    </div>

                    <!-- Presets Cepat Rentang Tanggal -->
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mr-1">Preset:</span>
                        <button type="button" @click="setPresetDates(0)" class="px-3 py-1 bg-gray-100 hover:bg-sipega-orange hover:text-white rounded-xl text-[10px] font-black transition">
                            1 Hari
                        </button>
                        <button type="button" @click="setPresetDates(2)" class="px-3 py-1 bg-gray-100 hover:bg-sipega-orange hover:text-white rounded-xl text-[10px] font-black transition">
                            3 Hari
                        </button>
                        <button type="button" @click="setPresetDates(4)" class="px-3 py-1 bg-gray-100 hover:bg-sipega-orange hover:text-white rounded-xl text-[10px] font-black transition">
                            5 Hari
                        </button>
                    </div>
                </div>

                <form method="GET" action="{{ route('duty-simulation.index') }}" id="simulationFilterForm" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Tanggal Mulai -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Tanggal Mulai <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="date_start" 
                                   id="inputDateStart"
                                   value="{{ $dateStart }}" 
                                   required 
                                   class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>

                        <!-- Tanggal Berakhir -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                Tanggal Berakhir <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="date_end" 
                                   id="inputDateEnd"
                                   value="{{ $dateEnd }}" 
                                   required 
                                   class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>

                        <!-- Filter Gugus Mutu -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span>🏛️</span> Filter Gugus Mutu
                            </label>
                            <select name="gugus_mutu" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                                <option value="all">-- Semua Gugus Mutu --</option>
                                @foreach($availableGugusMutu as $gm)
                                    <option value="{{ $gm }}" {{ $selectedGugus === $gm ? 'selected' : '' }}>{{ $gm }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Jabatan -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <span>💼</span> Filter Jabatan
                            </label>
                            <select name="jabatan" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                                <option value="all">-- Semua Jabatan --</option>
                                @foreach($availablePositions as $pos)
                                    <option value="{{ $pos }}" {{ $selectedJabatan === $pos ? 'selected' : '' }}>{{ $pos }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Status Ketersediaan & Pencarian -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                        <!-- Filter Ketersediaan -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Status Ketersediaan
                            </label>
                            <select name="status" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                                <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Pegawai (Tersedia & Sedang Dinas)</option>
                                <option value="available" {{ $selectedStatus === 'available' ? 'selected' : '' }}>🟢 Hanya Pegawai Bebas Tugas (Siap Ditugaskan)</option>
                                <option value="busy" {{ $selectedStatus === 'busy' ? 'selected' : '' }}>🔴 Hanya Pegawai Sedang Bertugas (Bentrok Jadwal)</option>
                            </select>
                        </div>

                        <!-- Pencarian Nama / NIP -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Cari Nama / NIP
                            </label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}" 
                                   placeholder="Ketik nama pegawai atau NIP..." 
                                   class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>

                        <!-- Tombol Submit & Reset -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 bg-sipega-orange hover:bg-orange-600 text-white font-black text-xs uppercase tracking-wider py-3.5 px-6 rounded-2xl transition shadow-lg shadow-orange-500/20 flex items-center justify-center gap-2">
                                <span>🎯</span> Jalankan Simulasi
                            </button>
                            <a href="{{ route('duty-simulation.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-500 font-black text-xs uppercase tracking-wider py-3.5 px-4 rounded-2xl transition" title="Reset Filter">
                                ↺ Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 3. TABEL SIMULASI & PEMETAAN PERSONIL -->
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-xl overflow-hidden">
                <div class="p-6 sm:p-8 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/40">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xl font-black text-sipega-navy">📋 Daftar Analisis Ketersediaan Personil</h3>
                            <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-black rounded-full">
                                {{ \Carbon\Carbon::parse($dateStart)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($dateEnd)->translatedFormat('d M Y') }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 font-bold mt-1">Centang pegawai untuk menyusun draft tim tugas dan langsung meneruskannya ke pembuatan Surat Tugas.</p>
                    </div>

                    <!-- Tombol Cepat Seleksi -->
                    <div class="flex items-center gap-2 self-stretch sm:self-auto">
                        <button type="button" @click="selectAllAvailable()" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-wider rounded-xl border border-emerald-200 transition">
                            ✓ Pilih Semua yang Bebas Tugas
                        </button>
                        <button type="button" @click="clearSelection()" x-show="selectedUsers.length > 0" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-black uppercase tracking-wider rounded-xl transition">
                            ✕ Lepas Semua
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-[10px] font-black uppercase tracking-wider text-gray-500 border-b border-gray-200">
                                <th class="py-4 px-4 w-12 text-center">Pilih</th>
                                <th class="py-4 px-6 min-w-[240px]">Pegawai</th>
                                <th class="py-4 px-5 min-w-[180px]">Jabatan & Golongan</th>
                                <th class="py-4 px-5 min-w-[160px]">Gugus Mutu</th>
                                <th class="py-4 px-5 text-center min-w-[150px]">Status Ketersediaan</th>
                                <th class="py-4 px-5 text-center min-w-[170px]" title="Riwayat Tugas Dinas Tahun {{ $year }} (DLK, DLP, DLN)">
                                    Mobilitas Dinas {{ $year }}
                                </th>
                                <th class="py-4 px-5 min-w-[200px]">Keterangan / Jadwal Dinas Aktif</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($simulationList as $item)
                                @php
                                    $u = $item['user'];
                                    $isBusy = $item['is_busy'];
                                @endphp
                                <tr class="transition-colors hover:bg-gray-50/70 {{ $isBusy ? 'bg-red-50/30' : '' }}"
                                    :class="selectedUsers.includes({{ $u->id }}) ? 'bg-orange-50/50' : ''">
                                    
                                    <!-- Checkbox Pilih -->
                                    <td class="py-4 px-4 text-center align-middle">
                                        <input type="checkbox" 
                                               :value="{{ $u->id }}" 
                                               x-model="selectedUsers" 
                                               class="rounded text-sipega-orange focus:ring-sipega-orange w-4 h-4 cursor-pointer">
                                    </td>

                                    <!-- Identitas Pegawai -->
                                    <td class="py-4 px-6 align-middle">
                                        <div class="flex items-center gap-3">
                                            @if($u->photo)
                                                <img src="{{ asset('storage/' . $u->photo) }}" class="w-10 h-10 object-cover rounded-2xl border-2 border-sipega-navy shadow-sm shrink-0">
                                            @else
                                                <div class="w-10 h-10 bg-sipega-navy flex items-center justify-center rounded-2xl text-white font-black text-sm shadow-sm shrink-0">
                                                    {{ substr($u->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-black text-sipega-navy text-sm leading-tight">{{ $u->name }}</div>
                                                <div class="text-[10px] text-gray-400 font-bold tracking-tight mt-0.5">NIP: {{ $u->nip ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Jabatan & Golongan -->
                                    <td class="py-4 px-5 align-middle">
                                        <div class="font-bold text-gray-800 text-xs leading-snug">
                                            {{ $u->position ?: 'Belum diatur' }}
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-1 text-[10px] text-gray-500 font-medium">
                                            <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-600 font-bold">Gol: {{ $u->golongan ?: '-' }}</span>
                                            @if($u->grade)
                                                <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-600 font-bold">KJ: {{ $u->grade }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Gugus Mutu -->
                                    <td class="py-4 px-5 align-middle">
                                        @if($u->gugus_mutu)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-800 border border-blue-200 rounded-xl text-[10px] font-black uppercase tracking-wider">
                                                <span>🏛️</span> {{ $u->gugus_mutu }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-gray-400 italic">Belum ditentukan</span>
                                        @endif
                                    </td>

                                    <!-- Status Ketersediaan -->
                                    <td class="py-4 px-5 text-center align-middle">
                                        @if($isBusy)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-100 text-red-800 rounded-full text-[10px] font-black uppercase tracking-wider border border-red-200">
                                                <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                                                Sedang Dinas
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-black uppercase tracking-wider border border-emerald-200">
                                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                                Siap Tugas
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Mobilitas Dinas Tahun Berjalan -->
                                    <td class="py-4 px-5 text-center align-middle">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold" title="DLK (Kantor / Lokal)">
                                                DLK: {{ $item['dlk_count'] }}
                                            </span>
                                            <span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded text-[10px] font-bold" title="DLP (Pusat)">
                                                DLP: {{ $item['dlp_count'] }}
                                            </span>
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold" title="DLN (Mitra)">
                                                DLN: {{ $item['dln_count'] }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-[10px] font-black text-gray-500">
                                            Total: <b class="text-sipega-navy">{{ $item['total_trips'] }}</b> Penugasan
                                        </div>
                                    </td>

                                    <!-- Keterangan / Jadwal Dinas yang Bertabrakan -->
                                    <td class="py-4 px-5 align-middle">
                                        @if($isBusy && count($item['conflicts']) > 0)
                                            <div class="space-y-1.5">
                                                @foreach($item['conflicts'] as $c)
                                                    <div class="p-2.5 bg-red-50/80 rounded-xl border border-red-200 text-[11px] text-red-900 leading-tight">
                                                        <div class="font-black flex items-center justify-between gap-1">
                                                            <span>ST No: {{ $c['letter_number'] }}</span>
                                                            <span class="text-[9px] bg-red-200 text-red-900 px-1.5 py-0.5 rounded font-bold">{{ $c['category'] }}</span>
                                                        </div>
                                                        <div class="font-medium text-gray-700 mt-0.5">{{ $c['title'] }}</div>
                                                        <div class="text-[10px] text-red-700 font-bold mt-1 flex items-center gap-1">
                                                            <span>📅</span> {{ $c['date_range'] }} &bull; 📍 {{ $c['location'] }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-emerald-700 text-[11px] font-bold flex items-center gap-1">
                                                <span>✓</span> Tidak ada jadwal tugas pada rentang tanggal ini.
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-16 text-center text-gray-400">
                                        <div class="text-4xl mb-3">🔍</div>
                                        <p class="text-sm font-extrabold text-sipega-navy uppercase tracking-wider">Tidak ada data pegawai yang sesuai dengan parameter simulasi.</p>
                                        <p class="text-xs text-gray-400 mt-1">Coba sesuaikan filter Jabatan, Gugus Mutu, atau kata kunci pencarian Anda.</p>
                                        <a href="{{ route('duty-simulation.index') }}" class="inline-block mt-4 px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[10px] font-black uppercase tracking-widest rounded-xl transition">
                                            Reset Parameter
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. FLOATING ACTION DOCK: TIM TUGAS SIMULASI TERPILIH -->
            <div x-show="selectedUsers.length > 0" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-8"
                 class="fixed bottom-6 inset-x-0 z-40 px-4 pointer-events-none">
                
                <div class="max-w-4xl mx-auto bg-sipega-navy text-white rounded-3xl p-4 sm:p-5 shadow-2xl border border-white/10 backdrop-blur-lg flex flex-col sm:flex-row items-center justify-between gap-4 pointer-events-auto">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-sipega-orange flex items-center justify-center text-xl font-black shadow-md">
                            🎯
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-black text-sm uppercase tracking-wide">Tim Tugas Terpilih:</span>
                                <span class="px-2.5 py-0.5 bg-sipega-orange text-white text-xs font-black rounded-full shadow-inner" x-text="selectedUsers.length + ' Orang'"></span>
                            </div>
                            <p class="text-[11px] text-gray-300 font-medium">
                                Tanggal: <b class="text-white">{{ \Carbon\Carbon::parse($dateStart)->translatedFormat('d M Y') }}</b> s.d <b class="text-white">{{ \Carbon\Carbon::parse($dateEnd)->translatedFormat('d M Y') }}</b>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="button" @click="clearSelection()" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white text-xs font-black uppercase tracking-wider rounded-2xl transition">
                            Batal
                        </button>
                        <button type="button" @click="proceedToLetterCreate()" class="flex-1 sm:flex-none px-6 py-2.5 bg-sipega-orange hover:bg-orange-600 text-white text-xs font-black uppercase tracking-wider rounded-2xl transition shadow-lg shadow-orange-500/30 flex items-center justify-center gap-2">
                            <span>🚀</span> Lanjutkan ke Buat Surat Tugas
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- SCRIPT CLIENT INTERACTION -->
    <script>
        // Data Pegawai yang bebas tugas
        const availableUserIds = @json($simulationList->where('is_available', true)->pluck('user.id')->values());

        function simulationApp() {
            return {
                selectedUsers: [],
                dateStart: '{{ $dateStart }}',
                dateEnd: '{{ $dateEnd }}',

                selectAllAvailable() {
                    this.selectedUsers = [...availableUserIds];
                },

                clearSelection() {
                    this.selectedUsers = [];
                },

                setPresetDates(daysToAdd) {
                    const startInput = document.getElementById('inputDateStart');
                    const endInput = document.getElementById('inputDateEnd');
                    if (!startInput.value) return;

                    const startDate = new Date(startInput.value);
                    const endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + daysToAdd);

                    const yyyy = endDate.getFullYear();
                    const mm = String(endDate.getMonth() + 1).padStart(2, '0');
                    const dd = String(endDate.getDate()).padStart(2, '0');
                    endInput.value = `${yyyy}-${mm}-${dd}`;

                    // Auto submit form to refresh simulation
                    document.getElementById('simulationFilterForm').submit();
                },

                proceedToLetterCreate() {
                    if (this.selectedUsers.length === 0) {
                        alert('Silakan pilih minimal 1 orang pegawai untuk membuat surat tugas.');
                        return;
                    }

                    // Susun URL ke route letters.create dengan query params
                    const params = new URLSearchParams();
                    params.append('date_start', this.dateStart);
                    params.append('date_end', this.dateEnd);
                    this.selectedUsers.forEach(id => {
                        params.append('users[]', id);
                    });

                    window.location.href = `{{ route('letters.create') }}?${params.toString()}`;
                }
            }
        }
    </script>
</x-app-layout>
