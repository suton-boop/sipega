<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center whitespace-nowrap">
            <div>
                <h2 class="font-extrabold text-2xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Pusat Administrasi
                </h2>
                <p class="text-[10px] font-bold text-sipega-orange uppercase tracking-[0.3em] mt-1">{{ auth()->user()->name }} &bull; SIPEGA ENGINE</p>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Smart Pengaturan Dropdown Menu (Semua Menu Administrasi & Master Switch Disatukan di Sini) -->
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" 
                            type="button" 
                            style="background-color: #003366; color: #ffffff;"
                            class="inline-flex items-center gap-2.5 px-5 py-2.5 bg-sipega-navy hover:bg-[#002244] text-white text-xs font-black rounded-2xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5 uppercase tracking-wider cursor-pointer border border-white/20">
                        <span class="text-sm">⚙️</span>
                        <span class="font-black text-white tracking-wider">Smart Pengaturan</span>
                        <span class="w-2.5 h-2.5 rounded-full {{ \App\Models\Setting::get('is_tukin_active') !== '0' && \App\Models\Setting::get('is_reward_active') === '1' ? 'bg-emerald-400' : 'bg-amber-400' }} animate-pulse"></span>
                        <svg class="w-3.5 h-3.5 text-white/80 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <!-- Dropdown Content Panel -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         class="absolute right-0 mt-2 w-96 bg-white rounded-3xl shadow-2xl border border-gray-200 p-5 z-50 text-left whitespace-normal space-y-4"
                         style="display: none; min-width: 22rem;">
                        
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-wider text-sipega-navy bg-slate-100 px-2.5 py-0.5 rounded-full">PUSAT KENDALI ADMINISTRATOR</span>
                                <h4 class="text-xs font-black text-sipega-navy mt-1">Smart Pengaturan & Menu Sistem</h4>
                            </div>
                            <a href="{{ route('settings.index') }}" class="text-[10px] font-black text-sipega-orange hover:text-orange-600 transition flex items-center gap-1 uppercase tracking-wider">
                                <span>Pusat Regulasi</span>
                                <span>&rarr;</span>
                            </a>
                        </div>

                        <!-- MENU-MENU UTAMA (PINDAHAN DARI HEADER) -->
                        <div class="space-y-2">
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Menu Peralatan Utama</span>

                            @if(\App\Models\Setting::get('is_reward_active') === '1')
                            <a href="{{ route('reward.index') }}" class="flex items-center justify-between p-3 rounded-2xl bg-orange-50 hover:bg-orange-100 text-orange-950 transition border border-orange-200/80 group">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">🏆</span>
                                    <div>
                                        <span class="text-xs font-black text-sipega-navy block leading-tight group-hover:text-sipega-orange transition-colors">Reward Center</span>
                                        <span class="text-[9px] text-gray-500 font-medium">Wall of Fame & ASN BerAKHLAK</span>
                                    </div>
                                </div>
                                <span class="text-[9px] bg-sipega-orange text-white px-2.5 py-1 rounded-full font-black uppercase tracking-wider shadow-sm">Buka</span>
                            </a>
                            @endif

                            <a href="{{ route('rbac.index') }}" class="flex items-center justify-between p-3 rounded-2xl bg-blue-50/70 hover:bg-blue-100 text-sipega-navy transition border border-blue-200/70 group">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">🛡️</span>
                                    <div>
                                        <span class="text-xs font-black text-sipega-navy block leading-tight">Matriks Hak Akses (RBAC)</span>
                                        <span class="text-[9px] text-gray-500 font-medium">Konfigurasi izin, role & modul pengguna</span>
                                    </div>
                                </div>
                                <span class="text-[9px] bg-sipega-navy text-white px-2.5 py-1 rounded-full font-black uppercase tracking-wider shadow-sm">Akses</span>
                            </a>

                            <a href="{{ route('users.index') }}" class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 hover:bg-slate-100 text-gray-800 transition border border-gray-200 group">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">👥</span>
                                    <div>
                                        <span class="text-xs font-black text-sipega-navy block leading-tight">Kelola Data Pegawai</span>
                                        <span class="text-[9px] text-gray-500 font-medium">Database ASN, Grade Tukin (KJ 1-17), NIP</span>
                                    </div>
                                </div>
                                <span class="text-[9px] bg-gray-800 text-white px-2.5 py-1 rounded-full font-black uppercase tracking-wider shadow-sm">NIP</span>
                            </a>
                        </div>

                        <!-- MASTER SAKLAR SISTEM (QUICK TOGGLES) -->
                        <div class="space-y-2 pt-2 border-t border-gray-100">
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block">Master Switch Sistem</span>

                            <!-- 1. Master Switch Tukin -->
                            <form action="{{ route('settings.update') }}" method="POST" class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-50 hover:bg-slate-100/80 transition border border-gray-100">
                                @csrf
                                <input type="hidden" name="key" value="is_tukin_active">
                                <div class="pr-2">
                                    <label class="text-xs font-black text-sipega-navy block leading-tight">SIPEGA Tukin</label>
                                    <span class="text-[9px] text-gray-400 font-medium">Modul perhitungan tunjangan kinerja</span>
                                </div>
                                <select name="value" onchange="this.form.submit()" class="text-[10px] font-black py-1 px-3 rounded-xl border-none focus:ring-0 cursor-pointer shadow-sm {{ \App\Models\Setting::get('is_tukin_active') !== '0' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    <option value="1" {{ \App\Models\Setting::get('is_tukin_active') !== '0' ? 'selected' : '' }}>AKTIF</option>
                                    <option value="0" {{ \App\Models\Setting::get('is_tukin_active') === '0' ? 'selected' : '' }}>OFF</option>
                                </select>
                            </form>

                            <!-- 2. Master Switch Reward -->
                            <form action="{{ route('settings.update') }}" method="POST" class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-50 hover:bg-slate-100/80 transition border border-gray-100">
                                @csrf
                                <input type="hidden" name="key" value="is_reward_active">
                                <div class="pr-2">
                                    <label class="text-xs font-black text-sipega-navy block leading-tight">SIPEGA Reward</label>
                                    <span class="text-[9px] text-gray-400 font-medium">Wall of Fame & penilaian ASN</span>
                                </div>
                                <select name="value" onchange="this.form.submit()" class="text-[10px] font-black py-1 px-3 rounded-xl border-none focus:ring-0 cursor-pointer shadow-sm {{ \App\Models\Setting::get('is_reward_active') === '1' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    <option value="1" {{ \App\Models\Setting::get('is_reward_active') === '1' ? 'selected' : '' }}>AKTIF</option>
                                    <option value="0" {{ \App\Models\Setting::get('is_reward_active') === '0' ? 'selected' : '' }}>OFF</option>
                                </select>
                            </form>

                            <!-- 3. Master Switch Uji Coba Realisasi -->
                            <form action="{{ route('settings.update') }}" method="POST" class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-50 hover:bg-slate-100/80 transition border border-gray-100">
                                @csrf
                                <input type="hidden" name="key" value="is_realization_open_anytime">
                                <div class="pr-2">
                                    <label class="text-xs font-black text-sipega-navy block leading-tight">Uji Coba Realisasi</label>
                                    <span class="text-[9px] text-gray-400 font-medium">Bypass jam 15:00 untuk simulasi</span>
                                </div>
                                <select name="value" onchange="this.form.submit()" class="text-[10px] font-black py-1 px-3 rounded-xl border-none focus:ring-0 cursor-pointer shadow-sm {{ \App\Models\Setting::get('is_realization_open_anytime') === '1' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    <option value="1" {{ \App\Models\Setting::get('is_realization_open_anytime') === '1' ? 'selected' : '' }}>OPEN (TEST)</option>
                                    <option value="0" {{ \App\Models\Setting::get('is_realization_open_anytime', '0') === '0' ? 'selected' : '' }}>NORMAL (JADWAL)</option>
                                </select>
                            </form>
                        </div>

                        <!-- TAUTAN PUSAT REGULASI LENGKAP -->
                        <div class="pt-2 border-t border-gray-100">
                            <a href="{{ route('settings.index') }}" class="w-full py-3 px-4 bg-sipega-navy hover:bg-[#002244] text-white text-xs font-black rounded-2xl transition flex items-center justify-between shadow-md">
                                <span class="flex items-center gap-2">
                                    <span>⚙️</span>
                                    <span>Buka Smart Pengaturan Regulasi Lengkap</span>
                                </span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen font-sans">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Performance Parameters -->
            <div class="bg-white overflow-hidden shadow-sm rounded-[32px] border-b-8 border-sipega-navy p-2">
                <div class="p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                        <div class="flex-1">
                            <h3 class="text-3xl font-black mb-2 text-sipega-navy tracking-tighter uppercase">Validasi Performa Pegawai</h3>
                            <p class="text-gray-500 font-medium @if(\App\Models\Setting::get('is_tukin_active') === '0') line-through decoration-red-500 @endif mb-6">
                                Sinkronisasi kehadiran harian untuk mematikan akurasi perhitungan Tukin secara otomatis.
                                @if(\App\Models\Setting::get('is_tukin_active') === '0')
                                    <span class="block text-red-600 font-black uppercase text-[10px] mt-1 tracking-widest">[MODUL TUKIN NON-AKTIF]</span>
                                @endif
                            </p>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                                <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                                    <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Pegawai</span>
                                    <span class="text-3xl font-black text-sipega-navy leading-none">{{ $totalUsers }}</span>
                                </div>
                                <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100 italic">
                                    <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 ">Status Hari</span>
                                    <span class="text-xl font-bold text-sipega-orange uppercase tracking-tighter">{{ $today }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3">
                            <a href="{{ route('admin.attendance.manage') }}" class="bg-sipega-orange hover:bg-orange-600 text-white font-black py-4 px-8 rounded-2xl shadow-xl transition-all hover:-translate-y-1 hover:shadow-sipega-orange/20 uppercase text-xs tracking-widest text-center flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Validasi Lupa Absen</span>
                            </a>
                            <form action="{{ route('users.recalculate-performance') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-sipega-navy hover:bg-[#002244] text-white font-black py-4 px-8 rounded-2xl shadow-xl transition-all hover:-translate-y-1 hover:shadow-sipega-navy/20 uppercase text-xs tracking-widest flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Kalkulasi Bobot Skor</span>
                                </button>
                            </form>
                            @if(\App\Models\Setting::get('is_tukin_active') !== '0')
                            <a href="{{ route('tukin.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl transition-all hover:-translate-y-1 hover:shadow-emerald-600/20 uppercase text-xs tracking-widest flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Kelola & Rekap Tukin</span>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- PUSAT UNDUH LAPORAN HARIAN & SLIP TUKIN (FILTER BULAN & TAHUN) -->
            <x-report-slip-picker :all-pegawai="$allPegawai" :current-user-id="auth()->id()" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Leaderboard High -->
                <div class="bg-white overflow-hidden shadow-2xl rounded-[48px] p-10 border border-gray-100 relative">
                    <div class="absolute -top-4 -right-4 bg-sipega-orange text-white w-20 h-20 rounded-full flex flex-col items-center justify-center rotate-12 shadow-xl">
                        <span class="text-[10px] font-black uppercase tracking-widest">Rank</span>
                        <span class="text-2xl font-black">#1</span>
                    </div>
                    <h3 class="text-2xl font-black mb-10 text-sipega-navy uppercase tracking-tighter flex items-center gap-4">
                        <span class="w-2.5 h-10 bg-sipega-orange rounded-full"></span>
                        Wall of Fame
                    </h3>
                    <div class="grid grid-cols-1 gap-6">
                        @foreach($top5Highest as $user)
                        <div class="group relative bg-gray-50 hover:bg-white p-4 rounded-[32px] transition-all duration-500 border border-transparent hover:border-gray-100 hover:shadow-2xl hover:-translate-y-1 flex items-center gap-6">
                            <div class="relative">
                                @if($user->profile_photo_path)
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="w-20 h-20 rounded-[24px] object-cover shadow-lg border-4 {{ $loop->first ? 'border-sipega-orange' : 'border-white' }} group-hover:scale-110 transition-transform duration-500">
                                @else
                                    <div class="w-20 h-20 rounded-[24px] bg-sipega-navy text-white flex items-center justify-center text-2xl font-black shadow-lg border-4 border-white">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                @if($loop->first)
                                    <span class="absolute -top-2 -right-2 bg-sipega-orange text-[8px] font-black text-white px-2 py-1 rounded-lg uppercase tracking-widest animate-bounce">TOP</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black text-gray-800 text-lg leading-none mb-1 group-hover:text-sipega-navy transition-colors">{{ $user->name }}</h4>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $user->position ?? 'Fungsional Umum' }}</p>
                                <div class="mt-3 flex items-center gap-2">
                                    <span class="px-3 py-1 bg-sipega-navy text-white text-[10px] font-black rounded-full shadow-sm">{{ $user->performance_score }} PTS</span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                </div>
                            </div>
                            <div class="text-4xl font-black text-gray-100 group-hover:text-sipega-orange/10 transition-colors">
                                0{{ $loop->iteration }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Leaderboard Low -->
                <div class="bg-gray-900 overflow-hidden shadow-2xl rounded-[48px] p-10 relative">
                    <div class="absolute top-0 right-0 p-8">
                         <div class="w-12 h-12 bg-red-500/20 rounded-2xl flex items-center justify-center animate-pulse">
                             <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                         </div>
                    </div>
                    <h3 class="text-2xl font-black mb-10 text-white uppercase tracking-tighter flex items-center gap-4">
                        <span class="w-2.5 h-10 bg-red-600 rounded-full"></span>
                        Attention Required
                    </h3>
                    <div class="space-y-4">
                        @foreach($top5Lowest as $user)
                        <div class="p-4 flex justify-between items-center group bg-white/5 hover:bg-white/10 rounded-2xl transition-all border border-white/5">
                            <div class="flex items-center gap-4">
                                @if($user->profile_photo_path)
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="w-10 h-10 rounded-xl object-cover grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100 transition-all">
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-gray-800 text-gray-500 flex items-center justify-center text-sm font-black italic">
                                        !
                                    </div>
                                @endif
                                <div>
                                    <span class="block font-bold text-gray-300 group-hover:text-white">{{ $user->name }}</span>
                                    <span class="text-[9px] font-black text-red-500 uppercase tracking-widest">Score: {{ $user->performance_score }}</span>
                                </div>
                            </div>
                            <button class="bg-red-500/10 text-red-500 px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-red-500 hover:text-white transition">Intervensi</button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- 1.5. Agenda Rapat & Pribadi (NEW) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Agenda Rapat (Mentions & Target_type all) -->
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[32px] p-8 border border-gray-100 relative">
                    <div class="absolute top-0 right-0 bg-sipega-orange text-white text-[10px] font-black px-4 py-2 rounded-bl-3xl rounded-tr-[32px] uppercase tracking-widest">
                        Agenda Instansi / Rapat
                    </div>
                    <h3 class="text-xl font-black text-sipega-navy mb-6 flex items-center gap-3">📅 Jadwal Rapat Anda</h3>
                    
                    @if(isset($upcomingMeetings) && $upcomingMeetings->count() > 0)
                        <div class="space-y-4">
                            @foreach($upcomingMeetings as $meeting)
                                <div class="p-4 rounded-3xl bg-blue-50 border border-blue-100 hover:shadow-md transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-black text-blue-900 text-sm">{{ $meeting->title }}</h4>
                                        <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded-full">{{ \Carbon\Carbon::parse($meeting->date)->format('d/m/Y') }}</span>
                                    </div>
                                    <p class="text-xs text-blue-700 font-medium flex items-center gap-2 mb-1">
                                        ⏱️ {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} WITA
                                    </p>
                                    <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest flex items-center gap-1">
                                        📍 {{ $meeting->location_name }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center p-6 text-gray-400 bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                            <span class="text-4xl mb-2 opacity-50">🏝️</span>
                            <p class="text-xs font-bold uppercase tracking-widest text-center">Belum ada agenda rapat terdekat.</p>
                        </div>
                    @endif
                </div>

                <!-- Agenda Pribadi (Daily Agenda) -->
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[32px] p-8 border border-gray-100 relative">
                    <div class="absolute top-0 right-0 bg-sipega-navy text-white text-[10px] font-black px-4 py-2 rounded-bl-3xl rounded-tr-[32px] uppercase tracking-widest">
                        Rencana / Agenda Pribadi
                    </div>
                    <h3 class="text-xl font-black text-sipega-navy mb-6 flex items-center gap-3">📝 Agenda Hari Ini</h3>
                    
                    @if(isset($todayAgenda) && $todayAgenda && $todayAgenda->items->count() > 0)
                        <div class="space-y-3">
                            @foreach($todayAgenda->items as $idx => $item)
                                <div class="flex items-start gap-3 p-3 rounded-2xl bg-gray-50 border border-gray-100">
                                    <div class="w-6 h-6 rounded-full {{ $item->status == 'completed' ? 'bg-green-100 text-green-600' : ($item->status == 'progress' ? 'bg-orange-100 text-orange-600' : 'bg-gray-200 text-gray-500') }} flex justify-center items-center text-[10px] font-black shrink-0 mt-0.5">
                                        {{ $idx + 1 }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 leading-tight">{{ $item->plan_description }}</p>
                                        <span class="text-[9px] font-black uppercase tracking-widest {{ $item->status == 'completed' ? 'text-green-500' : ($item->status == 'progress' ? 'text-orange-500' : 'text-gray-400') }}">
                                            Status: {{ ucfirst($item->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center p-6 text-gray-400 bg-red-50 rounded-3xl border border-dashed border-red-200">
                            <span class="text-4xl mb-2 opacity-50">⚠️</span>
                            <p class="text-xs font-bold uppercase tracking-widest text-red-500 text-center">Anda belum menyusun agenda pribadi hari ini. Segera buat prioritas Kerja Anda.</p>
                            <a href="{{ route('agenda.index') }}" class="mt-4 bg-red-500 text-white px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition">Buat Agenda</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Assignment Section -->
            <div class="bg-white overflow-hidden shadow-sm rounded-[40px] border border-gray-100">
                <div class="p-8 lg:p-12">
                    <div class="mb-10">
                        <h3 class="text-4xl font-black text-sipega-navy tracking-tighter uppercase mb-2">SIPEGA-Assign</h3>
                        <p class="text-gray-400 font-medium">Penerbitan Surat Tugas (ST) dengan deteksi bentrok jadwal otomatis.</p>
                    </div>
                    
                    @if(session('success'))
                        <div class="bg-green-50 text-green-700 p-6 rounded-3xl mb-8 font-bold border border-green-100 flex items-center gap-3">
                            <span class="bg-green-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs">✓</span>
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('assign.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        @csrf
                        <div class="lg:col-span-12 space-y-8">
                             <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-2">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-2">Keterangan / Keperluan Dinas</label>
                                    <input type="text" name="title" required class="w-full rounded-2xl border-gray-100 bg-gray-50 focus:bg-white focus:border-sipega-navy focus:ring-4 focus:ring-sipega-navy/5 p-4 font-bold text-gray-700" placeholder="Rapat Koordinasi...">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-2">Tanggal</label>
                                        <input type="date" name="date" required class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 font-bold text-gray-700">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2 px-2">Tipe</label>
                                        <select name="type" class="w-full rounded-2xl border-gray-100 bg-gray-50 p-4 font-bold text-gray-700">
                                            <option value="Individu">Perorangan</option>
                                            <option value="Kolektif">Kolektif</option>
                                        </select>
                                    </div>
                                </div>
                             </div>

                             <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                                <div>
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-3 px-2">Pilih Personil Pelaksana</label>
                                    <div class="bg-white border-2 border-dashed border-gray-100 rounded-3xl p-4 h-64 overflow-y-auto space-y-2">
                                        @foreach($allPegawai as $p)
                                            <label class="flex items-center gap-4 p-4 hover:bg-gray-50 rounded-2xl transition cursor-pointer border border-transparent hover:border-gray-100">
                                                <input type="checkbox" name="assigned_users[]" value="{{ $p->id }}" class="rounded-lg border-gray-300 text-sipega-navy focus:ring-sipega-navy w-6 h-6">
                                                <div>
                                                    <span class="block font-bold text-gray-800 leading-none mb-1">{{ $p->name }}</span>
                                                    <span class="text-[10px] uppercase font-black text-{{ strtolower($p->performance_color) }}-600">{{ $p->performance_color }} Performance</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex flex-col">
                                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-3 px-2">Justifikasi Pimpinan</label>
                                    <textarea name="justification" rows="5" class="w-full rounded-3xl border-gray-100 bg-gray-50 p-6 font-medium text-gray-700 flex-grow" placeholder="Catatan khusus atau instruksi pimpinan..."></textarea>
                                    
                                    <div class="mt-6 flex items-center gap-4 bg-red-50 p-4 rounded-2xl border border-red-100">
                                        <input type="checkbox" name="is_private" value="1" class="rounded-lg text-red-600 focus:ring-red-500 w-6 h-6">
                                        <label class="font-black text-red-800 uppercase text-[10px] tracking-widest">Tandai Sebagai "Dinas Khusus" (Tertutup)</label>
                                    </div>
                                </div>
                             </div>

                             <button type="submit" class="w-full bg-sipega-navy hover:bg-black text-white font-black py-6 rounded-3xl shadow-2xl transition-all hover:-translate-y-1 hover:shadow-sipega-navy/30 uppercase tracking-[0.2em]">
                                Terbitkan Surat Tugas Resmi
                             </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Import Section: SIPEGA-Check -->
            <div class="relative bg-gradient-to-br from-white via-white to-emerald-50/40 rounded-3xl lg:rounded-[36px] p-6 lg:p-8 shadow-xl border border-emerald-100/80 overflow-hidden hover:shadow-2xl transition-all duration-300">
                <!-- Subtle decorative background glow -->
                <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-teal-400/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6 lg:gap-8">
                    <!-- Left: Info & Description -->
                    <div class="space-y-3 max-w-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-black shadow-lg shadow-emerald-600/25 text-xl shrink-0">
                                ⚡
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-2xl font-black text-slate-800 tracking-tight leading-none">
                                        SIPEGA<span class="text-emerald-600 font-extrabold italic ml-1">CHECK</span>
                                    </h3>
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Presensi Log Sync
                                    </span>
                                </div>
                                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mt-1">
                                    Sinkronisasi Otomatis Mesin & Portal Kehadiran
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 font-medium leading-relaxed">
                            Upload file <strong class="text-slate-800">Laporan Log Transaksi</strong> (<code class="text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-mono text-[11px]">.html</code>, <code class="text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-mono text-[11px]">.xlsx</code>, <code class="text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded font-mono text-[11px]">.csv</code>) dari portal Kehadiran Kemendikdasmen atau mesin absensi. Sistem akan mencocokkan NIP dan menghitung TL/PSW secara otomatis.
                        </p>

                        <div class="pt-1">
                            <a href="{{ route('attendance.import.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100/80 px-4 py-2 rounded-xl border border-emerald-200/60 transition group">
                                <span>Buka Menu Import Log Lengkap (Form & Tempel Teks)</span>
                                <svg class="w-3.5 h-3.5 text-emerald-600 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Right: Elegant Upload Form with Interactive File Name Indicator -->
                    <div class="lg:w-[480px] shrink-0" x-data="{ fileName: '', fileSize: '', isDragging: false }">
                        <form action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            
                            <!-- Custom File Select Area -->
                            <div class="relative rounded-2xl border-2 border-dashed p-4 transition-all duration-200 bg-white/90 backdrop-blur-sm"
                                 :class="isDragging ? 'border-emerald-500 bg-emerald-50/60 ring-2 ring-emerald-500/20' : (fileName ? 'border-emerald-500 bg-emerald-50/40' : 'border-slate-200 hover:border-emerald-400')">
                                
                                <input type="file" 
                                       name="excel_file" 
                                       id="sipega_check_file"
                                       accept=".html,.htm,.xlsx,.csv,.xls,.txt" 
                                       required 
                                       @change="if ($event.target.files.length) { fileName = $event.target.files[0].name; fileSize = ($event.target.files[0].size / 1024).toFixed(1) + ' KB'; } else { fileName = ''; fileSize = ''; }"
                                       @dragover="isDragging = true"
                                       @dragleave="isDragging = false"
                                       @drop="isDragging = false"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"/>

                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition"
                                             :class="fileName ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-slate-100 text-slate-500'">
                                            <template x-if="fileName">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </template>
                                            <template x-if="!fileName">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            </template>
                                        </div>
                                        <div class="truncate">
                                            <div class="text-xs font-bold text-slate-800 truncate" x-text="fileName ? fileName : 'Pilih file Laporan Log Transaksi'"></div>
                                            <div class="text-[10px] text-slate-400 mt-0.5" x-text="fileName ? 'Ukuran: ' + fileSize : 'Format: .html, .xlsx, .csv, .xls, .txt'"></div>
                                        </div>
                                    </div>

                                    <div class="shrink-0 px-3.5 py-1.5 rounded-xl text-[11px] font-bold transition shadow-sm pointer-events-none"
                                         :class="fileName ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-700 border border-slate-200'">
                                        <span x-text="fileName ? 'Ganti File' : 'Pilih File'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Sync Button -->
                            <div>
                                <button type="submit" 
                                        class="w-full bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 hover:from-emerald-700 hover:to-teal-900 text-white font-black py-3.5 px-6 rounded-2xl shadow-lg shadow-emerald-700/20 hover:shadow-emerald-700/30 transition-all hover:-translate-y-0.5 active:translate-y-0 text-xs uppercase tracking-wider flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Sync Data Log Transaksi</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
