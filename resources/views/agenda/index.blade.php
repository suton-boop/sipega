<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center whitespace-nowrap">
            <div>
                <h2 class="font-extrabold text-2xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Agenda & Realisasi
                </h2>
                <p class="text-[10px] font-bold text-sipega-orange uppercase tracking-[0.3em] mt-1 italic font-sans tracking-wide">Pelaporan Kinerja Harian Pegawai</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if(session('success'))
                <div class="bg-green-50 text-green-700 p-6 rounded-3xl mb-8 font-bold border border-green-100 flex items-center gap-3 shadow-sm">
                    <span class="bg-green-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black italic">!</span> 
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 text-red-700 p-6 rounded-3xl mb-8 font-bold border border-red-100 flex items-center gap-3 shadow-sm">
                    <span class="bg-red-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black italic">X</span> 
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 text-red-700 p-6 rounded-3xl mb-8 font-bold border border-red-100 shadow-sm">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Agenda Card -->
            <div class="bg-white overflow-hidden shadow-2xl rounded-[40px] border border-gray-100 flex flex-col">
                <div class="p-10 lg:p-12">
                    <div class="mb-10">
                        <h3 class="text-3xl font-black text-sipega-navy tracking-tighter uppercase mb-2">Kegiatan Hari Ini</h3>
                        <div class="h-1.5 w-16 bg-sipega-orange rounded-full"></div>
                    </div>
                    
                    @if($myAgendaToday)
                        <!-- STEP 1: RENCANA (LIST VIEW) -->
                        <div class="bg-gray-50 p-8 rounded-[2.5rem] border border-gray-100 mb-10 relative overflow-hidden shadow-inner">
                            <div class="flex items-center justify-between mb-8">
                                <strong class="text-xl font-black text-sipega-navy uppercase tracking-tight">Rincian Rencana Pagi</strong>
                                <span class="text-[10px] text-gray-400 font-bold tracking-widest uppercase">Submited At: {{ \Carbon\Carbon::parse($myAgendaToday->submitted_at)->format('H:i') }} WITA</span>
                            </div>
                            <ul class="space-y-4">
                                @foreach($myAgendaToday->items as $item)
                                    <li class="flex items-start gap-4 bg-white p-5 rounded-3xl shadow-sm border border-gray-100 transition-all hover:border-sipega-orange group">
                                        <span class="bg-sipega-navy group-hover:bg-sipega-orange text-white text-[10px] font-black w-6 h-6 flex items-center justify-center rounded-lg mt-0.5 transition-colors">{{ $loop->iteration }}</span>
                                        <span class="font-bold text-gray-700 leading-snug">{{ $item->plan_description }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- STEP 2: REALISASI (PER ITEM) -->
                        @if(!$myAgendaToday->realization_submitted_at)
                            @php 
                                $hour = (int) now('Asia/Makassar')->format('H'); 
                                $isFlexible = \App\Models\Setting::get('is_realization_open_anytime') === '1';
                            @endphp
                            @if($isFlexible || ($hour >= 15 && $hour < 17))
                                <div class="bg-sipega-navy rounded-[3rem] p-10 lg:p-12 shadow-2xl relative overflow-hidden">
                                     <!-- Decoration -->
                                    <div class="absolute -top-20 -right-20 w-64 h-64 bg-sipega-orange/10 blur-[80px] rounded-full"></div>

                                    <div class="relative z-10">
                                        <h4 class="text-3xl font-black text-white mb-2 uppercase tracking-tighter">Evaluasi Kinerja Sore</h4>
                                        <p class="text-white/40 text-xs font-bold mb-10 uppercase tracking-widest">Tentukan status akhir untuk setiap kegiatan Anda</p>
                                        
                                        <form action="{{ route('agenda.realization', $myAgendaToday->id) }}" method="POST" enctype="multipart/form-data" class="space-y-10">
                                            @csrf
                                            <div class="space-y-6">
                                                @foreach($myAgendaToday->items as $item)
                                                    <div class="bg-white/5 p-8 rounded-[2rem] border border-white/10 shadow-sm backdrop-blur-sm">
                                                        <p class="font-bold text-white text-lg mb-6 leading-tight">{{ $item->plan_description }}</p>
                                                        <div class="grid grid-cols-3 gap-4 mb-6">
                                                            <label class="cursor-pointer group/choice">
                                                                <input type="radio" name="items[{{ $item->id }}][status]" value="completed" checked class="hidden peer">
                                                                <div class="text-center p-4 rounded-2xl border-2 border-white/5 bg-white/5 peer-checked:border-sipega-orange peer-checked:bg-sipega-orange peer-checked:text-white transition-all text-white/40">
                                                                    <span class="block text-[10px] font-black uppercase tracking-widest">Selesai</span>
                                                                </div>
                                                            </label>
                                                            <label class="cursor-pointer group/choice">
                                                                <input type="radio" name="items[{{ $item->id }}][status]" value="changed" class="hidden peer">
                                                                <div class="text-center p-4 rounded-2xl border-2 border-white/5 bg-white/5 peer-checked:border-sipega-orange peer-checked:bg-sipega-orange peer-checked:text-white transition-all text-white/40">
                                                                    <span class="block text-[10px] font-black uppercase tracking-widest">Berubah</span>
                                                                </div>
                                                            </label>
                                                            <label class="cursor-pointer group/choice">
                                                                <input type="radio" name="items[{{ $item->id }}][status]" value="progress" class="hidden peer">
                                                                <div class="text-center p-4 rounded-2xl border-2 border-white/5 bg-white/5 peer-checked:border-sipega-orange peer-checked:bg-sipega-orange peer-checked:text-white transition-all text-white/40">
                                                                    <span class="block text-[10px] font-black uppercase tracking-widest">Progres</span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                        <input type="text" name="items[{{ $item->id }}][notes]" class="w-full bg-white/5 border-white/10 focus:border-sipega-orange focus:ring-0 rounded-2xl text-xs text-white placeholder-white/20 p-4 font-medium" placeholder="Catatan tambahan (opsional)...">
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="bg-white/5 p-8 rounded-3xl border border-white/10 shadow-sm backdrop-blur-sm">
                                                <label class="font-black text-white/40 text-[10px] uppercase tracking-[0.2em] block mb-4">Lampiran Bukti Realisasi (JPG/PNG)</label>
                                                <input type="file" name="proof_file" accept=".jpg,.png,.jpeg" class="block w-full text-xs text-white/40 file:mr-6 file:py-3 file:px-8 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-sipega-orange file:text-white hover:file:bg-orange-600 transition-all cursor-pointer"/>
                                            </div>

                                            <button type="submit" class="bg-sipega-orange hover:bg-orange-600 text-white font-black py-6 rounded-[2rem] w-full shadow-2xl transition-all uppercase tracking-[0.2em] hover:-translate-y-1">
                                                Simpan Evaluasi Harian
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <div class="bg-gray-50 border-2 border-dashed border-gray-100 rounded-[3rem] p-12 text-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <h4 class="text-xl font-black text-gray-400 uppercase tracking-tight mb-2">Evaluasi Belum Terbuka</h4>
                                    <p class="text-sm text-gray-400 font-medium italic">Fitur evaluasi sore terbuka otomatis pukul 15:00 - 17:00 WITA.</p>
                                    @if($isFlexible)
                                        <p class="text-[10px] text-green-600 font-black uppercase mt-2">Mode Uji Coba: AKTIF (Akses Terbuka)</p>
                                    @endif
                                </div>
                            @endif
                        @else
                            <!-- STEP 3: SUMMARY SELESAI -->
                            <div class="bg-gradient-to-br from-sipega-navy to-black text-white p-10 lg:p-12 rounded-[3.5rem] shadow-2xl relative overflow-hidden">
                                <div class="absolute -top-10 -right-10 w-40 h-40 bg-green-500/10 blur-[60px] rounded-full"></div>
                                
                                <div class="flex items-center justify-between mb-10">
                                    <h4 class="text-3xl font-black uppercase tracking-tighter">Laporan Harian Tuntas</h4>
                                    <span class="px-6 py-2 bg-green-500 rounded-full text-[10px] font-black uppercase tracking-widest text-white animate-pulse">VERIFIED</span>
                                </div>

                                <div class="space-y-5">
                                    @foreach($myAgendaToday->items as $item)
                                    <div class="bg-white/5 p-6 rounded-[2rem] border border-white/10 flex justify-between items-center group hover:bg-white/10 transition-all">
                                        <div class="flex items-center gap-5">
                                            <div class="w-2 h-2 rounded-full {{ $item->status == 'completed' ? 'bg-green-500' : 'bg-orange-400' }}"></div>
                                            <div>
                                                <p class="font-bold text-white text-lg tracking-tight leading-none mb-1">{{ $item->plan_description }}</p>
                                                @if($item->realization_notes) <p class="text-[10px] text-white/40 font-medium italic tracking-wide">{{ $item->realization_notes }}</p> @endif
                                            </div>
                                        </div>
                                        <span class="text-[9px] font-black uppercase tracking-[0.2em] {{ $item->status == 'completed' ? 'text-green-400' : 'text-orange-400' }}">
                                            {{ $item->status }}
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-8 pt-8 border-t border-white/5 flex justify-between">
                                    <p class="text-[10px] font-black text-white/20 uppercase tracking-widest">Time: {{ \Carbon\Carbon::parse($myAgendaToday->realization_submitted_at)->format('H:i') }} WITA</p>
                                    <p class="text-[10px] font-black text-white/20 uppercase tracking-widest">SIPEGA ENGINE v2.0</p>
                                </div>
                            </div>
                        @endif

                    @else
                        <!-- FORM RENCANA PAGI (MULTI-INPUT) -->
                        @php 
                            $hour = (int) now('Asia/Makassar')->format('H'); 
                            $isFlexible = \App\Models\Setting::get('is_realization_open_anytime') === '1';
                        @endphp

                        @if($isFlexible || ($hour >= 7 && $hour < 17))
                            <div class="bg-sipega-navy p-10 lg:p-12 rounded-[3.5rem] text-white shadow-2xl relative overflow-hidden">
                                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-sipega-orange/10 blur-[80px] rounded-full"></div>
                                
                                <div class="relative z-10">
                                    <h4 class="text-3xl font-black uppercase tracking-tighter mb-2 italic">Selamat Pagi!</h4>
                                    <p class="text-white/40 text-xs font-bold mb-4 uppercase tracking-widest">Entri rencana kerja Anda hari ini ({{ $hour < 9 ? 'Tepat Waktu' : 'Terlambat' }})</p>
                                    
                                    @if((isset($upcomingMeetings) && $upcomingMeetings->count() > 0) || (isset($mySchedules) && $mySchedules->count() > 0))
                                        <div class="mb-8 p-6 bg-white/5 border border-white/10 rounded-[2rem] backdrop-blur-sm">
                                            <p class="text-[10px] font-black text-sipega-orange uppercase tracking-[0.2em] mb-4">💡 Kegiatan Terjadwal Hari Ini (Klik untuk tambah ke rencana):</p>
                                            <div class="flex flex-wrap gap-3">
                                                @foreach($upcomingMeetings as $meeting)
                                                    <button type="button" 
                                                            onclick="addMeetingToPlan('{{ $meeting->title }}')"
                                                            class="text-left p-3 rounded-2xl bg-white/10 hover:bg-sipega-orange hover:text-white transition group border border-transparent hover:border-white/20">
                                                        <p class="text-[11px] font-bold leading-tight">{{ $meeting->title }}</p>
                                                        <p class="text-[9px] opacity-60 font-black mt-1 uppercase tracking-tighter">🏢 Rapat: {{ $meeting->location_name }}</p>
                                                    </button>
                                                @endforeach

                                                @foreach($mySchedules as $schedule)
                                                    <button type="button" 
                                                            onclick="addMeetingToPlan('{{ $schedule->title }}')"
                                                            class="text-left p-3 rounded-2xl bg-blue-500/10 hover:bg-sipega-orange hover:text-white transition group border border-transparent hover:border-white/20">
                                                        <p class="text-[11px] font-bold leading-tight">{{ $schedule->title }}</p>
                                                        <p class="text-[9px] opacity-60 font-black mt-1 uppercase tracking-tighter">👤 Pribadi{{ $schedule->location ? ': ' . $schedule->location : '' }}</p>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <form action="{{ route('agenda.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                                        @csrf
                                        <div id="plan-container" class="space-y-4">
                                            <div class="relative group">
                                                <input type="text" name="plans[]" required minlength="5" class="w-full bg-white/5 border-2 border-white/10 focus:border-sipega-orange focus:ring-0 rounded-3xl p-6 text-white font-bold placeholder-white/20 transition-all" placeholder="Ketik kegiatan utama Anda...">
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-6">
                                            <button type="button" onclick="addPlanField()" class="bg-white/5 hover:bg-white/10 text-white font-black text-[10px] uppercase tracking-widest flex items-center gap-3 px-6 py-4 rounded-2xl transition">
                                                <span class="bg-sipega-orange text-white w-5 h-5 rounded-lg flex items-center justify-center">+</span> Tambah Kegiatan
                                            </button>
                                            <div class="h-px flex-grow bg-white/5"></div>
                                        </div>

                                        <button type="submit" class="bg-white text-sipega-navy hover:bg-sipega-orange hover:text-white font-black py-6 rounded-[2rem] w-full shadow-2xl transition-all uppercase tracking-[0.2em] hover:-translate-y-1">
                                            Kirim Seluruh Rencana
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="bg-gray-50 border-2 border-dashed border-gray-100 rounded-[3rem] p-12 text-center">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <h4 class="text-xl font-black text-gray-400 uppercase tracking-tight mb-2">Input Rencana Belum Terbuka</h4>
                                <p class="text-sm text-gray-400 font-medium italic">Pengisian rencana agenda harian tersedia pukul 07:00 - 17:00 WITA.</p>
                                @if($isFlexible)
                                    <p class="text-[10px] text-green-600 font-black uppercase mt-2">Mode Uji Coba: AKTIF (Akses Terbuka)</p>
                                @endif
                            </div>
                        @endif

                        <script>
                            function addPlanField() {
                                const container = document.getElementById('plan-container');
                                const count = container.children.length + 1;
                                const div = document.createElement('div');
                                div.className = 'relative animate-fade-in-down';
                                div.innerHTML = `<input type="text" name="plans[]" required minlength="5" class="w-full bg-white/5 border-2 border-white/10 focus:border-sipega-orange focus:ring-0 rounded-3xl p-6 text-white font-bold placeholder-white/20 transition-all border-dashed" placeholder="Kegiatan ke-${count}...">`;
                                container.appendChild(div);
                            }

                            function addMeetingToPlan(title) {
                                const container = document.getElementById('plan-container');
                                const firstInput = container.querySelector('input[name="plans[]"]');
                                
                                if (firstInput && (firstInput.value === '' || firstInput.value === null)) {
                                    firstInput.value = title;
                                    firstInput.classList.remove('border-white/10');
                                    firstInput.classList.add('border-sipega-orange');
                                } else {
                                    const div = document.createElement('div');
                                    div.className = 'relative animate-fade-in-down';
                                    div.innerHTML = `<input type="text" name="plans[]" value="${title}" required minlength="5" class="w-full bg-white/5 border-2 border-sipega-orange focus:border-sipega-orange focus:ring-0 rounded-3xl p-6 text-white font-bold placeholder-white/20 transition-all border-dashed">`;
                                    container.appendChild(div);
                                }
                            }
                        </script>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
