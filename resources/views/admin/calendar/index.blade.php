<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight uppercase">
            {{ __('Kalender Kerja SIPEGA') }}
        </h2>
    </x-slot>

    <style>
        .month-tab { transition: all 0.3s ease; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; }
        .day-cell { aspect-ratio: 1 / 1; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 12px; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
        .day-cell:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-color: #003366; }
        .day-cell.holiday { background-color: #FEF2F2; color: #DC2626; border-color: #FEE2E2; }
        .day-cell.work { background-color: #F9FAFB; color: #374151; }
        .day-cell.duty { background-color: #FFF7ED; color: #EA580C; border-color: #FDBA74; box-shadow: 0 2px 8px rgba(234, 88, 12, 0.15); }
        .day-cell.duty:hover { border-color: #EA580C; }
        .active-month { background-color: #003366 !important; color: white !important; font-weight: 800; }
        .sidebar-dark { background-color: #003366; color: white; min-height: 520px; }
        .text-sipega-navy { color: #003366; }
        .bg-sipega-navy { background-color: #003366; }
        .bg-sipega-orange { background-color: #ff8c00; }
        .text-sipega-orange { color: #ff8c00; }
        .day-cell span { line-height: 1; }
        .header-grid { display: grid; grid-template-columns: repeat(7, 1fr); width: 100%; text-align: center; margin-bottom: 24px; border-bottom: 1px solid #f3f4f6; pb: 4 }
    </style>

    <div class="py-10 bg-[#f8fafc]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Employee Info & Selection Bar -->
            <div class="mb-4 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center font-black border border-orange-200 shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-extrabold text-orange-600 uppercase tracking-widest bg-orange-100/60 px-2 py-0.5 rounded-full">Kalender Individu Pegawai</span>
                            @if($targetUser->id === auth()->id())
                                <span class="text-[9px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Akun Anda</span>
                            @endif
                        </div>
                        <h4 class="text-base font-black text-sipega-navy uppercase mt-0.5">
                            {{ $targetUser->name }}
                            <span class="text-xs font-semibold text-gray-400 font-mono">({{ $targetUser->nip ?? 'NIP: -' }})</span>
                        </h4>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                    <div class="px-3.5 py-1.5 bg-orange-50 rounded-xl border border-orange-200 text-xs font-bold text-orange-700 flex items-center gap-2 shadow-sm">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse"></span>
                        <span>Dinas Luar Bulan Ini: <strong class="text-orange-900">{{ $monthDutyCount }} Hari</strong></span>
                    </div>

                    @if($allUsers->count() > 1)
                    <form method="GET" action="{{ route('admin.calendar.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="month" value="{{ $currentMonthIdx }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <label for="user_id" class="text-[10px] font-black text-gray-500 uppercase tracking-wider hidden sm:inline">Pilih:</label>
                        <select name="user_id" id="user_id" onchange="this.form.submit()" class="text-xs font-bold rounded-xl border-gray-200 py-2 px-3 focus:ring-orange-500 focus:border-orange-500 bg-gray-50 text-gray-800 shadow-sm cursor-pointer max-w-[220px]">
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ $targetUser->id == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->nip ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                    @endif
                </div>
            </div>

            <!-- Horizontal Month Tabs -->
            <div class="flex items-center justify-between mb-4 bg-white p-2 rounded-xl shadow-sm border border-gray-100 overflow-x-auto no-scrollbar">
                @php
                    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                @endphp
                @foreach($months as $idx => $m)
                    <a href="{{ route('admin.calendar.index', ['month' => $idx + 1, 'year' => $year, 'user_id' => $targetUser->id]) }}" 
                       class="month-tab px-6 py-2 rounded-lg text-[10px] font-extrabold uppercase tracking-widest text-gray-400 hover:text-sipega-navy {{ $currentMonthIdx == ($idx + 1) ? 'active-month scale-105 shadow-md' : '' }}">
                        {{ $m }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white rounded-[32px] shadow-2xl overflow-hidden border border-gray-100">
                <div class="flex flex-col lg:flex-row">
                    
                    <!-- Left Section: The Grid -->
                    <div class="flex-1 p-8 lg:p-12">
                        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
                            <div>
                                <h3 class="text-4xl font-black text-sipega-navy uppercase tracking-tighter">{{ $months[$currentMonthIdx - 1] }} {{ $year }}</h3>
                                <p class="text-[10px] font-black text-sipega-orange uppercase tracking-[0.4em] mt-1 italic">Tahun Anggaran Berjalan</p>
                            </div>
                            <div class="flex flex-wrap gap-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Libur/Akhir Pekan</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Hari Kerja ASN</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-orange-500 ring-2 ring-orange-200"></div>
                                    <span class="text-[9px] font-extrabold text-orange-600 uppercase tracking-widest">Dinas Luar (ST)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Days Labels (Fixed Horizontal) -->
                        <div class="header-grid">
                            @foreach(['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'MINGGU'] as $label)
                                <span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">{{ $label }}</span>
                            @endforeach
                        </div>

                        <!-- Logic Calendar Grid -->
                        <div class="calendar-grid">
                            @php
                                $startOfMonth = \Carbon\Carbon::create($year, $currentMonthIdx, 1);
                                $daysInMonth = $startOfMonth->daysInMonth;
                                $firstDayOfWeek = $startOfMonth->dayOfWeekIso; // 1-7
                            @endphp

                            {{-- Placeholder for shifting --}}
                            @for($i = 1; $i < $firstDayOfWeek; $i++)
                                <div></div>
                            @endfor

                            {{-- Render Days --}}
                            @for($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $cDate = \Carbon\Carbon::create($year, $currentMonthIdx, $day);
                                    $dateStr = $cDate->toDateString();
                                    $event = $events->firstWhere('date', $dateStr);
                                    $isWeekend = $cDate->isWeekend();
                                    $isHoliday = ($event && ($event->type == 'Holiday' || $event->type == 'Shared Leave'));
                                    $hasDuty = isset($dutyDates[$dateStr]) && count($dutyDates[$dateStr]) > 0;
                                    
                                    if ($hasDuty) {
                                        $cellClass = 'duty';
                                        $dotColor = 'bg-orange-500';
                                    } elseif ($isWeekend || $isHoliday) {
                                        $cellClass = 'holiday';
                                        $dotColor = 'bg-red-500';
                                    } else {
                                        $cellClass = 'work';
                                        $dotColor = 'bg-green-500';
                                    }
                                @endphp
                                <div class="day-cell {{ $cellClass }}" 
                                     onclick="showDayDetail('{{ $dateStr }}', '{{ $event ? addslashes($event->description) : ($isWeekend ? 'Libur Akhir Pekan' : 'Hari Kerja ASN SIPEGA') }}', '{{ $event ? $event->type : ($isWeekend ? 'Holiday' : 'Working Day') }}')">
                                    <span class="text-xl font-black">{{ $day }}</span>
                                    @if($hasDuty)
                                        <div class="flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded-full bg-orange-100 border border-orange-300 shadow-sm" title="Sedang Dinas Luar (Surat Tugas Resmi)">
                                            <div class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></div>
                                            <span class="text-[8px] font-black uppercase text-orange-700 tracking-wider">DL</span>
                                        </div>
                                    @else
                                        <div class="w-1.5 h-1.5 rounded-full {{ $dotColor }} mt-2 shadow-sm"></div>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Right Section: Detail Sidebar -->
                    <div id="side-panel" class="sidebar-dark w-full lg:w-96 p-10 flex flex-col justify-between">
                        <div>
                            <div id="detail-date" class="text-3xl font-black italic tracking-tighter mb-2 border-b border-white/10 pb-4">
                                {{ now()->translatedFormat('d F Y') }}
                            </div>
                            
                            <div class="mt-8 space-y-6">
                                <div class="flex items-start gap-4">
                                    <div id="detail-dot" class="w-5 h-5 rounded-full bg-green-500 shrink-0 mt-1 shadow-lg shadow-green-500/20"></div>
                                    <div>
                                        <h4 id="detail-title" class="text-base font-black uppercase tracking-widest mb-1 italic text-emerald-400">Hari Kerja ASN</h4>
                                        <p id="detail-desc" class="text-xs text-gray-300 font-medium leading-relaxed">Pimpinan dan Pegawai beroperasi sesuai jam kerja standar SIPEGA.</p>
                                    </div>
                                </div>

                                <!-- Duty Detail Cards Container -->
                                <div id="duty-box" class="hidden bg-white/10 rounded-2xl p-5 border border-orange-400/40 shadow-inner">
                                    <div class="flex items-center gap-2 text-xs font-black text-orange-300 uppercase tracking-wider mb-3">
                                        <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Agenda Dinas Luar Resmi
                                    </div>
                                    <div id="duty-list" class="space-y-3">
                                        <!-- Rendered dynamically via JS -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dashboard Editor Form (Hanya Admin / Pimpinan / Kasubag) -->
                        @if(in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
                        <div class="pt-8 border-t border-white/10 mt-8">
                            <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-4 italic">Pengaturan Hari Khusus</h5>
                            <form action="{{ route('admin.calendar.store') }}" method="POST" class="space-y-3">
                                @csrf
                                <input type="hidden" name="date" id="form-date" value="{{ now()->toDateString() }}">
                                
                                <div>
                                    <select name="type" required class="w-full bg-white/10 border-white/20 rounded-xl text-xs font-bold py-3 px-4 focus:ring-sipega-orange focus:border-sipega-orange text-white">
                                        <option value="Working Day" class="text-black">Hari Kerja Khusus</option>
                                        <option value="Shared Leave" class="text-black">Cuti Bersama</option>
                                        <option value="Holiday" class="text-black">Hari Libur Nasional</option>
                                        <option value="Overtime" class="text-black">Lembur Kolektif</option>
                                    </select>
                                </div>

                                <div>
                                    <input type="text" name="description" placeholder="Keterangan hari..." required class="w-full bg-white/10 border-white/20 rounded-xl text-xs font-medium py-3 px-4 focus:ring-sipega-orange focus:border-sipega-orange text-white placeholder-gray-400">
                                </div>

                                <button type="submit" class="w-full bg-sipega-orange hover:bg-orange-600 text-white font-black py-3.5 rounded-xl text-xs uppercase tracking-[0.2em] shadow-lg transition-all hover:-translate-y-0.5">
                                    Simpan Status Hari
                                </button>
                            </form>
                        </div>
                        @else
                        <div class="pt-6 border-t border-white/10 mt-8 text-[11px] text-gray-400 leading-relaxed italic">
                            💡 Surat Tugas yang berstatus Disetujui (Approved) otomatis tampil berwarna orange dan tercatat dalam Rekap Dinas Luar.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dutyDatesMap = @json($dutyDates);
        const targetUserName = @json($targetUser->name);

        function showDayDetail(date, desc, type) {
            const dateObj = new Date(date);
            const options = { day: '2-digit', month: 'long', year: 'numeric' };
            const formattedDate = dateObj.toLocaleDateString('id-ID', options);
            
            document.getElementById('detail-date').innerText = formattedDate.toUpperCase();
            const formDateInput = document.getElementById('form-date');
            if (formDateInput) {
                formDateInput.value = date;
            }

            const dot = document.getElementById('detail-dot');
            const title = document.getElementById('detail-title');
            const descEl = document.getElementById('detail-desc');
            const dutyBox = document.getElementById('duty-box');
            const dutyList = document.getElementById('duty-list');

            const duties = dutyDatesMap[date];

            if (duties && duties.length > 0) {
                dot.className = 'w-5 h-5 rounded-full bg-orange-500 shrink-0 mt-1 shadow-lg shadow-orange-500/40 ring-4 ring-orange-400/30';
                title.className = 'text-base font-black uppercase tracking-widest mb-1 italic text-orange-400';
                title.innerText = 'SEDANG DINAS LUAR (ST)';
                descEl.innerText = `${targetUserName} ditugaskan pada agenda kedinasan resmi di tanggal ini.`;
                
                dutyList.innerHTML = duties.map(d => `
                    <div class="p-3 bg-white/10 rounded-xl border border-orange-400/30 text-xs text-white space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-orange-500/40 text-orange-200 border border-orange-400/50 uppercase tracking-wider">${d.category || 'DL'}</span>
                            <span class="text-[10px] text-gray-300 font-mono">${d.dates}</span>
                        </div>
                        <div class="font-black text-sm text-white leading-tight">${d.title}</div>
                        <div class="text-[11px] text-orange-200 font-mono">No: ${d.number}</div>
                        <div class="text-[11px] text-gray-300 flex items-center gap-1.5 pt-0.5">
                            <span class="text-orange-400">📍</span> <span>${d.location}</span>
                        </div>
                        <div class="pt-2 border-t border-white/10">
                            <a href="/letters/${d.id}" class="inline-flex items-center gap-1 text-[11px] font-bold text-orange-300 hover:text-white transition underline">
                                <span>Lihat Rincian Surat Tugas</span> &rarr;
                            </a>
                        </div>
                    </div>
                `).join('');
                dutyBox.classList.remove('hidden');
            } else {
                dutyBox.classList.add('hidden');
                dutyList.innerHTML = '';
                if (type === 'Holiday' || type === 'Shared Leave') {
                    dot.className = 'w-5 h-5 rounded-full bg-red-500 shrink-0 mt-1 shadow-lg shadow-red-500/20';
                    title.className = 'text-base font-black uppercase tracking-widest mb-1 italic text-red-400';
                    title.innerText = 'LIBUR / CUTI';
                    descEl.innerText = desc;
                } else {
                    dot.className = 'w-5 h-5 rounded-full bg-green-500 shrink-0 mt-1 shadow-lg shadow-green-500/20';
                    title.className = 'text-base font-black uppercase tracking-widest mb-1 italic text-emerald-400';
                    title.innerText = 'HARI KERJA ASN';
                    descEl.innerText = desc;
                }
            }
        }

        // Jalankan seleksi hari ini saat pertama kali halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const todayStr = '{{ now()->toDateString() }}';
            const todayEvent = @json($events->firstWhere('date', now()->toDateString()));
            const isWeekendToday = {{ now()->isWeekend() ? 'true' : 'false' }};
            const todayDesc = todayEvent ? todayEvent.description : (isWeekendToday ? 'Libur Akhir Pekan' : 'Hari Kerja ASN SIPEGA');
            const todayType = todayEvent ? todayEvent.type : (isWeekendToday ? 'Holiday' : 'Working Day');
            
            showDayDetail(todayStr, todayDesc, todayType);
        });
    </script>
</x-app-layout>
