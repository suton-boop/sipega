<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-100 text-sipega-orange border border-orange-200">
                        Console Manajemen
                    </span>
                    <span class="text-xs text-gray-400 font-bold">•</span>
                    <span class="text-xs text-gray-500 font-semibold">Absensi & Kedisiplinan</span>
                </div>
                <h2 class="text-2xl font-black text-sipega-navy mt-1 tracking-tight">
                    Kelola Kehadiran & Keterangan Pegawai
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Verifikasi, revisi data absensi harian, dan berikan keterangan status (Dinas Luar, Cuti, Sakit, Izin, ALPA).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('attendance.import.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-2xl shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import Log Mesin
                </a>
                <a href="{{ route('performance.recap') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-sipega-navy text-xs font-bold rounded-2xl shadow-sm transition-all">
                    <svg class="w-4 h-4 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Raport Pegawai
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen" x-data="{
        editModal: false,
        form: {
            user_id: '{{ $selectedUser?->id ?? '' }}',
            user_name: '{{ $selectedUser?->name ?? '' }}',
            date: '',
            formatted_date: '',
            status: 'Hadir',
            notes: '',
            check_in: '',
            check_out: '',
            tl_minutes: 0,
            psw_minutes: 0
        },
        openEdit(row) {
            this.form.date = row.date;
            this.form.formatted_date = row.formatted_date + ' (' + row.day_name + ')';
            this.form.status = (row.status === 'Libur' || row.status === 'Belum Ada Log') ? 'Hadir' : row.status;
            this.form.notes = row.notes || '';
            this.form.check_in = row.check_in || '';
            this.form.check_out = row.check_out || '';
            this.form.tl_minutes = row.tl_minutes || 0;
            this.form.psw_minutes = row.psw_minutes || 0;
            this.editModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Notification -->
            @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-sm font-semibold shadow-sm animate-fade-in">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800 text-sm font-semibold shadow-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- FILTER CARD: BULAN & PEGAWAI -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('admin.attendance.manage') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    
                    <!-- Filter Bulan -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-black uppercase tracking-wider text-sipega-navy mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            1. Pilih Periode Bulan
                        </label>
                        <input type="month" name="month" value="{{ $monthYear }}" class="w-full text-sm font-bold rounded-2xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange transition-all py-2.5 px-4 shadow-sm">
                    </div>

                    <!-- Filter Nama Pegawai -->
                    <div class="md:col-span-6">
                        <label class="block text-xs font-black uppercase tracking-wider text-sipega-navy mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            2. Pilih Nama Pegawai
                        </label>
                        <select name="user_id" class="w-full text-sm font-bold rounded-2xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange transition-all py-2.5 px-4 shadow-sm">
                            <option value="">-- Pilih Pegawai untuk Menampilkan Data --</option>
                            @foreach($allPegawai as $p)
                                <option value="{{ $p->id }}" {{ ($selectedUser && $selectedUser->id == $p->id) ? 'selected' : '' }}>
                                    {{ $p->name }} {{ $p->nip ? '('.$p->nip.')' : '' }} - {{ $p->position ?? 'Pegawai' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tombol Submit Filter -->
                    <div class="md:col-span-2">
                        <button type="submit" class="w-full py-2.5 px-4 bg-sipega-navy hover:bg-blue-900 text-white font-bold text-xs uppercase tracking-wider rounded-2xl shadow-sm transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>

            <!-- JIKA BELUM MEMILIH PEGAWAI: EMPTY STATE PANDUAN -->
            @if(!$selectedUser)
            <div class="bg-white rounded-[2.5rem] p-12 text-center border border-gray-100 shadow-sm">
                <div class="w-20 h-20 bg-orange-50 text-sipega-orange rounded-3xl mx-auto flex items-center justify-center mb-5 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-lg font-black text-sipega-navy">Pilih Pegawai dan Periode Bulan di Atas</h3>
                <p class="text-xs text-gray-500 max-w-md mx-auto mt-2 leading-relaxed">
                    Silakan tentukan bulan dan pilih nama pegawai untuk menampilkan tabel kehadiran harian, jam finger, serta memberi atau mengedit keterangan dinas, cuti, sakit, dan izin.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span> Total {{ $allPegawai->count() }} Pegawai Terdaftar
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span> Periode: {{ \Carbon\Carbon::parse($monthYear.'-01')->translatedFormat('F Y') }}
                    </span>
                </div>
            </div>
            @else

            <!-- PROFIL PEGAWAI TERPILIH & STATISTIK BULANAN -->
            <div class="bg-gradient-to-r from-sipega-navy via-slate-800 to-sipega-navy rounded-[2.5rem] p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-sipega-orange font-black text-2xl shadow-inner">
                            {{ strtoupper(substr($selectedUser->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-sipega-orange text-white">
                                    {{ $selectedUser->role }}
                                </span>
                                <span class="text-xs text-white/50 font-bold">•</span>
                                <span class="text-xs text-white/80 font-bold">NIP. {{ $selectedUser->nip ?? '-' }}</span>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-black text-white mt-1">
                                {{ $selectedUser->name }}
                            </h3>
                            <p class="text-xs text-white/70 mt-0.5">
                                {{ $selectedUser->position ?? 'Pegawai' }} &mdash; {{ $selectedUser->unit_kerja ?? 'BPMP Provinsi Kalimantan Timur' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="text-left md:text-right">
                            <span class="text-[10px] font-black uppercase tracking-widest text-white/60 block">Bulan Aktif</span>
                            <span class="text-base font-black text-sipega-orange">
                                {{ \Carbon\Carbon::parse($monthYear.'-01')->translatedFormat('F Y') }}
                            </span>
                        </div>
                        <a href="{{ route('attendance.recap', $selectedUser->id) }}?month={{ $monthYear }}" target="_blank" class="px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-2xl text-xs font-bold text-white transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            Lihat Lembar Rekap
                        </a>
                    </div>
                </div>

                <!-- MINI STATS CARDS -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mt-6 pt-6 border-t border-white/10">
                    <div class="bg-white/5 rounded-2xl p-3 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-emerald-400 block">Hadir</span>
                        <span class="text-xl font-black text-white mt-0.5 block">{{ $stats['hadir'] }}</span>
                        <span class="text-[9px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-3 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-blue-400 block">Dinas Luar (DL)</span>
                        <span class="text-xl font-black text-white mt-0.5 block">{{ $stats['dl'] }}</span>
                        <span class="text-[9px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-3 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-purple-400 block">Cuti</span>
                        <span class="text-xl font-black text-white mt-0.5 block">{{ $stats['cuti'] }}</span>
                        <span class="text-[9px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-3 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-amber-400 block">Sakit</span>
                        <span class="text-xl font-black text-white mt-0.5 block">{{ $stats['sakit'] }}</span>
                        <span class="text-[9px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-3 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-teal-400 block">Izin</span>
                        <span class="text-xl font-black text-white mt-0.5 block">{{ $stats['izin'] }}</span>
                        <span class="text-[9px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-3 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-rose-400 block">ALPA</span>
                        <span class="text-xl font-black text-white mt-0.5 block">{{ $stats['alpa'] }}</span>
                        <span class="text-[9px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-3 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-orange-400 block">Terlambat (TL)</span>
                        <span class="text-xl font-black text-white mt-0.5 block">{{ $stats['total_tl'] }}</span>
                        <span class="text-[9px] text-white/50">Menit</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-3 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-orange-400 block">Plg Cepat (PSW)</span>
                        <span class="text-xl font-black text-white mt-0.5 block">{{ $stats['total_psw'] }}</span>
                        <span class="text-[9px] text-white/50">Menit</span>
                    </div>
                </div>
            </div>

            <!-- TABEL HARIAN KEHADIRAN PEGAWAI -->
            <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-gray-100 gap-4">
                    <div>
                        <h3 class="text-lg font-black text-sipega-navy">
                            Rincian Kehadiran & Status Harian
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Klik tombol <strong>"Beri / Edit Keterangan"</strong> pada tanggal yang ingin diubah statusnya (misal: DL, Sakit, Cuti, Izin).
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                            {{ count($dailyData) }} Hari Kalender
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto mt-4 -mx-6 sm:-mx-8">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-gray-500 border-b border-gray-200">
                                <th class="py-3 px-4 text-center w-12">No</th>
                                <th class="py-3 px-4">Tanggal & Hari</th>
                                <th class="py-3 px-4 text-center">Jam Masuk</th>
                                <th class="py-3 px-4 text-center">Jam Pulang</th>
                                <th class="py-3 px-4 text-center">TL (Mnt)</th>
                                <th class="py-3 px-4 text-center">PSW (Mnt)</th>
                                <th class="py-3 px-4 text-center">Status Kehadiran</th>
                                <th class="py-3 px-4">Keterangan / Alasan</th>
                                <th class="py-3 px-4 text-center">Sumber</th>
                                <th class="py-3 px-4 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs font-medium">
                            @foreach($dailyData as $idx => $row)
                            <tr class="hover:bg-slate-50/80 transition-colors {{ $row['status'] === 'ALPA' ? 'bg-red-50/40' : (in_array($row['status'], ['DL', 'Tugas Luar (ST)']) ? 'bg-blue-50/30' : ($row['is_weekend'] || $row['is_holiday'] ? 'bg-gray-50/60' : '')) }}">
                                <!-- No -->
                                <td class="py-3 px-4 text-center text-gray-400 font-bold">
                                    {{ $idx + 1 }}
                                </td>

                                <!-- Tanggal -->
                                <td class="py-3 px-4">
                                    <div class="font-bold text-sipega-navy">
                                        {{ $row['formatted_date'] }}
                                    </div>
                                    <div class="text-[10px] font-semibold {{ $row['is_weekend'] ? 'text-rose-500' : 'text-gray-400' }}">
                                        {{ $row['day_name'] }}
                                        @if($row['is_holiday'])
                                            <span class="text-rose-600 font-bold">(Libur Nasional)</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Check In -->
                                <td class="py-3 px-4 text-center font-mono font-bold {{ $row['check_in'] ? 'text-emerald-700' : 'text-gray-300' }}">
                                    {{ $row['check_in'] ?? '-' }}
                                </td>

                                <!-- Check Out -->
                                <td class="py-3 px-4 text-center font-mono font-bold {{ $row['check_out'] ? 'text-indigo-700' : 'text-gray-300' }}">
                                    {{ $row['check_out'] ?? '-' }}
                                </td>

                                <!-- TL -->
                                <td class="py-3 px-4 text-center font-bold {{ $row['tl_minutes'] > 0 ? 'text-rose-600 bg-rose-50/50 rounded-xl' : 'text-gray-400' }}">
                                    {{ $row['tl_minutes'] > 0 ? $row['tl_minutes'] : '-' }}
                                </td>

                                <!-- PSW -->
                                <td class="py-3 px-4 text-center font-bold {{ $row['psw_minutes'] > 0 ? 'text-amber-600 bg-amber-50/50 rounded-xl' : 'text-gray-400' }}">
                                    {{ $row['psw_minutes'] > 0 ? $row['psw_minutes'] : '-' }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $badgeClasses = match($row['status']) {
                                            'Hadir' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'DL', 'Tugas Luar (ST)' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'Cuti' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'Sakit' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'Izin' => 'bg-teal-100 text-teal-800 border-teal-200',
                                            'ALPA' => 'bg-rose-100 text-rose-800 border-rose-200',
                                            'Libur' => 'bg-gray-100 text-gray-600 border-gray-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-black border {{ $badgeClasses }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>

                                <!-- Keterangan (Notes) -->
                                <td class="py-3 px-4">
                                    @if(!empty($row['notes']))
                                        <div class="text-xs font-semibold text-gray-800 bg-amber-50/70 border border-amber-200/60 rounded-xl p-2 inline-block max-w-xs">
                                            {{ $row['notes'] }}
                                        </div>
                                    @else
                                        <span class="text-gray-300 italic text-[11px]">- Belum ada keterangan -</span>
                                    @endif
                                </td>

                                <!-- Sumber -->
                                <td class="py-3 px-4 text-center">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $row['source'] === 'Manual' ? 'bg-orange-100 text-sipega-orange' : ($row['source'] === 'Mesin' ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-600') }}">
                                        {{ $row['source'] }}
                                    </span>
                                </td>

                                <!-- Aksi Edit Modal -->
                                <td class="py-3 px-4 text-center">
                                    <button type="button" @click="openEdit({{ json_encode($row) }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sipega-orange/10 hover:bg-sipega-orange text-sipega-orange hover:text-white rounded-xl text-xs font-bold transition-all border border-sipega-orange/30">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Beri Keterangan
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>

        <!-- MODAL EDIT / BERI KETERANGAN (Alpine.js) -->
        <div x-show="editModal" 
             x-cloak 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            
            <div @click.away="editModal = false" 
                 class="bg-white rounded-[2.5rem] max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-gray-100 transform transition-all">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-sipega-orange block">
                            Formulir Keterangan Kehadiran
                        </span>
                        <h3 class="text-xl font-black text-sipega-navy mt-0.5">
                            Edit Status & Keterangan
                        </h3>
                    </div>
                    <button @click="editModal = false" class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Info Pegawai & Tanggal -->
                <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-gray-200/60 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Pegawai</span>
                        <span class="font-black text-sipega-navy" x-text="form.user_name"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-gray-400 font-bold block text-[10px] uppercase">Tanggal</span>
                        <span class="font-black text-sipega-orange" x-text="form.formatted_date"></span>
                    </div>
                </div>

                <!-- Form POST Update -->
                <form method="POST" action="{{ route('admin.attendance.update_log') }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="user_id" :value="form.user_id">
                    <input type="hidden" name="date" :value="form.date">

                    <!-- Pilihan Status -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-sipega-navy mb-1.5">
                            Status Kehadiran <span class="text-red-500">*</span>
                        </label>
                        <select name="status" x-model="form.status" required class="w-full text-sm font-bold rounded-2xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange transition-all py-2.5 px-4 shadow-sm">
                            <option value="Hadir">Hadir</option>
                            <option value="DL">Dinas Luar (DL)</option>
                            <option value="Cuti">Cuti</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Izin">Izin</option>
                            <option value="ALPA">ALPA (Tanpa Keterangan)</option>
                            <option value="WFH">WFH / WFA</option>
                            <option value="Libur">Libur / Off</option>
                        </select>
                    </div>

                    <!-- Keterangan / Alasan -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-sipega-navy mb-1.5">
                            Keterangan / Alasan Resmi
                        </label>
                        <textarea name="notes" x-model="form.notes" rows="3" placeholder="Contoh: Dinas Luar Pendampingan Sekolah Penggerak di Kota Balikpapan / Cuti Tahunan SK No. 123..." class="w-full text-xs font-medium rounded-2xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange transition-all p-3 shadow-sm"></textarea>
                        <p class="text-[10px] text-gray-400 mt-1">Keterangan ini akan tampil pada rekap kehadiran pegawai dan laporan bulanan.</p>
                    </div>

                    <!-- Jam Masuk & Jam Pulang (Opsional Koreksi) -->
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">
                                Jam Masuk (Check In)
                            </label>
                            <input type="time" name="check_in" x-model="form.check_in" class="w-full text-xs font-mono font-bold rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange p-2">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">
                                Jam Pulang (Check Out)
                            </label>
                            <input type="time" name="check_out" x-model="form.check_out" class="w-full text-xs font-mono font-bold rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange p-2">
                        </div>
                    </div>

                    <!-- Menit TL & PSW -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">
                                Terlambat (TL) - Menit
                            </label>
                            <input type="number" name="tl_minutes" x-model="form.tl_minutes" min="0" class="w-full text-xs font-bold rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange p-2">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">
                                Pulang Cepat (PSW) - Menit
                            </label>
                            <input type="number" name="psw_minutes" x-model="form.psw_minutes" min="0" class="w-full text-xs font-bold rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange p-2">
                        </div>
                    </div>
                    <div class="text-[10px] text-gray-400 bg-blue-50/60 p-2.5 rounded-xl border border-blue-100">
                        <strong>Tips:</strong> Jika status diubah menjadi <em>DL, Cuti, Sakit, atau Izin</em>, sistem secara otomatis mereset penalti TL & PSW ke 0 agar tidak mengurangi Tukin pegawai.
                    </div>

                    <!-- Buttons -->
                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="editModal = false" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-2xl transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-sipega-orange hover:bg-orange-600 text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-app-layout>