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

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-[10px] font-black uppercase tracking-[0.2em] px-4 text-white/70 hover:text-white transition-all">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('documents.index')" :active="request()->routeIs('documents.index')" class="text-[10px] font-black uppercase tracking-[0.2em] px-4 text-white/70 hover:text-white transition-all">
                        {{ __('Dokumen Fisik') }}
                    </x-nav-link>

                    <!-- Group 1: KINERJA -->
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-4 py-2 text-[10px] font-black leading-5 text-white/60 hover:text-white focus:outline-none transition duration-200 ease-in-out uppercase tracking-[0.2em] {{ request()->routeIs('agenda.*', 'evidence.*', 'schedules.*') ? 'text-white bg-white/5 rounded-full' : '' }}">
                                <span>Kinerja</span>
                                <svg class="ms-1.5 h-3 w-3 opacity-40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-2 text-[8px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 mb-1">Manajemen Output</div>
                            <x-dropdown-link :href="route('agenda.index')" class="text-[11px] font-bold py-3">Agenda & Realisasi</x-dropdown-link>
                            <x-dropdown-link :href="route('evidence.index')" class="text-[11px] font-bold py-3 text-sipega-navy bg-sipega-orange/5">Bukti Fisik (SKP)</x-dropdown-link>
                            <x-dropdown-link :href="route('schedules.index')" class="text-[11px] font-bold py-3">Agenda Individu</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <!-- Group 2: PRESENSI -->
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-4 py-2 text-[10px] font-black leading-5 text-white/60 hover:text-white focus:outline-none transition duration-200 ease-in-out uppercase tracking-[0.2em] {{ request()->routeIs('attendance.*', 'letters.*', 'travel-reports.*') ? 'text-white bg-white/5 rounded-full' : '' }}">
                                <span>Layanan</span>
                                <svg class="ms-1.5 h-3 w-3 opacity-40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-2 text-[8px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 mb-1">Administrasi Pegawai</div>
                            <x-dropdown-link :href="route('attendance.recap')" class="text-[11px] font-bold py-3 text-sipega-navy bg-blue-50/60 hover:bg-blue-100/60 flex items-center justify-between">
                                <span>Kehadiran Saya</span>
                                <span class="text-[8px] bg-blue-600 text-white px-2 py-0.5 rounded-full font-black">LOG</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('attendance.index')" class="text-[11px] font-bold py-3">Presensi Rapat / Kegiatan</x-dropdown-link>
                            @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator']))
                            <x-dropdown-link :href="route('admin.attendance.manage')" class="text-[11px] font-bold py-3 text-sipega-navy bg-amber-50/60 hover:bg-amber-100/60 flex items-center justify-between">
                                <span>Kelola Kehadiran & Keterangan</span>
                                <span class="text-[8px] bg-sipega-orange text-white px-2 py-0.5 rounded-full font-black">ADMIN</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('attendance.import.index')" class="text-[11px] font-bold py-3 text-emerald-800 bg-emerald-50/70 hover:bg-emerald-100 flex items-center justify-between">
                                <span>Import Log Daftar Hadir</span>
                                <span class="text-[8px] bg-emerald-600 text-white px-2 py-0.5 rounded-full font-black">LOG</span>
                            </x-dropdown-link>
                            @endif
                            <x-dropdown-link :href="route('letters.index')" class="text-[11px] font-bold py-3">SK & Surat Tugas</x-dropdown-link>
                            <x-dropdown-link :href="route('travel-reports.index')" class="text-[11px] font-bold py-3 text-sipega-navy bg-sipega-orange/5 flex items-center justify-between">
                                <span>Laporan Perjalanan Dinas</span>
                                <span class="text-[8px] bg-sipega-orange text-white px-2 py-0.5 rounded-full font-black">LAPORAN</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('qrcode.index')" class="text-[11px] font-bold py-3 border-t border-gray-50 bg-slate-50">Pembuat QR Code</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    @if(auth()->user() && \App\Http\Controllers\TukinController::isPrivilegedUser(auth()->user()))
                    <x-nav-link :href="route('tukin.index')" :active="request()->routeIs('tukin.*')" class="text-[10px] font-black uppercase tracking-[0.2em] px-4 text-emerald-300 hover:text-white transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span>{{ __('Hitung Tukin') }}</span>
                    </x-nav-link>
                    @else
                    <x-nav-link :href="route('tukin.index')" :active="request()->routeIs('tukin.*')" class="text-[10px] font-black uppercase tracking-[0.2em] px-4 text-white/70 hover:text-white transition-all">
                        {{ __('Slip Tukin') }}
                    </x-nav-link>
                    @endif

                    @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
                    <x-nav-link :href="route('letters.index')" :active="request()->routeIs('letters.*')" class="text-[10px] font-black uppercase tracking-[0.2em] px-4 text-sipega-orange hover:text-white transition-all">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            {{ __('Surat Tugas') }}
                        </span>
                    </x-nav-link>
                    @endif

                    @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
                    <!-- Group 3: ADMINISTRASI -->
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-4 py-2 text-[10px] font-black leading-5 text-sipega-orange hover:text-white focus:outline-none transition duration-200 ease-in-out uppercase tracking-[0.2em] {{ request()->routeIs('leader.*', 'travel.*', 'admin.calendar.*', 'duty-simulation.*', 'performance.*', 'attendance.import.*', 'admin.attendance.*') ? 'text-white bg-sipega-orange rounded-full' : '' }}">
                                <span>Console</span>
                                <svg class="ms-1.5 h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-2 text-[8px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 mb-1">Peralatan Manajemen</div>
                            <x-dropdown-link :href="route('tukin.index')" class="text-[11px] font-bold py-3 text-emerald-900 bg-emerald-50/80 hover:bg-emerald-100 flex items-center justify-between border-b border-gray-50">
                                <span>Hitung Tukin (14/2025)</span>
                                <span class="text-[8px] bg-emerald-600 text-white px-2 py-0.5 rounded-full font-black">EXCEL</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.attendance.manage')" class="text-[11px] font-bold py-3 text-sipega-navy bg-orange-50/70 hover:bg-orange-100/70 flex items-center justify-between border-b border-gray-50">
                                <span>Kelola Kehadiran Pegawai</span>
                                <span class="text-[8px] bg-sipega-orange text-white px-2 py-0.5 rounded-full font-black">KELOLA</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('attendance.import.index')" class="text-[11px] font-bold py-3 text-emerald-800 bg-emerald-50/70 hover:bg-emerald-100 flex items-center justify-between border-b border-gray-50">
                                <span>Import Log Daftar Hadir</span>
                                <span class="text-[8px] bg-emerald-600 text-white px-2 py-0.5 rounded-full font-black">IMPORT</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('performance.recap')" class="text-[11px] font-bold py-3 text-emerald-800 bg-emerald-50/70 hover:bg-emerald-100 flex items-center justify-between border-b border-gray-50">
                                <span>Raport Capaian Pegawai</span>
                                <span class="text-[8px] bg-emerald-600 text-white px-2 py-0.5 rounded-full font-black">SKOR</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('duty-simulation.index')" class="text-[11px] font-bold py-3 text-sipega-navy bg-orange-50/60 hover:bg-orange-100/60 flex items-center justify-between border-b border-gray-50">
                                <span>Simulasi Surat Tugas</span>
                                <span class="text-[8px] bg-sipega-orange text-white px-2 py-0.5 rounded-full font-black">SIMULASI</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('users.index')" class="text-[11px] font-bold py-3 text-sipega-navy hover:bg-orange-50/50 flex items-center justify-between">
                                <span>Data Pegawai</span>
                                <span class="text-[8px] bg-sipega-navy text-white px-2 py-0.5 rounded-full font-black">NIP</span>
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('leader.agenda.index')" class="text-[11px] font-bold py-3">Monitoring & Penilaian</x-dropdown-link>
                            <x-dropdown-link :href="route('travel.recap')" class="text-[11px] font-bold py-3">Rekap Dinas Luar</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.calendar.index')" class="text-[11px] font-bold py-3 border-t border-gray-50">Kalender Kerja</x-dropdown-link>
                            <x-dropdown-link :href="route('settings.index')" class="text-[11px] font-bold py-3 text-sipega-navy bg-amber-50/70 hover:bg-amber-100 flex items-center justify-between border-t border-amber-200/60">
                                <span>Pengaturan Aturan & Sistem</span>
                                <span class="text-[8px] bg-amber-500 text-white px-2 py-0.5 rounded-full font-black">⚙️ ATURAN</span>
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
                            {{ __('Pengaturan Aturan & Sistem') }}
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
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="font-black uppercase tracking-widest text-[11px]">
                {{ __('Beranda') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('documents.index')" :active="request()->routeIs('documents.index')" class="font-black uppercase tracking-widest text-[11px] text-sipega-orange">
                {{ __('Pusat Dokumen') }}
            </x-responsive-nav-link>

            <div class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-50 flex items-center gap-3 mt-4">
                <span class="w-1.5 h-1.5 bg-sipega-navy rounded-full"></span> KINERJA PEGAWAI
            </div>
            <x-responsive-nav-link :href="route('agenda.index')" :active="request()->routeIs('agenda.index')" class="text-xs font-medium py-3 ps-8">
                {{ __('Agenda & Realisasi') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('evidence.index')" :active="request()->routeIs('evidence.*')" class="text-xs font-medium py-3 ps-8">
                {{ __('Bukti Fisik (SKP)') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('schedules.index')" :active="request()->routeIs('schedules.index')" class="text-xs font-medium py-3 ps-8">
                {{ __('Agenda Individu') }}
            </x-responsive-nav-link>

            <div class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-50 flex items-center gap-3 mt-2">
                <span class="w-1.5 h-1.5 bg-sipega-navy rounded-full"></span> LAYANAN & ABSENSI
            </div>
            <x-responsive-nav-link :href="route('attendance.recap')" :active="request()->routeIs('attendance.recap')" class="text-xs font-bold py-3 ps-8 text-blue-700 bg-blue-50/50">
                {{ __('Kehadiran Saya (Log Mesin)') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.index')" class="text-xs font-medium py-3 ps-8">
                {{ __('Presensi Rapat / Kegiatan') }}
            </x-responsive-nav-link>
            @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator']))
            <x-responsive-nav-link :href="route('admin.attendance.manage')" :active="request()->routeIs('admin.attendance.*')" class="text-xs font-bold py-3 ps-8 text-sipega-orange bg-orange-50/50">
                {{ __('Kelola Kehadiran & Keterangan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('attendance.import.index')" :active="request()->routeIs('attendance.import.*')" class="text-xs font-bold py-3 ps-8 text-emerald-700 bg-emerald-50/50">
                {{ __('Import Log Daftar Hadir') }}
            </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('letters.index')" :active="request()->routeIs('letters.*')" class="text-xs font-medium py-3 ps-8">
                {{ __('SK & Surat Tugas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('travel-reports.index')" :active="request()->routeIs('travel-reports.*')" class="text-xs font-semibold py-3 ps-8 text-sipega-orange">
                {{ __('Laporan Perjalanan Dinas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('qrcode.index')" :active="request()->routeIs('qrcode.*')" class="text-xs font-medium py-3 ps-8 bg-slate-50">
                {{ __('Pembuat QR Code') }}
            </x-responsive-nav-link>

            @if(auth()->user() && \App\Http\Controllers\TukinController::isPrivilegedUser(auth()->user()))
            <x-responsive-nav-link :href="route('tukin.index')" :active="request()->routeIs('tukin.*')" class="text-xs font-bold py-3 ps-8 text-emerald-800 bg-emerald-50/70 flex items-center justify-between">
                <span>{{ __('Hitung Tukin Pegawai (Permendikdasmen 14/2025)') }}</span>
                <span class="text-[8px] bg-emerald-600 text-white px-2 py-0.5 rounded font-black">EXCEL</span>
            </x-responsive-nav-link>
            @else
            <x-responsive-nav-link :href="route('tukin.index')" :active="request()->routeIs('tukin.*')" class="text-xs font-medium py-3 ps-8">
                {{ __('Slip Tukin Saya') }}
            </x-responsive-nav-link>
            @endif

            @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
            <div class="px-6 py-4 text-[10px] font-bold text-sipega-navy bg-slate-50 uppercase tracking-[0.2em] flex items-center justify-between mt-6 border-y border-gray-100">
                <span>DASHBOARD MANAJEMEN</span>
                <span class="text-[7px] bg-sipega-navy text-white px-2 py-0.5 rounded tracking-widest">ADMIN</span>
            </div>
            <x-responsive-nav-link :href="route('admin.attendance.manage')" :active="request()->routeIs('admin.attendance.*')" class="text-xs font-bold py-3 ps-8 text-sipega-orange bg-orange-50/50">
                {{ __('Kelola Kehadiran Pegawai') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('attendance.import.index')" :active="request()->routeIs('attendance.import.*')" class="text-xs font-bold py-3 ps-8 text-emerald-700 bg-emerald-50/50">
                {{ __('Import Log Kehadiran') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('performance.recap')" :active="request()->routeIs('performance.*')" class="text-xs font-bold py-3 ps-8 text-emerald-700 bg-emerald-50/50">
                {{ __('Raport Capaian Pegawai') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('duty-simulation.index')" :active="request()->routeIs('duty-simulation.*')" class="text-xs font-bold py-3 ps-8 text-sipega-orange bg-orange-50/50">
                {{ __('Simulasi Surat Tugas') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')" class="text-xs font-medium py-3 ps-8 text-sipega-navy">
                {{ __('Data Pegawai') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leader.agenda.index')" :active="request()->routeIs('leader.agenda.index')" class="text-xs font-medium py-3 ps-8 text-sipega-navy">
                {{ __('Monitoring & Penilaian') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('travel.recap')" :active="request()->routeIs('travel.recap')" class="text-xs font-medium py-3 ps-8">
                {{ __('Rekap Dinas Luar') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.calendar.index')" :active="request()->routeIs('admin.calendar.index')" class="text-xs font-medium py-3 ps-8">
                {{ __('Kalender Kerja') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')" class="text-xs font-bold py-3 ps-8 text-amber-700 bg-amber-50/50">
                {{ __('⚙️ Pengaturan Aturan & Sistem') }}
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
