<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-sipega-navy leading-tight">
                {{ __('📊 Dashboard Pimpinan - Monitoring Performa Global') }}
            </h2>
            @if(\App\Models\Setting::get('is_reward_active') === '1')
            <a href="{{ route('reward.index') }}" class="bg-sipega-orange hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-full shadow-md text-sm transition hover:-translate-y-0.5">
                🎁 REWARD CENTER
            </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- 1. PERFORMANCE HEATMAP (Logic Flowchart Monitoring) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Biru -->
                <div class="bg-blue-600 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden group hover:scale-105 transition-transform">
                    <div class="absolute -right-4 -top-4 opacity-20 group-hover:rotate-12 transition">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    </div>
                    <h4 class="font-black text-xs uppercase tracking-widest opacity-80">Score Biru</h4>
                    <p class="text-5xl font-black mt-2">{{ $heatmap['Biru'] ?? 0 }}</p>
                    <p class="text-xs font-bold mt-1 text-blue-100 italic">"Excellent Performance"</p>
                </div>
                <!-- Hijau -->
                <div class="bg-green-600 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden group hover:scale-105 transition-transform">
                    <h4 class="font-black text-xs uppercase tracking-widest opacity-80">Score Hijau</h4>
                    <p class="text-5xl font-black mt-2">{{ $heatmap['Hijau'] ?? 0 }}</p>
                    <p class="text-xs font-bold mt-1 text-green-100 italic">"On Track / Standard"</p>
                </div>
                <!-- Kuning -->
                <div class="bg-yellow-500 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden group hover:scale-105 transition-transform">
                    <h4 class="font-black text-xs uppercase tracking-widest opacity-80 text-yellow-900">Score Kuning</h4>
                    <p class="text-5xl font-black mt-2 text-yellow-900">{{ $heatmap['Kuning'] ?? 0 }}</p>
                    <p class="text-xs font-bold mt-1 text-yellow-800 italic">"Needs Attention"</p>
                </div>
                <!-- Merah -->
                <div class="bg-red-600 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden group hover:scale-105 transition-transform">
                    <h4 class="font-black text-xs uppercase tracking-widest opacity-80">Score Merah</h4>
                    <p class="text-5xl font-black mt-2">{{ $heatmap['Merah'] ?? 0 }}</p>
                    <p class="text-xs font-bold mt-1 text-red-100 italic">"Warning / Critical"</p>
                </div>
            </div>

            <!-- 2. REAL-TIME ACTIVITY: Agenda Monitor -->
            <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border-t-8 border-sipega-navy">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h3 class="text-3xl font-black text-sipega-navy">🕒 Real-time Agenda Monitor</h3>
                            <p class="text-gray-400 font-bold uppercase tracking-widest text-xs mt-1">Status Kehadiran Rencana Kerja Hari Ini ({{ $today }})</p>
                        </div>
                        <div class="text-right">
                            <span class="text-4xl font-black text-sipega-navy">{{ count($submittedToday) }}</span>
                            <span class="text-gray-400 font-bold">/ {{ $totalUsers }} Pegawai</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Sudah Submit -->
                        <div class="bg-green-50 rounded-3xl p-6 border border-green-100 shadow-inner">
                            <h4 class="font-black text-sm text-green-700 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-ping"></span>
                                Telah Tertaut ({{ count($submittedToday) }})
                            </h4>
                            <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                                @forelse($allUsers as $u)
                                    @if(in_array($u->id, $submittedToday))
                                    <div class="flex items-center gap-3 bg-white p-3 rounded-2xl shadow-sm border border-green-200">
                                        <div class="w-8 h-8 rounded-full bg-{{ strtolower($u->performance_color) }}-100 flex items-center justify-center text-xs font-bold text-{{ strtolower($u->performance_color) }}-700 border border-{{ strtolower($u->performance_color) }}-400">
                                            {{ substr($u->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-800">{{ $u->name }}</p>
                                            <p class="text-[10px] text-green-600 font-bold uppercase tracking-tighter">Verified Agenda ✅</p>
                                        </div>
                                    </div>
                                    @endif
                                @empty
                                @endforelse
                            </div>
                        </div>

                        <!-- Belum Submit -->
                        <div class="bg-red-50 rounded-3xl p-6 border border-red-100 shadow-inner">
                            <h4 class="font-black text-sm text-red-700 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                                Menunggu / Belum Input ({{ $totalUsers - count($submittedToday) }})
                            </h4>
                            <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                                @forelse($allUsers as $u)
                                    @if(!in_array($u->id, $submittedToday))
                                    <div class="flex items-center gap-3 bg-white p-3 rounded-2xl shadow-sm border border-red-200 opacity-70 grayscale hover:grayscale-0 transition cursor-help" title="Potensi pengurangan skor harian">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-400">
                                            {{ substr($u->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-600">{{ $u->name }}</p>
                                            <p class="text-[10px] text-red-400 font-bold uppercase tracking-tighter italic">Pending Submission ⌛</p>
                                        </div>
                                    </div>
                                    @endif
                                @empty
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>

            <!-- 2.5. Agenda Rapat & Pribadi (NEW) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Agenda Rapat (Upcoming Meetings) -->
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[2.5rem] p-8 border border-gray-100 relative">
                    <div class="absolute top-0 right-0 bg-sipega-orange text-white text-[10px] font-black px-4 py-2 rounded-bl-3xl rounded-tr-[2.5rem] uppercase tracking-widest">
                        Agenda Instansi / Rapat
                    </div>
                    <h3 class="text-2xl font-black text-sipega-navy mb-6 flex items-center gap-3">📅 Jadwal Rapat Anda</h3>
                    
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
                            <p class="text-xs font-bold uppercase tracking-widest text-center">Belum ada agenda rapat terdekat untuk Anda.</p>
                        </div>
                    @endif
                </div>

                <!-- Agenda Pribadi (Pimpinan's Daily Agenda) -->
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[2.5rem] p-8 border border-gray-100 relative">
                    <div class="absolute top-0 right-0 bg-sipega-navy text-white text-[10px] font-black px-4 py-2 rounded-bl-3xl rounded-tr-[2.5rem] uppercase tracking-widest">
                        Rencana / Agenda Pribadi
                    </div>
                    <h3 class="text-2xl font-black text-sipega-navy mb-6 flex items-center gap-3">📝 Agenda Hari Ini</h3>
                    
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
                            <p class="text-xs font-bold uppercase tracking-widest text-red-500 text-center">Anda belum menyusun agenda pribadi hari ini.</p>
                            <a href="{{ route('agenda.index') }}" class="mt-4 bg-red-500 text-white px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-red-600 transition">Buat Agenda</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. DINAS KHUSUS & PRIVATE ARCHIVE -->
            <div class="bg-sipega-navy rounded-[2.5rem] shadow-2xl p-8 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-20 -mt-20 blur-3xl"></div>
                <div class="relative z-10">
                    <h3 class="text-3xl font-black mb-6 flex items-center gap-4">
                        🕵️ Arsip Dinas Khusus (Private)
                        <span class="bg-orange-500 text-white text-[10px] px-3 py-1 rounded-full uppercase tracking-tighter">Pimpinan Only</span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($privateAssignments as $st)
                        <div class="bg-white/10 backdrop-blur-md rounded-3xl p-6 border border-white/10 hover:bg-white/20 transition group">
                            <div class="flex justify-between items-start mb-4">
                                <span class="text-[10px] font-black bg-white/20 px-2 py-1 rounded-md tracking-widest">NO: {{ $st->letter_number }}</span>
                                <span class="text-sipega-orange font-bold text-xs">{{ \Carbon\Carbon::parse($st->date)->format('M Y') }}</span>
                            </div>
                            <h4 class="text-xl font-extrabold mb-2 group-hover:text-orange-400 transition">{{ $st->title }}</h4>
                            <p class="text-sm text-gray-300 line-clamp-2 mb-4">{{ $st->description }}</p>
                            <div class="flex items-center gap-2">
                                <div class="flex -space-x-2">
                                    @foreach($st->users as $u)
                                    <div class="w-8 h-8 rounded-full border-2 border-sipega-navy bg-{{ strtolower($u->performance_color) }}-400 flex items-center justify-center text-[10px] font-black text-white" title="{{ $u->name }}">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    @endforeach
                                </div>
                                <span class="text-[10px] text-gray-400 italic ml-2">Peserta Terpilih</span>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-2 text-center py-12 border-2 border-dashed border-white/10 rounded-3xl">
                            <p class="text-gray-400 italic">Belum ada penugasan khusus bersifat rahasia.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
