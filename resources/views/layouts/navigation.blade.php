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
                            <button class="inline-flex items-center px-4 py-2 text-[10px] font-black leading-5 text-white/60 hover:text-white focus:outline-none transition duration-200 ease-in-out uppercase tracking-[0.2em] {{ request()->routeIs('attendance.*', 'letters.*') ? 'text-white bg-white/5 rounded-full' : '' }}">
                                <span>Layanan</span>
                                <svg class="ms-1.5 h-3 w-3 opacity-40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-2 text-[8px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 mb-1">Administrasi Pegawai</div>
                            <x-dropdown-link :href="route('attendance.index')" class="text-[11px] font-bold py-3">Daftar Hadir</x-dropdown-link>
                            <x-dropdown-link :href="route('letters.index')" class="text-[11px] font-bold py-3">SK & Surat Tugas</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    @if(\App\Models\Setting::get('is_tukin_active') !== '0')
                    <x-nav-link :href="route('tukin.index')" :active="request()->routeIs('tukin.*')" class="text-[10px] font-black uppercase tracking-[0.2em] px-4 text-white/70 hover:text-white transition-all">
                        {{ __('Tukin') }}
                    </x-nav-link>
                    @endif

                    @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
                    <!-- Group 3: ADMINISTRASI -->
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-4 py-2 text-[10px] font-black leading-5 text-sipega-orange hover:text-white focus:outline-none transition duration-200 ease-in-out uppercase tracking-[0.2em] {{ request()->routeIs('leader.*', 'travel.*', 'admin.calendar.*') ? 'text-white bg-sipega-orange rounded-full' : '' }}">
                                <span>Console</span>
                                <svg class="ms-1.5 h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-2 text-[8px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 mb-1">Peralatan Manajemen</div>
                            <x-dropdown-link :href="route('leader.agenda.index')" class="text-[11px] font-bold py-3">Monitoring & Penilaian</x-dropdown-link>
                            <x-dropdown-link :href="route('travel.recap')" class="text-[11px] font-bold py-3">Rekap Dinas Luar</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.calendar.index')" class="text-[11px] font-bold py-3 border-t border-gray-50">Kalender Kerja</x-dropdown-link>
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
            <x-responsive-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.index')" class="text-xs font-medium py-3 ps-8">
                {{ __('Daftar Hadir') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('letters.index')" :active="request()->routeIs('letters.*')" class="text-xs font-medium py-3 ps-8">
                {{ __('SK & Surat Tugas') }}
            </x-responsive-nav-link>

            @if(\App\Models\Setting::get('is_tukin_active') !== '0')
            <div class="px-6 py-4 text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-50 flex items-center gap-3 mt-2">
                <span class="w-1.5 h-1.5 bg-sipega-navy rounded-full"></span> KEUANGAN
            </div>
            <x-responsive-nav-link :href="route('tukin.index')" :active="request()->routeIs('tukin.*')" class="text-xs font-medium py-3 ps-8">
                {{ __('Laporan Tukin') }}
            </x-responsive-nav-link>
            @endif

            @if(auth()->user() && in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
            <div class="px-6 py-4 text-[10px] font-bold text-sipega-navy bg-slate-50 uppercase tracking-[0.2em] flex items-center justify-between mt-6 border-y border-gray-100">
                <span>DASHBOARD MANAJEMEN</span>
                <span class="text-[7px] bg-sipega-navy text-white px-2 py-0.5 rounded tracking-widest">ADMIN</span>
            </div>
            <x-responsive-nav-link :href="route('leader.agenda.index')" :active="request()->routeIs('leader.agenda.index')" class="text-xs font-medium py-3 ps-8 text-sipega-navy">
                {{ __('Monitoring & Penilaian') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('travel.recap')" :active="request()->routeIs('travel.recap')" class="text-xs font-medium py-3 ps-8">
                {{ __('Rekap Dinas Luar') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.calendar.index')" :active="request()->routeIs('admin.calendar.index')" class="text-xs font-medium py-3 ps-8">
                {{ __('Kalender Kerja') }}
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
