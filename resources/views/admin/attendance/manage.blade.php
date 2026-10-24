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
        recalcModal: false,
        recalcRamadhan: false,
        recalcAllModal: false,
        recalcAllRamadhan: false,
        tl_info: '',
        psw_info: '',
        form: {
            user_id: '{{ $selectedUser?->id ?? '' }}',
            user_name: '{{ $selectedUser?->name ?? '' }}',
            date: '',
            day_of_week: 1,
            formatted_date: '',
            status: 'Hadir',
            notes: '',
            check_in: '',
            check_out: '',
            tl_minutes: 0,
            psw_minutes: 0,
            is_ramadhan: false
        },
        openEdit(row) {
            this.form.date = row.date;
            this.form.day_of_week = row.day_of_week ?? 1;
            this.form.formatted_date = row.formatted_date + ' (' + row.day_name + ')';
            this.form.status = (row.status === 'Libur' || row.status === 'Belum Ada Log') ? 'Hadir' : row.status;
            this.form.notes = row.notes || '';
            this.form.check_in = row.check_in || '';
            this.form.check_out = row.check_out || '';
            this.form.tl_minutes = row.tl_minutes || 0;
            this.form.psw_minutes = row.psw_minutes || 0;
            this.form.is_ramadhan = false;
            this.recalculateMinutes();
            this.editModal = true;
        },
        recalculateMinutes() {
            // Status bebas penalti
            if (['DL', 'Tugas Luar (ST)', 'Cuti', 'Sakit', 'Izin', 'LDS', 'LPS', 'Libur'].includes(this.form.status)) {
                this.form.tl_minutes = 0;
                this.form.psw_minutes = 0;
                this.tl_info = 'Bebas penalti TL';
                this.psw_info = 'Bebas penalti PSW';
                return;
            }

            const isFriday = (this.form.day_of_week === 5);
            const startTarget = this.form.is_ramadhan ? '08:00' : '07:30';
            const endTarget = this.form.is_ramadhan ? (isFriday ? '15:30' : '15:00') : (isFriday ? '16:30' : '16:00');

            // Hitung Menit Terlambat (TL)
            if (this.form.check_in && this.form.check_in.length >= 4) {
                const parts = this.form.check_in.split(':');
                const h = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10);
                const tParts = startTarget.split(':');
                const th = parseInt(tParts[0], 10);
                const tm = parseInt(tParts[1], 10);

                const diff = (h * 60 + m) - (th * 60 + tm);
                if (diff > 0) {
                    this.form.tl_minutes = diff;
                    this.tl_info = diff + ' mnt (Standar ' + startTarget + ')';
                } else {
                    this.form.tl_minutes = 0;
                    this.tl_info = 'Tepat / lebih awal (<= ' + startTarget + ')';
                }
            } else {
                this.form.tl_minutes = 0;
                if (['LD', 'Lupa Absen Datang (LD)'].includes(this.form.status)) {
                    this.tl_info = '⚠️ Lupa Absen Datang (LD)';
                } else {
                    this.tl_info = '⚠️ Jam masuk kosong (Perlu Verifikasi)';
                }
            }

            // Hitung Menit Pulang Sebelum Waktu (PSW)
            if (this.form.check_out && this.form.check_out.length >= 4) {
                const parts = this.form.check_out.split(':');
                const h = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10);
                const tParts = endTarget.split(':');
                const th = parseInt(tParts[0], 10);
                const tm = parseInt(tParts[1], 10);

                const diff = (th * 60 + tm) - (h * 60 + m);
                if (diff > 0) {
                    this.form.psw_minutes = diff;
                    this.psw_info = diff + ' mnt (Standar ' + endTarget + ')';
                } else {
                    this.form.psw_minutes = 0;
                    this.psw_info = 'Tepat / lewat jam (' + endTarget + ')';
                }
            } else {
                this.form.psw_minutes = 0;
                if (['LP', 'Lupa Absen Pulang (LP)'].includes(this.form.status)) {
                    this.psw_info = '⚠️ Lupa Absen Pulang (LP)';
                } else {
                    this.psw_info = '⚠️ Jam pulang kosong (Perlu Verifikasi)';
                }
            }
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
                    <div class="md:col-span-3">
                        <label class="block text-xs font-black uppercase tracking-wider text-sipega-navy mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            1. Periode Bulan
                        </label>
                        <input type="month" name="month" value="{{ $monthYear }}" class="w-full text-sm font-bold rounded-2xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange transition-all py-2.5 px-4 shadow-sm">
                    </div>

                    <!-- Filter Nama Pegawai -->
                    <div class="md:col-span-5">
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

                    <!-- Tombol Submit Filter & Tombol Hitung All Pegawai -->
                    <div class="md:col-span-4 flex items-center gap-2">
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-sipega-navy hover:bg-blue-900 text-white font-bold text-xs uppercase tracking-wider rounded-2xl shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Tampilkan
                        </button>
                        <button type="button" @click="recalcAllModal = true" class="py-2.5 px-4 bg-sipega-orange hover:bg-orange-600 text-white font-bold text-xs rounded-2xl shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span>Hitung TL & PSW All Pegawai</span>
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
                <div class="mt-6 flex flex-wrap justify-center items-center gap-3">
                    <button type="button" @click="recalcAllModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider bg-sipega-orange hover:bg-orange-600 text-white shadow-md transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Hitung TL & PSW All Pegawai
                    </button>
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
                                {{ $selectedUser->position ?? 'Pegawai' }} &mdash; {{ $selectedUser->gugus_mutu ?? 'BPMP Provinsi Kalimantan Timur' }}
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
                @if(($stats['needs_verif'] ?? 0) > 0)
                <div class="mt-4 p-3.5 bg-amber-500/20 border border-amber-400/40 rounded-2xl flex items-center justify-between text-xs text-amber-200">
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg">⚠️</span>
                        <div>
                            <span class="font-black text-amber-300">Perhatian:</span>
                            <span class="font-medium">Terdapat <strong>{{ $stats['needs_verif'] }} hari</strong> kehadiran yang jam masuk atau jam pulangnya kosong dan memerlukan verifikasi (LD / LP / Surat).</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-400 text-slate-900 uppercase tracking-wider whitespace-nowrap shadow-sm">
                        {{ $stats['needs_verif'] }} Perlu Verifikasi
                    </span>
                </div>
                @endif

                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 xl:grid-cols-13 gap-2 mt-6 pt-6 border-t border-white/10">
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-emerald-400 block">Hadir</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['hadir'] }}</span>
                        <span class="text-[8px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-blue-400 block">DL</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['dl'] }}</span>
                        <span class="text-[8px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-purple-400 block">Cuti</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['cuti'] }}</span>
                        <span class="text-[8px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-amber-400 block">Sakit</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['sakit'] }}</span>
                        <span class="text-[8px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-teal-400 block">Izin</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['izin'] }}</span>
                        <span class="text-[8px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-amber-300 block">LD</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['ld'] }}</span>
                        <span class="text-[8px] text-white/50">Lupa Dtg</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-indigo-300 block">LP</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['lp'] }}</span>
                        <span class="text-[8px] text-white/50">Lupa Plg</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-sky-300 block">LDS</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['lds'] }}</span>
                        <span class="text-[8px] text-white/50">LD Surat</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-violet-300 block">LPS</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['lps'] }}</span>
                        <span class="text-[8px] text-white/50">LP Surat</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-rose-400 block">ALPA</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['alpa'] }}</span>
                        <span class="text-[8px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border {{ ($stats['needs_verif'] ?? 0) > 0 ? 'border-amber-400/50 bg-amber-500/10' : 'border-white/10' }} text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-amber-300 block">Verif</span>
                        <span class="text-lg font-black {{ ($stats['needs_verif'] ?? 0) > 0 ? 'text-amber-300' : 'text-white' }} mt-0.5 block">{{ $stats['needs_verif'] ?? 0 }}</span>
                        <span class="text-[8px] text-white/50">Hari</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-orange-400 block">TL</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['total_tl'] }}</span>
                        <span class="text-[8px] text-white/50">Menit</span>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-2.5 border border-white/10 text-center">
                        <span class="text-[9px] font-black uppercase tracking-wider text-orange-400 block">PSW</span>
                        <span class="text-lg font-black text-white mt-0.5 block">{{ $stats['total_psw'] }}</span>
                        <span class="text-[8px] text-white/50">Menit</span>
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
                            Klik tombol <strong>"Beri / Edit Keterangan"</strong> pada tanggal yang ingin diubah statusnya, atau klik <strong>"Hitung Otomatis Menit"</strong> untuk mengkalkulasi seluruh log bulan ini.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="recalcModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 bg-sipega-navy hover:bg-black text-white text-xs font-bold rounded-2xl shadow-sm transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Hitung Pegawai Ini
                        </button>
                        <button type="button" @click="recalcAllModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 bg-sipega-orange hover:bg-orange-600 text-white text-xs font-bold rounded-2xl shadow-sm transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Hitung All Pegawai
                        </button>
                        <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-2xl text-xs font-bold bg-gray-100 text-gray-700">
                            {{ count($dailyData) }} Hari
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
                                <td class="py-3 px-4 text-center font-mono font-bold">
                                    @if($row['check_in'])
                                        <span class="text-emerald-700">{{ $row['check_in'] }}</span>
                                    @elseif(!empty($row['needs_check_in_verif']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300" title="Jam masuk tidak ada scan / Lupa Absen Datang">
                                            Kosong
                                        </span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>

                                <!-- Check Out -->
                                <td class="py-3 px-4 text-center font-mono font-bold">
                                    @if($row['check_out'])
                                        <span class="text-indigo-700">{{ $row['check_out'] }}</span>
                                    @elseif(!empty($row['needs_check_out_verif']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300" title="Jam pulang tidak ada scan / Lupa Absen Pulang">
                                            Kosong
                                        </span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>

                                <!-- TL (Terlambat) -->
                                <td class="py-3 px-4 text-center">
                                    @if(!empty($row['needs_check_in_verif']))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-sm whitespace-nowrap" title="Jam masuk kosong, jangan anggap 0 menit. Tandai untuk verifikasi / LD">
                                            <span>⚠️</span> Verifikasi
                                        </span>
                                    @elseif($row['tl_minutes'] > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-black bg-rose-100 text-rose-700 border border-rose-200 shadow-sm whitespace-nowrap" title="Terlambat {{ $row['tl_minutes'] }} Menit (Masuk > 07.30)">
                                            {{ $row['tl_minutes'] }} mnt
                                        </span>
                                    @elseif($row['check_in'])
                                        <span class="text-emerald-600 font-bold text-[10px] whitespace-nowrap" title="Masuk lebih awal atau tepat waktu (0 menit)">0 mnt</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>

                                <!-- PSW (Pulang Sebelum Waktu) -->
                                <td class="py-3 px-4 text-center">
                                    @if(!empty($row['needs_check_out_verif']))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 shadow-sm whitespace-nowrap" title="Jam pulang kosong, jangan anggap 0 menit. Tandai untuk verifikasi / LP">
                                            <span>⚠️</span> Verifikasi
                                        </span>
                                    @elseif($row['psw_minutes'] > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-200 shadow-sm whitespace-nowrap" title="Pulang Cepat {{ $row['psw_minutes'] }} Menit (Pulang sebelum jadwal)">
                                            {{ $row['psw_minutes'] }} mnt
                                        </span>
                                    @elseif($row['check_out'])
                                        <span class="text-emerald-600 font-bold text-[10px] whitespace-nowrap" title="Pulang setelah jadwal atau tepat waktu (0 menit)">0 mnt</span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
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
                                            'LD', 'Lupa Absen Datang (LD)' => 'bg-amber-50 text-amber-900 border-amber-300',
                                            'LP', 'Lupa Absen Pulang (LP)' => 'bg-indigo-50 text-indigo-900 border-indigo-300',
                                            'LDS' => 'bg-sky-100 text-sky-900 border-sky-300',
                                            'LPS' => 'bg-violet-100 text-violet-900 border-violet-300',
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

                    <!-- Pilihan Pola Ramadhan di Modal -->
                    <div class="p-3 rounded-2xl border transition-all" :class="form.is_ramadhan ? 'bg-emerald-50 border-emerald-300' : 'bg-slate-50 border-gray-200'">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-xs font-bold text-sipega-navy flex items-center gap-1.5">
                                <span>🌙</span> Gunakan Pola Jam Ramadhan
                            </span>
                            <input type="checkbox" name="is_ramadhan" value="1" x-model="form.is_ramadhan" @change="recalculateMinutes()" class="rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
                        </label>
                        <p class="text-[10px] text-gray-500 mt-1" x-text="form.is_ramadhan ? 'Standar: Masuk 08:00 | Pulang 15:00 (Jumat 15:30)' : 'Standar Normal: Masuk 07:30 | Pulang 16:00 (Jumat 16:30)'"></p>
                    </div>

                    <!-- Pilihan Status -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-sipega-navy mb-1.5">
                            Status Kehadiran <span class="text-red-500">*</span>
                        </label>
                        <select name="status" x-model="form.status" @change="recalculateMinutes()" required class="w-full text-sm font-bold rounded-2xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange transition-all py-2.5 px-4 shadow-sm">
                            <option value="Hadir">Hadir</option>
                            <option value="DL">Dinas Luar (DL)</option>
                            <option value="Cuti">Cuti</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Izin">Izin</option>
                            <option value="LD">Lupa Absen Datang (LD)</option>
                            <option value="LP">Lupa Absen Pulang (LP)</option>
                            <option value="LDS">LDS (Lupa Absen Datang Bersurat)</option>
                            <option value="LPS">LPS (Lupa Absen Pulang Bersurat)</option>
                            <option value="ALPA">ALPA (Tanpa Keterangan)</option>
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

                    <!-- Peringatan & Panduan Verifikasi Jam Kosong -->
                    <div x-show="!form.check_in && !['DL', 'Tugas Luar (ST)', 'Cuti', 'Sakit', 'Izin', 'Libur', 'ALPA'].includes(form.status)" class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800 flex items-start gap-2.5">
                        <span class="text-base flex-shrink-0">⚠️</span>
                        <div>
                            <strong class="font-black text-amber-900 block">Jam Masuk Kosong (Perlu Verifikasi)</strong>
                            <p class="text-[11px] text-amber-700 mt-0.5 leading-relaxed">
                                Jam masuk tidak otomatis dianggap 0 menit. Jika pegawai hadir namun tidak scan masuk, tetapkan status <strong>LD (Lupa Absen Datang)</strong> atau <strong>LDS (Lupa Absen Datang Bersurat)</strong>, atau isikan jam masuk hasil verifikasi manual.
                            </p>
                        </div>
                    </div>

                    <div x-show="!form.check_out && !['DL', 'Tugas Luar (ST)', 'Cuti', 'Sakit', 'Izin', 'Libur', 'ALPA'].includes(form.status)" class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800 flex items-start gap-2.5">
                        <span class="text-base flex-shrink-0">⚠️</span>
                        <div>
                            <strong class="font-black text-amber-900 block">Jam Pulang Kosong (Perlu Verifikasi)</strong>
                            <p class="text-[11px] text-amber-700 mt-0.5 leading-relaxed">
                                Jam pulang tidak otomatis dianggap 0 menit. Jika pegawai hadir namun tidak scan pulang, tetapkan status <strong>LP (Lupa Absen Pulang)</strong> atau <strong>LPS (Lupa Absen Pulang Bersurat)</strong>, atau isikan jam pulang hasil verifikasi manual.
                            </p>
                        </div>
                    </div>

                    <!-- Jam Masuk & Jam Pulang (Opsional Koreksi) -->
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">
                                Jam Masuk (Check In)
                            </label>
                            <input type="time" step="1" name="check_in" x-model="form.check_in" @input="recalculateMinutes()" class="w-full text-xs font-mono font-bold rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange p-2">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">
                                Jam Pulang (Check Out)
                            </label>
                            <input type="time" step="1" name="check_out" x-model="form.check_out" @input="recalculateMinutes()" class="w-full text-xs font-mono font-bold rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange p-2">
                        </div>
                    </div>

                    <!-- Menit TL & PSW Otomatis -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-gray-700 flex items-center justify-between">
                                <span>Terlambat (TL)</span>
                                <span class="text-[9px] font-bold text-rose-500" x-text="tl_info"></span>
                            </label>
                            <div class="relative">
                                <input type="number" name="tl_minutes" x-model="form.tl_minutes" min="0" class="w-full text-xs font-bold rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange p-2 pr-12">
                                <span class="absolute right-3 top-2 text-[10px] text-gray-400 font-bold">menit</span>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-gray-700 flex items-center justify-between">
                                <span>Pulang Cepat (PSW)</span>
                                <span class="text-[9px] font-bold text-amber-500" x-text="psw_info"></span>
                            </label>
                            <div class="relative">
                                <input type="number" name="psw_minutes" x-model="form.psw_minutes" min="0" class="w-full text-xs font-bold rounded-xl border-gray-200 bg-gray-50/50 focus:bg-white focus:border-sipega-orange focus:ring-sipega-orange p-2 pr-12">
                                <span class="absolute right-3 top-2 text-[10px] text-gray-400 font-bold">menit</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <button type="button" @click="recalculateMinutes()" class="text-[11px] font-bold text-sipega-orange hover:text-orange-700 transition flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>Hitung Ulang Menit Otomatis</span>
                        </button>
                    </div>

                    <div class="text-[10px] text-gray-500 bg-blue-50/70 p-3 rounded-2xl border border-blue-100 leading-relaxed">
                        <strong>Keterangan:</strong> Menit keterlambatan (TL) dihitung otomatis jika masuk lewat dari jam standar. Pulang cepat (PSW) dihitung jika pulang sebelum jam kerja berakhir. Jika status diubah menjadi <em>DL, Cuti, Sakit, Izin, LDS, atau LPS</em>, menit TL & PSW otomatis menjadi 0.
                    </div>

                    <!-- Buttons -->
                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="button" @click="editModal = false" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-2xl transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-sipega-orange hover:bg-orange-600 text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- MODAL RECALCULATE MONTH (Hitung Otomatis Seluruh Bulan) -->
        @if($selectedUser)
        <div x-show="recalcModal" 
             x-cloak 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            
            <div @click.away="recalcModal = false" 
                 class="bg-white rounded-[2.5rem] max-w-md w-full p-6 sm:p-8 shadow-2xl border border-gray-100 transform transition-all">
                
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-sipega-navy block">
                            Kalkulasi Otomatis Massal
                        </span>
                        <h3 class="text-xl font-black text-sipega-navy mt-0.5">
                            Hitung Menit TL & PSW Sebulan
                        </h3>
                    </div>
                    <button @click="recalcModal = false" class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-gray-200/60 text-xs space-y-1">
                    <p class="text-gray-500">Pegawai: <b class="text-sipega-navy">{{ $selectedUser->name }}</b></p>
                    <p class="text-gray-500">Periode: <b class="text-sipega-orange">{{ \Carbon\Carbon::parse($monthYear.'-01')->translatedFormat('F Y') }}</b></p>
                </div>

                <form method="POST" action="{{ route('admin.attendance.recalculate_month') }}" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <input type="hidden" name="month" value="{{ $monthYear }}">

                    <!-- Pola Jam Kerja Pilihan -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-wider text-sipega-navy">
                            Pilih Pola Jam Kerja:
                        </label>
                        <div class="p-3 rounded-2xl border transition-all" :class="recalcRamadhan ? 'bg-emerald-50 border-emerald-300' : 'bg-slate-50 border-gray-200'">
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-xs font-bold text-sipega-navy flex items-center gap-1.5">
                                    <span>🌙</span> Pola Jam Kerja Ramadhan / Puasa
                                </span>
                                <input type="checkbox" name="is_ramadhan" value="1" x-model="recalcRamadhan" class="rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
                            </label>
                            <p class="text-[10px] text-gray-500 mt-1" x-text="recalcRamadhan ? 'Masuk 08:00 | Pulang 15:00 (Jumat 15:30 WITA)' : 'Masuk 07:30 | Pulang 16:00 (Jumat 16:30 WITA)'"></p>
                        </div>
                    </div>

                    <p class="text-[11px] text-gray-500 leading-relaxed bg-amber-50/70 border border-amber-200/60 p-3 rounded-2xl">
                        Sistem akan membaca jam masuk dan jam pulang seluruh hari pada bulan ini, lalu otomatis menghitung menit keterlambatan (TL) dan menit pulang sebelum waktunya (PSW) secara akurat.
                    </p>

                    <div class="pt-3 flex items-center justify-end gap-3">
                        <button type="button" @click="recalcModal = false" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-2xl transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-sipega-navy hover:bg-black text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Mulai Hitung Ulang
                        </button>
                    </div>
                </form>

            </div>
        </div>
        @endif

        <!-- MODAL RECALCULATE ALL EMPLOYEES (Hitung Otomatis Seluruh Pegawai) -->
        <div x-show="recalcAllModal" 
             x-cloak 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            
            <div @click.away="recalcAllModal = false" 
                 class="bg-white rounded-[2.5rem] max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-gray-100 transform transition-all">
                
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-sipega-orange block">
                            Kalkulasi Otomatis Seluruh Pegawai
                        </span>
                        <h3 class="text-xl font-black text-sipega-navy mt-0.5">
                            Hitung Menit TL & PSW All Pegawai
                        </h3>
                    </div>
                    <button @click="recalcAllModal = false" class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="mt-4 p-4 rounded-2xl bg-orange-50/70 border border-orange-200/60 text-xs space-y-1">
                    <p class="text-gray-700">Sasaran: <b class="text-sipega-navy">Semua Pegawai Terdaftar ({{ $allPegawai->count() }} Pegawai)</b></p>
                    <p class="text-gray-700">Periode: <b class="text-sipega-orange">{{ \Carbon\Carbon::parse($monthYear.'-01')->translatedFormat('F Y') }}</b></p>
                </div>

                <form method="POST" action="{{ route('admin.attendance.recalculate_all') }}" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="month" value="{{ $monthYear }}">

                    <!-- Pola Jam Kerja Pilihan -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase tracking-wider text-sipega-navy">
                            Pilih Pola Jam Kerja:
                        </label>
                        <div class="p-3.5 rounded-2xl border transition-all" :class="recalcAllRamadhan ? 'bg-emerald-50 border-emerald-300' : 'bg-slate-50 border-gray-200'">
                            <label class="flex items-center justify-between cursor-pointer">
                                <span class="text-xs font-bold text-sipega-navy flex items-center gap-1.5">
                                    <span>🌙</span> Pola Jam Kerja Ramadhan / Puasa
                                </span>
                                <input type="checkbox" name="is_ramadhan" value="1" x-model="recalcAllRamadhan" class="rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
                            </label>
                            <p class="text-[10px] text-gray-500 mt-1" x-text="recalcAllRamadhan ? 'Standar: Masuk 08:00 | Pulang 15:00 (Jumat 15:30 WITA)' : 'Standar Normal: Masuk 07:30 | Pulang 16:00 (Jumat 16:30 WITA)'"></p>
                        </div>
                    </div>

                    <div class="text-[11px] text-gray-600 leading-relaxed bg-slate-50 border border-slate-200 p-3.5 rounded-2xl space-y-1">
                        <p class="font-bold text-sipega-navy">Informasi Proses:</p>
                        <ul class="list-disc pl-4 space-y-0.5 text-[10px] text-gray-500">
                            <li>Sistem akan memproses seluruh log kehadiran seluruh pegawai pada bulan <strong>{{ \Carbon\Carbon::parse($monthYear.'-01')->translatedFormat('F Y') }}</strong>.</li>
                            <li>Status bebas penalti (DL, Cuti, Sakit, Izin, LDS, LPS, Libur) otomatis diberi TL: 0 dan PSW: 0.</li>
                            <li>Kalkulasi capaian performa dan raport seluruh pegawai akan otomatis dimutakhirkan.</li>
                        </ul>
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-3">
                        <button type="button" @click="recalcAllModal = false" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-2xl transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-sipega-orange hover:bg-orange-600 text-white text-xs font-bold rounded-2xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Mulai Hitung All Pegawai
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-app-layout>