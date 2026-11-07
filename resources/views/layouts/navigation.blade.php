<nav x-data="{ open: false }" class="bg-sipega-navy border-b border-sipega-navy/20 shadow-xl">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <x-application-logo class="block h-10 w-auto fill-current text-white" />
                        <div class="hidden lg:block border-l border-white/20 pl-4">
                            <h1 class="text-white font-black text-xl tracking-tighter leading-none italic">SIPEGA<span class="text-sipega-orange font-bold uppercase text-[8px] ml-1 tracking-widest not-italic">Elite</span></h1>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links (Menu Utama) -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-8 sm:flex items-center">
                    <!-- 1. HOME -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-[10px] font-black uppercase tracking-[0.2em] px-3.5 text-white/70 hover:text-white transition-all">
                        {{ __('Home') }}
                    </x-nav-link>

                    <!-- 2. MIK (MONITORING IZIN KARYAWAN) -->
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3.5 py-2 text-[10px] font-black leading-5 text-blue-300 hover:text-white focus:outline-none transition duration-200 ease-in-out uppercase tracking-[0.2em] {{ request()->routeIs('mik.*') ? 'text-white bg-blue-600/30 rounded-full border border-blue-400/40 shadow-sm' : '' }}">
                                <span class="flex items-center gap-1.5">
                                    <span>🏢</span>
                                    <span>MiK</span>
                                </span>
                                <svg class="ms-1.5 h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <!-- Section: MiK (Izin Keluar) -->
                            <div class="px-4 py-2 text-[8px] font-black text-[#0070ba] uppercase tracking-widest border-b border-gray-50 mb-1 flex items-center justify-between bg-blue-50/70">
                                <span class="flex items-center gap-1.5"><span>🏢</span> Monitoring Izin Karyawan</span>
                                <span class="text-[7px] bg-[#0070ba] text-white px-1.5 py-0.5 rounded font-black">TERTIB</span>
                            </div>
                            <x-dropdown-link :href="route('mik.index')" class="text-[11px] font-bold py-2 text-sipega-navy hover:bg-blue-50 flex items-center justify-between">
                                <span>Dashboard MiK (Statistik Izin Keluar)</span>
                                <span class="text-[8px] bg-[#0070ba] text-white px-2 py-0.5 rounded-full font-black">UTAMA</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('mik.form_kantor')" class="text-[11px] font-bold py-2 flex items-center justify-between">
                                <span>Form_Kantor (Izin Keluar Kantor)</span>
                                <span class="text-[8px] bg-sky-100 text-sky-800 px-1.5 py-0.5 rounded font-bold">Kantor</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('mik.form_ruangan')" class="text-[11px] font-bold py-2 flex items-center justify-between">
                                <span>Form_Ruangan (Izin Keluar Ruangan)</span>
                                <span class="text-[8px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-bold">Ruangan</span>
                            </x-dropdown-link>
                            @if(auth()->user()?->canApproveMik())
                            <x-dropdown-link :href="route('mik.approvals')" class="text-[11px] font-bold py-2 flex items-center justify-between border-t border-gray-50">
                                <span>Persetujuan Izin MiK (Ketua GM / Kasubag / Kepala)</span>
                                <span class="text-[8px] bg-emerald-600 text-white px-1.5 py-0.5 rounded font-black">APPROVAL</span>
                            </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>

                    <!-- 3. ARSIP -->
                    <x-nav-link :href="route('documents.index')" :active="request()->routeIs('documents.index')" class="text-[10px] font-black uppercase tracking-[0.2em] px-3.5 text-white/70 hover:text-white transition-all">
                        {{ __('Arsip') }}
                    </x-nav-link>

                    <!-- 4. KINERJA -->
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3.5 py-2 text-[10px] font-black leading-5 text-white/70 hover:text-white focus:outline-none transition duration-200 ease-in-out uppercase tracking-[0.2em] {{ request()->routeIs('agenda.*', 'evidence.*', 'schedules.*') ? 'text-white bg-white/10 rounded-full' : '' }}">
                                <span>Kinerja</span>
                                <svg class="ms-1.5 h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-2 text-[8px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 mb-1">Manajemen Output SKP</div>
                            <x-dropdown-link :href="route('agenda.index', ['open_rhk' => 1])" onclick="if(typeof openModal === 'function' && document.getElementById('rhkModal')){ openModal('rhkModal'); event.preventDefault(); }" class="text-[11px] font-bold py-2.5 flex items-center justify-between text-sipega-navy hover:bg-slate-50">
                                <span class="flex items-center gap-1.5">
                                    <span>🎯</span> Kelola RHK
                                </span>
                                @if(auth()->check())
                                    <span class="text-[9px] font-black bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full">
                                        {{ auth()->user()->rhks->count() }}
                                    </span>
                                @endif
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('agenda.index')" class="text-[11px] font-bold py-2.5">Agenda & Realisasi</x-dropdown-link>
                            <x-dropdown-link :href="route('evidence.index')" class="text-[11px] font-bold py-2.5 text-sipega-navy bg-sipega-orange/5">Bukti Fisik (SKP)</x-dropdown-link>
                            <x-dropdown-link :href="route('schedules.index')" class="text-[11px] font-bold py-2.5">Agenda Individu</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- 5. CUTI (ADMINISTRASI CUTI ASN) -->
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3.5 py-2 text-[10px] font-black leading-5 text-amber-300 hover:text-white focus:outline-none transition duration-200 ease-in-out uppercase tracking-[0.2em] {{ request()->routeIs('leaves.*') ? 'text-white bg-amber-500/20 rounded-full border border-amber-400/30' : '' }}">
                                <span class="flex items-center gap-1.5">
                                    <span>🏖️</span>
                                    <span>Cuti</span>
                                </span>
                                <svg class="ms-1.5 h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <!-- Section: Cuti ASN -->
                            <div class="px-4 py-2 text-[8px] font-black text-amber-800 uppercase tracking-widest border-b border-gray-50 mb-1 flex items-center justify-between bg-amber-50/60">
                                <span class="flex items-center gap-1"><span>🏖️</span> Administrasi Cuti ASN</span>
                                <span class="text-[7px] bg-amber-600 text-white px-1.5 py-0.5 rounded font-black">BKN</span>
                            </div>
                            <x-dropdown-link :href="route('leaves.index')" class="text-[11px] font-bold py-2 text-sipega-navy hover:bg-amber-50 flex items-center justify-between">
                                <span>Kelola Data Cuti</span>
                                <span class="text-[8px] bg-sipega-orange text-white px-2 py-0.5 rounded-full font-black">CUTI</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('leaves.create')" class="text-[11px] font-bold py-2 flex items-center justify-between">
                                <span>+ Ajukan Cuti Baru</span>
                                <span class="text-[8px] bg-emerald-600 text-white px-1.5 py-0.5 rounded font-black">FORM</span>
                            </x-dropdown-link>
                            @if(auth()->user()?->isPimpinanOrAdmin())
                            <x-dropdown-link :href="route('leaves.approvals')" class="text-[11px] font-bold py-2 flex items-center justify-between">
                                <span>Persetujuan Cuti</span>
                                <span class="text-[8px] bg-blue-600 text-white px-1.5 py-0.5 rounded font-black">APPROVAL</span>
                            </x-dropdown-link>
                            @endif
                            @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator']))
                            <x-dropdown-link :href="route('leaves.balances')" class="text-[11px] font-bold py-2 text-emerald-800 flex items-center justify-between border-t border-gray-50">
                                <span>Saldo Cuti Satker</span>
                                <span class="text-[8px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-bold">KUOTA</span>
                            </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>

                    <!-- 5. PIKET PELAYANAN (ULT & PERPUSTAKAAN) -->
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3.5 py-2 text-[10px] font-black leading-5 text-emerald-300 hover:text-white focus:outline-none transition duration-200 ease-in-out uppercase tracking-[0.2em] {{ request()->routeIs('piket.*') ? 'text-white bg-emerald-600/30 rounded-full border border-emerald-400/30' : '' }}">
                                <span class="flex items-center gap-1.5">
                                    <span>🛡️</span>
                                    <span>Piket</span>
                                </span>
                                <svg class="ms-1.5 h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-2 text-[8px] font-black text-emerald-700 uppercase tracking-widest border-b border-gray-50 mb-1 flex items-center justify-between bg-emerald-50/50">
                                <span>Piket Pelayanan ULT & Perpus</span>
                                <span class="text-[7px] bg-emerald-600 text-white px-1.5 py-0.5 rounded font-black">TAHUNAN</span>
                            </div>
                            <x-dropdown-link :href="route('piket.dashboard')" class="text-[11px] font-bold py-2 text-sipega-navy hover:bg-emerald-50 flex items-center justify-between">
                                <span class="flex items-center gap-2"><span>📊</span> Dashboard Piket</span>
                                <span class="text-[8px] bg-emerald-600 text-white px-2 py-0.5 rounded-full font-black">UTAMA</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('piket.schedules.calendar')" class="text-[11px] font-bold py-2 flex items-center justify-between">
                                <span class="flex items-center gap-2"><span>📅</span> Kalender Piket</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('piket.schedules.index')" class="text-[11px] font-bold py-2 flex items-center justify-between">
                                <span class="flex items-center gap-2"><span>🗓️</span> Pengaturan Jadwal</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('piket.conflicts.index')" class="text-[11px] font-bold py-2 text-rose-700 flex items-center justify-between">
                                <span class="flex items-center gap-2"><span>⚠️</span> Deteksi Konflik</span>
                                <span class="text-[8px] bg-rose-100 text-rose-800 px-1.5 py-0.5 rounded font-black">OTOMATIS</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('piket.replacements.index')" class="text-[11px] font-bold py-2 flex items-center justify-between">
                                <span class="flex items-center gap-2"><span>🔄</span> Penggantian Petugas</span>
                                <span class="text-[8px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-bold">3 Level</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('piket.swaps.index')" class="text-[11px] font-bold py-2 flex items-center justify-between">
                                <span class="flex items-center gap-2"><span>🔀</span> Tukar Jadwal</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('piket.monitoring.index')" class="text-[11px] font-bold py-2 flex items-center justify-between">
                                <span class="flex items-center gap-2"><span>📡</span> Monitoring & Presensi</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('piket.officers.index')" class="text-[11px] font-bold py-2 flex items-center justify-between border-t border-gray-50">
                                <span class="flex items-center gap-2"><span>👥</span> Data Petugas Piket</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('piket.reports.index')" class="text-[11px] font-bold py-2 flex items-center justify-between">
                                <span class="flex items-center gap-2"><span>📈</span> Laporan & Rekapitulasi</span>
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- 6. PRESENSI -->
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3.5 py-2 text-[10px] font-black leading-5 text-sky-200 hover:text-white focus:outline-none transition duration-200 ease-in-out uppercase tracking-[0.2em] {{ request()->routeIs('attendance.*', 'letters.*', 'travel-reports.*', 'tukin.*', 'qrcode.*') ? 'text-white bg-sky-500/20 rounded-full border border-sky-400/30' : '' }}">
                                <span class="flex items-center gap-1.5">
                                    <span>⏱️</span>
                                    <span>Presensi</span>
                                </span>
                                <svg class="ms-1.5 h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <!-- Presensi & Kedinasan -->
                            <div class="px-4 py-2 text-[8px] font-black text-sky-800 uppercase tracking-widest border-b border-gray-50 mb-1 flex items-center justify-between bg-sky-50/60">
                                <span>Presensi & Kedinasan</span>
                                <span class="text-[7px] bg-sky-600 text-white px-1.5 py-0.5 rounded font-black">LOG</span>
                            </div>
                            <x-dropdown-link :href="route('attendance.recap')" class="text-[11px] font-bold py-2 text-sipega-navy hover:bg-sky-50 flex items-center justify-between">
                                <span>Laporan Harian (Log Presensi)</span>
                                <span class="text-[8px] bg-blue-600 text-white px-2 py-0.5 rounded-full font-black">LOG</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('attendance.index')" class="text-[11px] font-bold py-2">Presensi Rapat / Kegiatan</x-dropdown-link>
                            <x-dropdown-link :href="route('letters.index')" class="text-[11px] font-bold py-2">SK & Surat Tugas</x-dropdown-link>
                            <x-dropdown-link :href="route('travel-reports.index')" class="text-[11px] font-bold py-2 text-sipega-navy bg-sipega-orange/5 flex items-center justify-between">
                                <span>Laporan Perjalanan Dinas</span>
                                <span class="text-[8px] bg-sipega-orange text-white px-2 py-0.5 rounded-full font-black">LAPORAN</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('qrcode.index')" class="text-[11px] font-bold py-2 border-b border-gray-50">Pembuat QR Code</x-dropdown-link>

                            <!-- Remunerasi / Tukin -->
                            <div class="px-4 py-2 text-[8px] font-black text-emerald-800 uppercase tracking-widest border-b border-gray-50 my-1 flex items-center justify-between bg-emerald-50/60">
                                <span>Tunjangan Kinerja (Tukin)</span>
                                <span class="text-[7px] bg-emerald-600 text-white px-1.5 py-0.5 rounded font-black">14/2025</span>
                            </div>
                            @if(auth()->user()?->isOperatorKepegawaian())
                            <x-dropdown-link :href="route('tukin.index')" class="text-[11px] font-bold py-2 text-emerald-900 bg-emerald-50/80 hover:bg-emerald-100 flex items-center justify-between">
                                <span>Hitung Tukin (Permendikdasmen 14/2025)</span>
                                <span class="text-[8px] bg-emerald-600 text-white px-2 py-0.5 rounded-full font-black">EXCEL</span>
                            </x-dropdown-link>
                            @elseif(\App\Models\Setting::get('show_slip_tukin', '1') == '1' && \App\Models\Setting::get('is_tukin_active', '1') == '1')
                            <x-dropdown-link :href="route('tukin.index')" class="text-[11px] font-bold py-2 text-emerald-900 hover:bg-emerald-50">
                                <span>Slip Tukin Saya</span>
                            </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>

                    <!-- 7. ADMIN (HANYA MUNCUL UNTUK ADMIN, PIMPINAN, KASUBAG) -->
                    @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3.5 py-2 text-[10px] font-black leading-5 text-sipega-orange hover:text-white focus:outline-none transition duration-200 ease-in-out uppercase tracking-[0.2em] {{ request()->routeIs('leader.*', 'travel.*', 'admin.calendar.*', 'duty-simulation.*', 'performance.*', 'attendance.import.*', 'admin.attendance.*', 'pimpinan.auto_evaluation.*', 'users.*', 'settings.*') ? 'text-white bg-sipega-orange rounded-full shadow-md shadow-orange-500/20' : '' }}">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Admin</span>
                                </span>
                                <svg class="ms-1 h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <!-- Otomatisasi & Evaluasi Pimpinan -->
                            <div class="px-4 py-2 text-[8px] font-black text-rose-700 uppercase tracking-widest border-b border-gray-50 mb-1 flex items-center justify-between bg-rose-50/60">
                                <span>Peralatan & Evaluasi Pimpinan</span>
                                <span class="text-[7px] bg-rose-600 text-white px-1.5 py-0.5 rounded font-black">PIMPINAN</span>
                            </div>
                            @if(in_array(auth()->user()->role, ['Pimpinan', 'Admin']))
                            <x-dropdown-link :href="route('pimpinan.auto_evaluation.index')" class="text-[11px] font-bold py-2.5 text-amber-950 bg-amber-50/90 hover:bg-amber-100 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <span>⚡</span>
                                    <span>Otomatisasi Penilaian (Jam 24:00)</span>
                                </span>
                                <span class="text-[8px] bg-red-600 text-white px-2 py-0.5 rounded-full font-black">AUTO</span>
                            </x-dropdown-link>
                            @endif
                            <x-dropdown-link :href="route('leader.agenda.index')" class="text-[11px] font-bold py-2.5 flex items-center justify-between">
                                <span>Monitoring & Penilaian Pegawai</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('performance.recap')" class="text-[11px] font-bold py-2.5 text-emerald-800 bg-emerald-50/70 hover:bg-emerald-100 flex items-center justify-between">
                                <span>Raport Capaian Pegawai</span>
                                <span class="text-[8px] bg-emerald-600 text-white px-2 py-0.5 rounded-full font-black">SKOR</span>
                            </x-dropdown-link>

                            <!-- Manajemen Operasional -->
                            <div class="px-4 py-2 text-[8px] font-black text-gray-400 uppercase tracking-widest border-y border-gray-50 my-1 flex items-center justify-between bg-slate-50">
                                <span>Operasional Kehadiran & ST</span>
                                <span class="text-[7px] bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded font-bold">ADMIN</span>
                            </div>
                            <x-dropdown-link :href="route('admin.attendance.manage')" class="text-[11px] font-bold py-2 text-sipega-navy hover:bg-orange-50 flex items-center justify-between">
                                <span>Kelola Kehadiran Pegawai</span>
                                <span class="text-[8px] bg-sipega-orange text-white px-2 py-0.5 rounded-full font-black">KELOLA</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('attendance.import.index')" class="text-[11px] font-bold py-2 text-emerald-800 hover:bg-emerald-50 flex items-center justify-between">
                                <span>Import Log (SIPEGA-Check)</span>
                                <span class="text-[8px] bg-emerald-600 text-white px-2 py-0.5 rounded-full font-black">SYNC</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('duty-simulation.index')" class="text-[11px] font-bold py-2 text-sipega-navy hover:bg-orange-50 flex items-center justify-between">
                                <span>Simulasi Beban Surat Tugas</span>
                                <span class="text-[8px] bg-sipega-orange text-white px-2 py-0.5 rounded-full font-black">SIMULASI</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('travel.recap')" class="text-[11px] font-bold py-2">Rekap Dinas Luar</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.calendar.index')" class="text-[11px] font-bold py-2">Kalender Kerja Dinas</x-dropdown-link>

                            <!-- Master Data & Konfigurasi -->
                            <div class="px-4 py-2 text-[8px] font-black text-gray-400 uppercase tracking-widest border-y border-gray-50 my-1 bg-slate-50">
                                Master Data & Sistem
                            </div>
                            <x-dropdown-link :href="route('users.index')" class="text-[11px] font-bold py-2 text-sipega-navy hover:bg-orange-50/50 flex items-center justify-between">
                                <span>Data Pegawai (NIP & Satker)</span>
                                <span class="text-[8px] bg-sipega-navy text-white px-2 py-0.5 rounded-full font-black">NIP</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('settings.index')" class="text-[11px] font-bold py-2 text-sipega-navy bg-amber-50/70 hover:bg-amber-100 flex items-center justify-between border-t border-amber-200/60">
                                <span class="flex items-center gap-1.5">
                                    <span class="text-sm">⚙️</span>
                                    <span class="font-black text-sipega-navy">Smart Pengaturan</span>
                                </span>
                                <span class="text-[8px] bg-amber-500 text-white px-2 py-0.5 rounded-full font-black tracking-wider">SISTEM</span>
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2.5 bg-white/5 border border-white/10 text-[10px] font-black rounded-2xl text-white hover:bg-white/10 focus:outline-none transition ease-in-out duration-150 uppercase tracking-widest">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1.5">
                                <svg class="fill-current h-4 w-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator', 'Kepegawaian']))
                        <x-dropdown-link :href="route('settings.index')">
                            {{ __('⚙️ Smart Pengaturan') }}
                        </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100 shadow-2xl">
        <div class="pt-2 pb-3 space-y-1 bg-white">
            <!-- 1: HOME -->
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="font-black uppercase tracking-widest text-[11px]">
                {{ __('Home') }}
            </x-responsive-nav-link>

            <!-- 2: MIK (MONITORING IZIN KARYAWAN) -->
            <div class="px-6 py-3.5 text-[9px] font-black text-[#0070ba] bg-blue-50 uppercase tracking-[0.2em] border-y border-blue-200/50 flex items-center justify-between mt-2">
                <span class="flex items-center gap-1.5">
                    <span>🏢</span> MIK
                </span>
                <span class="text-[8px] bg-[#0070ba] text-white px-2 py-0.5 rounded font-black">TERTIB</span>
            </div>
            <x-responsive-nav-link :href="route('mik.index')" :active="request()->routeIs('mik.index')" class="text-xs font-bold py-2.5 ps-8 text-[#0070ba]">
                {{ __('Dashboard MiK (Statistik Izin Keluar)') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('mik.form_kantor')" :active="request()->routeIs('mik.form_kantor')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Form_Kantor (Izin Keluar Kantor)') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('mik.form_ruangan')" :active="request()->routeIs('mik.form_ruangan')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Form_Ruangan (Izin Keluar Ruangan)') }}
            </x-responsive-nav-link>
            @if(auth()->user()?->canApproveMik())
            <x-responsive-nav-link :href="route('mik.approvals')" :active="request()->routeIs('mik.approvals')" class="text-xs font-medium py-2.5 ps-8 text-emerald-700">
                {{ __('Persetujuan Izin MiK (Ketua GM / Kasubag / Kepala)') }}
            </x-responsive-nav-link>
            @endif

            <!-- 3: ARSIP -->
            <x-responsive-nav-link :href="route('documents.index')" :active="request()->routeIs('documents.index')" class="font-black uppercase tracking-widest text-[11px] text-sipega-orange mt-2">
                {{ __('Arsip') }}
            </x-responsive-nav-link>

            <!-- 4: KINERJA -->
            <div class="px-6 py-3.5 text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-50 flex items-center gap-2 mt-3 bg-slate-50">
                <span>🎯</span> KINERJA
            </div>
            <x-responsive-nav-link :href="route('agenda.index', ['open_rhk' => 1])" onclick="if(typeof openModal === 'function' && document.getElementById('rhkModal')){ openModal('rhkModal'); event.preventDefault(); }" :active="request()->has('open_rhk')" class="text-xs font-semibold py-2.5 ps-8 flex items-center justify-between">
                <span>Kelola RHK</span>
                @if(auth()->check())
                    <span class="text-[9px] font-black bg-slate-100 text-slate-700 px-2 py-0.5 rounded-full me-4">
                        {{ auth()->user()->rhks->count() }}
                    </span>
                @endif
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('agenda.index')" :active="request()->routeIs('agenda.index') && !request()->has('open_rhk')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Agenda & Realisasi') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('evidence.index')" :active="request()->routeIs('evidence.*')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Bukti Fisik (SKP)') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('schedules.index')" :active="request()->routeIs('schedules.index')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Agenda Individu') }}
            </x-responsive-nav-link>

            <!-- 5: CUTI ASN -->
            <div class="px-6 py-3.5 text-[9px] font-black text-amber-800 bg-amber-50 uppercase tracking-[0.2em] border-y border-amber-200/50 flex items-center justify-between mt-2">
                <span class="flex items-center gap-1.5">
                    <span>🏖️</span> CUTI
                </span>
                <span class="text-[8px] bg-amber-600 text-white px-2 py-0.5 rounded font-black">BKN</span>
            </div>
            <x-responsive-nav-link :href="route('leaves.index')" :active="request()->routeIs('leaves.index')" class="text-xs font-bold py-2.5 ps-8 text-sipega-navy">
                {{ __('Kelola Data Cuti ASN') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leaves.create')" :active="request()->routeIs('leaves.create')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('+ Ajukan Cuti Baru') }}
            </x-responsive-nav-link>
            @if(auth()->user()?->isPimpinanOrAdmin())
            <x-responsive-nav-link :href="route('leaves.approvals')" :active="request()->routeIs('leaves.approvals')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Persetujuan Cuti (Approval)') }}
            </x-responsive-nav-link>
            @endif
            @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator']))
            <x-responsive-nav-link :href="route('leaves.balances')" :active="request()->routeIs('leaves.balances')" class="text-xs font-medium py-2.5 ps-8 text-emerald-700">
                {{ __('Saldo Cuti Satker') }}
            </x-responsive-nav-link>
            @endif

            <!-- 6: PIKET -->
            <div class="px-6 py-3.5 text-[9px] font-black text-emerald-800 bg-emerald-50 uppercase tracking-[0.2em] border-y border-emerald-200/50 flex items-center justify-between mt-2">
                <span class="flex items-center gap-1.5">
                    <span>🛡️</span> PIKET
                </span>
                <span class="text-[8px] bg-emerald-600 text-white px-2 py-0.5 rounded font-black">PELAYANAN</span>
            </div>
            <x-responsive-nav-link :href="route('piket.dashboard')" :active="request()->routeIs('piket.dashboard')" class="text-xs font-bold py-2.5 ps-8 text-emerald-800">
                {{ __('Dashboard Piket') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('piket.schedules.calendar')" :active="request()->routeIs('piket.schedules.calendar')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Kalender Piket') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('piket.schedules.index')" :active="request()->routeIs('piket.schedules.*') && !request()->routeIs('piket.schedules.calendar')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Pengaturan Jadwal Tahunan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('piket.conflicts.index')" :active="request()->routeIs('piket.conflicts.*')" class="text-xs font-medium py-2.5 ps-8 text-rose-700">
                {{ __('Deteksi Konflik Jadwal') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('piket.replacements.index')" :active="request()->routeIs('piket.replacements.*')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Penggantian Petugas (3 Prioritas)') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('piket.swaps.index')" :active="request()->routeIs('piket.swaps.*')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Pengajuan Tukar Jadwal') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('piket.monitoring.index')" :active="request()->routeIs('piket.monitoring.*')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Monitoring & Presensi Layanan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('piket.reports.index')" :active="request()->routeIs('piket.reports.*')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Laporan dan Rekapitulasi') }}
            </x-responsive-nav-link>

            <!-- 6: PRESENSI -->
            <div class="px-6 py-3.5 text-[9px] font-black text-sky-800 bg-sky-50 uppercase tracking-[0.2em] border-y border-sky-200/50 flex items-center justify-between mt-2">
                <span class="flex items-center gap-1.5">
                    <span>⏱️</span> PRESENSI
                </span>
                <span class="text-[8px] bg-sky-600 text-white px-2 py-0.5 rounded font-black">PRESENSI</span>
            </div>
            <x-responsive-nav-link :href="route('attendance.recap')" :active="request()->routeIs('attendance.recap')" class="text-xs font-bold py-2.5 ps-8 text-blue-700 bg-blue-50/50">
                {{ __('Laporan Harian (Log Presensi)') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.index')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Presensi Rapat / Kegiatan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('letters.index')" :active="request()->routeIs('letters.*')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('SK & Surat Tugas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('travel-reports.index')" :active="request()->routeIs('travel-reports.*')" class="text-xs font-semibold py-2.5 ps-8 text-sipega-orange">
                {{ __('Laporan Perjalanan Dinas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('qrcode.index')" :active="request()->routeIs('qrcode.*')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Pembuat QR Code') }}
            </x-responsive-nav-link>
            @if(auth()->user()?->isOperatorKepegawaian())
            <x-responsive-nav-link :href="route('tukin.index')" :active="request()->routeIs('tukin.*')" class="text-xs font-bold py-2.5 ps-8 text-emerald-800 bg-emerald-50/70 flex items-center justify-between">
                <span>{{ __('Hitung Tukin Pegawai (Permendikdasmen 14/2025)') }}</span>
                <span class="text-[8px] bg-emerald-600 text-white px-2 py-0.5 rounded font-black">EXCEL</span>
            </x-responsive-nav-link>
            @elseif(\App\Models\Setting::get('show_slip_tukin', '1') == '1' && \App\Models\Setting::get('is_tukin_active', '1') == '1')
            <x-responsive-nav-link :href="route('tukin.index')" :active="request()->routeIs('tukin.*')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Slip Tukin Saya') }}
            </x-responsive-nav-link>
            @endif

            <!-- 7: ADMIN -->
            @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
            <div class="px-6 py-3.5 text-[9px] font-black text-sipega-orange bg-orange-50 uppercase tracking-[0.2em] flex items-center justify-between mt-3 border-y border-orange-200/60">
                <span class="flex items-center gap-1.5">
                    <span>⚙️</span> ADMIN
                </span>
                <span class="text-[7px] bg-sipega-orange text-white px-2 py-0.5 rounded font-black">MANAGEMENT</span>
            </div>
            @if(in_array(auth()->user()->role, ['Pimpinan', 'Admin']))
            <x-responsive-nav-link :href="route('pimpinan.auto_evaluation.index')" :active="request()->routeIs('pimpinan.auto_evaluation.*')" class="text-xs font-black py-2.5 ps-8 text-amber-900 bg-amber-50 flex items-center justify-between">
                <span>⚡ {{ __('Otomatisasi Penilaian Pegawai (Jam 24:00)') }}</span>
                <span class="text-[7px] bg-red-600 text-white px-1.5 py-0.5 rounded font-black">AUTO</span>
            </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('admin.attendance.manage')" :active="request()->routeIs('admin.attendance.*')" class="text-xs font-bold py-2.5 ps-8 text-sipega-navy">
                {{ __('Kelola Kehadiran Pegawai') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('attendance.import.index')" :active="request()->routeIs('attendance.import.*')" class="text-xs font-bold py-2.5 ps-8 text-emerald-700">
                {{ __('Import Log Kehadiran (SIPEGA-Check)') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('performance.recap')" :active="request()->routeIs('performance.*')" class="text-xs font-bold py-2.5 ps-8 text-emerald-700">
                {{ __('Raport Capaian Pegawai') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('duty-simulation.index')" :active="request()->routeIs('duty-simulation.*')" class="text-xs font-bold py-2.5 ps-8 text-sipega-orange">
                {{ __('Simulasi Surat Tugas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')" class="text-xs font-medium py-2.5 ps-8 text-sipega-navy">
                {{ __('Data Pegawai (NIP & Satker)') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leader.agenda.index')" :active="request()->routeIs('leader.agenda.index')" class="text-xs font-medium py-2.5 ps-8 text-sipega-navy">
                {{ __('Monitoring & Penilaian') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('travel.recap')" :active="request()->routeIs('travel.recap')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Rekap Dinas Luar') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.calendar.index')" :active="request()->routeIs('admin.calendar.index')" class="text-xs font-medium py-2.5 ps-8">
                {{ __('Kalender Kerja Dinas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*', 'smartpengaturan')" class="text-xs font-bold py-2.5 ps-8 text-amber-700 bg-amber-50/50">
                {{ __('⚙️ Smart Pengaturan') }}
            </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
