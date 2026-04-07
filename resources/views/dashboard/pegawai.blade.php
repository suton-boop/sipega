<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sipega-navy leading-tight font-sans tracking-wide">
            {{ __('Halo Pegawai, ') }} <span class="text-sipega-orange">{{ $user->name }}</span> 👋
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- 1. Skor Performa & Warna Pribadi (Visibility Refinement) -->
            @php
                $perfData = $user->performance_breakdown ?? ['breakdown' => ['attendance' => 0, 'agenda' => 0, 'task' => 0]];
                $breakdown = $perfData['breakdown'] ?? ['attendance' => 0, 'agenda' => 0, 'task' => 0];
                
                $statusColor = 'blue'; $label = 'Sangat Istimewa'; $icon = '💎';
                $perfColor = $user->performance_color ?? 'Hijau';

                if ($perfColor == 'Merah') { $statusColor = 'red'; $label = 'Peringatan Keras'; $icon = '⚠️'; }
                elseif ($perfColor == 'Kuning') { $statusColor = 'yellow'; $label = 'Perlu Pembinaan'; $icon = '🔔'; }
                elseif ($perfColor == 'Hijau') { $statusColor = 'green'; $label = 'Disiplin / Baik'; $icon = '✅'; }
                
                $colorClasses = [
                    'blue' => ['bg' => 'bg-blue-600', 'text' => 'text-blue-600', 'light' => 'bg-blue-50', 'border' => 'border-blue-200'],
                    'red' => ['bg' => 'bg-red-600', 'text' => 'text-red-600', 'light' => 'bg-red-50', 'border' => 'border-red-200'],
                    'yellow' => ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-600', 'light' => 'bg-yellow-50', 'border' => 'border-yellow-200'],
                    'green' => ['bg' => 'bg-green-600', 'text' => 'text-green-600', 'light' => 'bg-green-50', 'border' => 'border-green-200'],
                ][$statusColor];
            @endphp
            
            <div class="relative group">
                <div class="absolute -inset-1 bg-gray-200 rounded-[3rem] blur opacity-25 group-hover:opacity-40 transition"></div>
                
                <div class="relative bg-white shadow-2xl sm:rounded-[3rem] overflow-hidden border border-gray-100">
                    <div class="p-8 md:p-12">
                        <div class="flex flex-col md:flex-row items-center gap-10">
                            <!-- Left: Massive Circular Score -->
                            <div class="relative flex-shrink-0">
                                <svg class="w-48 h-48 transform -rotate-90">
                                    <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="12" fill="transparent" class="text-gray-100" />
                                    <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="12" fill="transparent" 
                                        stroke-dasharray="{{ 552.92 }}" 
                                        stroke-dashoffset="{{ 552.92 * (1 - $user->performance_score/100) }}" 
                                        class="{{ $colorClasses['text'] }} transition-all duration-1000 ease-out" />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-5xl font-black text-sipega-navy">{{ number_format($user->performance_score ?? 0, 1) }}</span>
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Skor Akhir</span>
                                </div>
                            </div>

                            <!-- Middle: Details & Breakdown -->
                            <div class="flex-grow text-center md:text-left">
                                <div class="inline-flex items-center gap-2 {{ $colorClasses['light'] }} {{ $colorClasses['text'] }} px-4 py-2 rounded-full border {{ $colorClasses['border'] }} mb-4">
                                     <span class="text-lg">{{ $icon }}</span>
                                     <span class="text-xs font-black uppercase tracking-widest">{{ $perfColor }}: {{ $label }}</span>
                                </div>
                                <h3 class="text-3xl font-black text-sipega-navy mb-6 leading-tight">Analisis Performa<br><span class="text-gray-400 text-lg font-bold italic tracking-normal">Berdasarkan SIPEGA Weighted Point System</span></h3>

                                <!-- Breakdown Grid -->
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="bg-gray-50 p-4 rounded-3xl border border-gray-100">
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Absensi</p>
                                        <p class="text-xl font-black text-sipega-navy">{{ $breakdown['attendance'] }}<span class="text-[10px] text-gray-300">/40</span></p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-3xl border border-gray-100">
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Agenda</p>
                                        <p class="text-xl font-black text-sipega-navy">{{ $breakdown['agenda'] }}<span class="text-[10px] text-gray-300">/30</span></p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-3xl border border-gray-100">
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Penugasan</p>
                                        <p class="text-xl font-black text-sipega-navy">{{ $breakdown['task'] }}<span class="text-[10px] text-gray-300">/30</span></p>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-6 font-bold uppercase tracking-widest">Update Terakhir: {{ now('Asia/Makassar')->format('d/m/Y H:i') }} WITA</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(\App\Models\Setting::get('is_tukin_active') !== '0')
            @php
                $tukin = $user->calculateMonthlyTukin();
            @endphp

            <!-- Tukin Estimation Card (New) -->
            <div class="bg-gradient-to-r from-sipega-navy to-[#002244] overflow-hidden shadow-2xl sm:rounded-[3rem] p-8 text-white relative group transition-all hover:scale-[1.01]">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6 relative z-10">
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-[0.3em] text-sipega-orange mb-2">Estimasi Tunjangan Kinerja (Tukin) - {{ $tukin['month'] }}</h3>
                        <p class="text-4xl font-black">Rp {{ number_format($tukin['net_tukin'], 0, ',', '.') }}</p>
                        <div class="mt-4 flex flex-wrap gap-4 text-[10px] items-center">
                            <span class="bg-white/10 px-3 py-1 rounded-full border border-white/20">Base: Rp {{ number_format($tukin['base_tukin'], 0, ',', '.') }}</span>
                            @if($tukin['attendance_penalty_amount'] > 0)
                                <span class="text-red-300 font-bold bg-red-500/20 px-3 py-1 rounded-full border border-red-500/30">📉 Potongan Absen ({{ $tukin['attendance_penalty_minutes'] }}m): -Rp {{ number_format($tukin['attendance_penalty_amount'], 0, ',', '.') }}</span>
                            @endif
                            @if($tukin['performance_penalty_amount'] > 0)
                                <span class="text-orange-300 font-bold bg-orange-500/20 px-3 py-1 rounded-full border border-orange-500/30">📊 Potongan Kinerja ({{ $tukin['performance_penalty_percent'] }}%): -Rp {{ number_format($tukin['performance_penalty_amount'], 0, ',', '.') }}</span>
                            @endif
                            @if($tukin['attendance_penalty_amount'] == 0 && $tukin['performance_penalty_amount'] == 0)
                                <span class="text-green-300 font-bold bg-green-500/20 px-3 py-1 rounded-full border border-green-500/30">⭐ Performa Sempurna: Tanpa Potongan</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-5xl opacity-20">💰</span>
                    </div>
                </div>
                <!-- Interactive Pattern -->
                <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none text-8xl font-black rotate-12">TUKIN</div>
            </div>
            @endif

            <!-- 1.5. Agenda Rapat & Pribadi (NEW) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Agenda Rapat (Mentions & Target_type all) -->
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] p-8 border border-gray-100 relative">
                    <div class="absolute top-0 right-0 bg-sipega-orange text-white text-[10px] font-black px-4 py-2 rounded-bl-3xl rounded-tr-[3rem] uppercase tracking-widest">
                        Agenda Instansi / Rapat
                    </div>
                    <h3 class="text-2xl font-black text-sipega-navy mb-6 flex items-center gap-3">📅 Jadwal Rapat</h3>
                    
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
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] p-8 border border-gray-100 relative">
                    <div class="absolute top-0 right-0 bg-sipega-navy text-white text-[10px] font-black px-4 py-2 rounded-bl-3xl rounded-tr-[3rem] uppercase tracking-widest">
                        Rencana / Agenda Pribadi
                    </div>
                    <h3 class="text-2xl font-black text-sipega-navy mb-6 flex items-center gap-3">📝 Agenda Hari Ini</h3>
                    
                    @if($todayAgenda && $todayAgenda->items->count() > 0)
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

            <!-- 2. Status Kerja Hari Ini (Agenda & Realisasi) -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] p-10 relative group">
                <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                    <div class="md:w-3/5">
                        <h3 class="text-3xl font-black mb-3 text-sipega-navy flex items-center gap-3">📝 Status Agenda Kerja</h3>
                        <p class="text-gray-500 font-medium text-sm leading-relaxed mb-6">Pantau rincian kegiatan dan evaluasi harian Anda secara terpisah di menu Agenda.</p>
                        
                        @if($todayAgenda)
                            <div class="p-4 rounded-3xl border-2 {{ $todayAgenda->realization_submitted_at ? 'bg-green-50 border-green-100 text-green-700' : 'bg-orange-50 border-orange-100 text-orange-700' }} flex items-center gap-4 transition-all group-hover:shadow-md">
                                <span class="text-2xl">{{ $todayAgenda->realization_submitted_at ? '✅' : '⏳' }}</span>
                                <div>
                                    <p class="text-sm font-black uppercase tracking-tight">{{ $todayAgenda->realization_submitted_at ? 'Tuntas & Terverifikasi' : 'Rencana Terkirim, Menunggu Realisasi' }}</p>
                                    <p class="text-[10px] font-bold opacity-75">Update terakhir: {{ \Carbon\Carbon::parse($todayAgenda->updated_at)->format('H:i') }} WITA</p>
                                </div>
                            </div>
                        @else
                            <div class="p-4 rounded-3xl bg-red-50 border-2 border-red-100 text-red-700 flex items-center gap-4">
                                <span class="text-2xl">⚠️</span>
                                <div>
                                    <p class="text-sm font-black uppercase tracking-tight">Belum Ada Agenda Hari Ini</p>
                                    <p class="text-[10px] font-bold opacity-75 italic">Wajib submit rencana sebelum 09:00 WITA</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="md:w-1/3 w-full">
                        <a href="{{ route('agenda.index') }}" class="block text-center bg-sipega-navy hover:bg-black text-white font-black py-5 px-8 rounded-3xl shadow-2xl transition hover:-translate-y-2 transform active:scale-95 text-lg uppercase tracking-widest">
                            KELOLA AGENDA 🚀
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. Grid Menu Akses Cepat lainnya -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Quick Attendance -->
                <div class="bg-gradient-to-br from-blue-700 to-blue-900 overflow-hidden shadow-2xl sm:rounded-[3rem] p-8 text-white hover:rotate-1 transition-transform cursor-pointer" onclick="window.location='{{ route('attendance.index') }}'">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-3xl">📍</div>
                        <span class="text-[10px] font-black uppercase tracking-widest bg-white/10 px-3 py-1 rounded-full border border-white/20">Presensi</span>
                    </div>
                    <h4 class="text-2xl font-black mb-1">Daftar Hadir</h4>
                    <p class="text-blue-100 text-xs font-bold opacity-80 mb-6">Presensi rapat GPS & QR Code.</p>
                    <div class="mt-auto flex items-center gap-2 font-black text-xs uppercase tracking-widest text-orange-400 group-hover:gap-4 transition-all">
                        BUKA MENU PRESENSI ➜
                    </div>
                </div>

                <!-- Quick Assignments -->
                <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] p-8 border border-gray-100 group hover:-rotate-1 transition-transform cursor-pointer" onclick="window.location='{{ route('letters.index') }}'">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center text-3xl shadow-inner italic font-black text-sipega-navy">ST</div>
                        <span class="text-[10px] font-black uppercase tracking-widest bg-gray-100 text-gray-500 px-3 py-1 rounded-full border border-gray-200">{{ $myAssignmentsCount }} Dokumen</span>
                    </div>
                    <h4 class="text-2xl font-black mb-1 text-sipega-navy">SK & Surat Tugas</h4>
                    <p class="text-gray-400 text-xs font-bold mb-6">Unduh ST & Unggah LHDL Mandiri.</p>
                    <div class="mt-auto flex items-center gap-2 font-black text-xs uppercase tracking-widest text-sipega-navy opacity-50">
                        AKSES DOKUMEN ➜
                    </div>
                </div>

            </div>

            <!-- 4. Wall of Fame (Community) -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] p-10 border-t-[8px] border-sipega-orange">
                <h3 class="text-2xl font-black mb-8 text-sipega-navy flex items-center gap-3 italic">🏆 Wall of Fame : Bulan Ini</h3>
                <div class="grid grid-cols-5 gap-4">
                    @foreach($top5Highest as $top)
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 rounded-3xl bg-gray-50 border-2 border-green-500 p-1 mb-2 shadow-lg transition hover:scale-110">
                            <div class="w-full h-full rounded-2xl bg-gradient-to-br from-green-500 to-green-700 flex items-center justify-center text-white font-black text-lg">
                                {{ substr($top->name, 0, 1) }}
                            </div>
                        </div>
                        <p class="text-[8px] font-black uppercase text-gray-500 text-center leading-tight">{{ explode(' ', $top->name)[0] }}</p>
                        <p class="text-[10px] font-bold text-green-600">{{ number_format($top->performance_score, 0) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Footer Quick Drive -->
            <div class="bg-sipega-navy p-10 rounded-[3rem] shadow-2xl flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-center md:text-left">
                    <h3 class="text-2xl font-black text-white">☁️ SIPEGA-Drive</h3>
                    <p class="text-white/50 text-xs font-bold tracking-widest uppercase mt-1">Cloud Locker Dinas Anda</p>
                </div>
                <a href="{{ $user->drive_folder_url ?? '#' }}" target="_blank" class="bg-white hover:bg-sipega-orange hover:text-white text-sipega-navy font-black py-4 px-10 rounded-full shadow-2xl transition transform active:scale-95 text-sm uppercase tracking-widest whitespace-nowrap">
                    AKSES CLOUD FOLDER ➜
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
