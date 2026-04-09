<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center whitespace-nowrap">
            <div>
                <h2 class="font-extrabold text-2xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Pusat Administrasi
                </h2>
                <p class="text-[10px] font-bold text-sipega-orange uppercase tracking-[0.3em] mt-1">{{ auth()->user()->name }} &bull; SIPEGA ENGINE</p>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Master Switch Reward -->
                <form action="{{ route('settings.update') }}" method="POST" class="flex items-center bg-gray-50 px-4 py-2 rounded-2xl border border-gray-200">
                    @csrf
                    <input type="hidden" name="key" value="is_reward_active">
                    <label class="text-[10px] font-black mr-3 text-gray-400 uppercase tracking-widest">SIPEGA Reward:</label>
                    <select name="value" onchange="this.form.submit()" class="text-[10px] font-extrabold py-1 px-4 rounded-xl border-none focus:ring-0 {{ \App\Models\Setting::get('is_reward_active') === '1' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        <option value="1" {{ \App\Models\Setting::get('is_reward_active') === '1' ? 'selected' : '' }}>AKTIF</option>
                        <option value="0" {{ \App\Models\Setting::get('is_reward_active') === '0' ? 'selected' : '' }}>OFF</option>
                    </select>
                </form>

                <!-- Master Switch Realisasi (Flexible for Testing) -->
                <form action="{{ route('settings.update') }}" method="POST" class="flex items-center bg-gray-50 px-4 py-2 rounded-2xl border border-gray-200">
                    @csrf
                    <input type="hidden" name="key" value="is_realization_open_anytime">
                    <label class="text-[10px] font-black mr-3 text-gray-400 uppercase tracking-widest leading-none">Uji Coba<br>Realisasi:</label>
                    <select name="value" onchange="this.form.submit()" class="text-[10px] font-extrabold py-1 px-4 rounded-xl border-none focus:ring-0 {{ \App\Models\Setting::get('is_realization_open_anytime') === '1' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        <option value="1" {{ \App\Models\Setting::get('is_realization_open_anytime') === '1' ? 'selected' : '' }}>OPEN (TEST)</option>
                        <option value="0" {{ \App\Models\Setting::get('is_realization_open_anytime', '0') === '0' ? 'selected' : '' }}>NORMAL (JADWAL)</option>
                    </select>
                </form>

                <!-- Master Switch Tukin -->
                <form action="{{ route('settings.update') }}" method="POST" class="flex items-center bg-gray-50 px-4 py-2 rounded-2xl border border-gray-200">
                    @csrf
                    <input type="hidden" name="key" value="is_tukin_active">
                    <label class="text-[10px] font-black mr-3 text-gray-400 uppercase tracking-widest">SIPEGA Tukin:</label>
                    <select name="value" onchange="this.form.submit()" class="text-[10px] font-extrabold py-1 px-4 rounded-xl border-none focus:ring-0 {{ \App\Models\Setting::get('is_tukin_active') !== '0' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        <option value="1" {{ \App\Models\Setting::get('is_tukin_active') !== '0' ? 'selected' : '' }}>AKTIF</option>
                        <option value="0" {{ \App\Models\Setting::get('is_tukin_active') === '0' ? 'selected' : '' }}>OFF</option>
                    </select>
                </form>

                <div class="flex gap-2">
                    @if(\App\Models\Setting::get('is_reward_active') === '1')
                    <a href="{{ route('reward.index') }}" class="bg-sipega-orange hover:bg-orange-600 text-white font-bold py-2.5 px-6 rounded-2xl shadow-lg transition-all hover:-translate-y-0.5 whitespace-nowrap overflow-hidden text-xs uppercase tracking-widest">
                        Reward Center
                    </a>
                    @endif
                    <a href="{{ route('rbac.index') }}" class="bg-sipega-navy hover:bg-[#002244] text-white font-bold py-2.5 px-6 rounded-2xl shadow-lg transition-all hover:-translate-y-0.5 whitespace-nowrap overflow-hidden text-xs uppercase tracking-widest border border-white/10">
                        Matriks Akses
                    </a>
                    <a href="{{ route('users.index') }}" class="bg-gray-800 hover:bg-black text-white font-bold py-2.5 px-6 rounded-2xl shadow-lg transition-all hover:-translate-y-0.5 whitespace-nowrap overflow-hidden text-xs uppercase tracking-widest">
                        Pegawai
                    </a>
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
                            <button class="bg-sipega-orange hover:bg-orange-600 text-white font-black py-4 px-8 rounded-2xl shadow-xl transition-all hover:-translate-y-1 hover:shadow-sipega-orange/20 uppercase text-xs tracking-widest">
                                Validasi Lupa Absen
                            </button>
                            <form action="{{ route('users.recalculate-performance') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-sipega-navy hover:bg-[#002244] text-white font-black py-4 px-8 rounded-2xl shadow-xl transition-all hover:-translate-y-1 hover:shadow-sipega-navy/20 uppercase text-xs tracking-widest flex items-center justify-center gap-3">
                                    Kalkulasi Bobot Skor
                                </button>
                            </form>
                            @if(\App\Models\Setting::get('is_tukin_active') !== '0')
                            <a href="{{ route('tukin.export') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl transition-all hover:-translate-y-1 hover:shadow-emerald-600/20 uppercase text-xs tracking-widest flex items-center justify-center gap-3">
                                Export Rekap Tukin
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

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

            <!-- Import Section -->
            <div class="bg-white overflow-hidden shadow-sm rounded-[40px] p-8 lg:p-12 border border-emerald-100">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
                    <div>
                        <h3 class="text-3xl font-black text-emerald-700 tracking-tighter uppercase mb-2">SIPEGA-Check</h3>
                        <p class="text-gray-400 font-medium italic">Upload file <b>Laporan Log Transaksi</b> (Excel) dari mesin absensi. Sistem akan mencocokkan NIP dan menghitung TL/PSW secara otomatis.</p>
                    </div>

                    <form action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row items-center gap-4 bg-emerald-50/50 p-4 rounded-3xl border border-emerald-50">
                        @csrf
                        <input type="file" name="excel_file" accept=".xlsx,.csv,.xls" required class="block w-full text-xs text-emerald-800 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-emerald-600 file:text-white file:uppercase file:tracking-widest cursor-pointer"/>
                        <button type="submit" class="w-full md:w-auto bg-emerald-700 hover:bg-emerald-800 text-white font-black py-4 px-8 rounded-2xl transition-all shadow-lg text-[10px] uppercase tracking-widest">
                            Sync Data
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
