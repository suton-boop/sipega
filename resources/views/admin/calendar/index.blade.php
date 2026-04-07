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
        .active-month { background-color: #003366 !important; color: white !important; font-weight: 800; }
        .sidebar-dark { background-color: #003366; color: white; min-height: 500px; }
        .text-sipega-navy { color: #003366; }
        .bg-sipega-navy { background-color: #003366; }
        .bg-sipega-orange { background-color: #ff8c00; }
        .text-sipega-orange { color: #ff8c00; }
        .day-cell span { line-height: 1; }
        .header-grid { display: grid; grid-template-columns: repeat(7, 1fr); width: 100%; text-align: center; margin-bottom: 24px; border-bottom: 1px solid #f3f4f6; pb: 4 }
    </style>

    <div class="py-10 bg-[#f8fafc]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Horizontal Month Tabs -->
            <div class="flex items-center justify-between mb-2 bg-white p-2 rounded-xl shadow-sm border border-gray-100 overflow-x-auto no-scrollbar">
                @php
                    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    $currentMonthIdx = (int)request('month', now()->month);
                @endphp
                @foreach($months as $idx => $m)
                    <a href="{{ route('admin.calendar.index', ['month' => $idx + 1]) }}" 
                       class="month-tab px-6 py-2 rounded-lg text-[10px] font-extrabold uppercase tracking-widest text-gray-400 hover:text-sipega-navy {{ $currentMonthIdx == ($idx + 1) ? 'active-month scale-105 shadow-md' : '' }}">
                        {{ $m }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white rounded-[32px] shadow-2xl overflow-hidden border border-gray-100">
                <div class="flex flex-col lg:flex-row">
                    
                    <!-- Left Section: The Grid -->
                    <div class="flex-1 p-10 lg:p-14">
                        <div class="flex justify-between items-end mb-12">
                            <div>
                                <h3 class="text-4xl font-black text-sipega-navy uppercase tracking-tighter">{{ $months[$currentMonthIdx - 1] }} 2026</h3>
                                <p class="text-[10px] font-black text-sipega-orange uppercase tracking-[0.4em] mt-1 italic">Tahun Anggaran Berjalan</p>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Libur/Akhir Pekan</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Hari Kerja ASN</span>
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
                                $startOfMonth = \Carbon\Carbon::create(2026, $currentMonthIdx, 1);
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
                                    $cDate = \Carbon\Carbon::create(2026, $currentMonthIdx, $day);
                                    $dateStr = $cDate->toDateString();
                                    $event = $events->firstWhere('date', $dateStr);
                                    $isWeekend = $cDate->isWeekend();
                                    $isHoliday = ($event && ($event->type == 'Holiday' || $event->type == 'Shared Leave'));
                                    
                                    $cellClass = ($isWeekend || $isHoliday) ? 'holiday' : 'work';
                                    $dotColor = ($isWeekend || $isHoliday) ? 'bg-red-500' : 'bg-green-500';
                                @endphp
                                <div class="day-cell {{ $cellClass }}" 
                                     onclick="showDayDetail('{{ $dateStr }}', '{{ $event ? $event->description : ($isWeekend ? 'Libur Akhir Pekan' : 'Hari Kerja ASN SIPEGA') }}', '{{ $event ? $event->type : ($isWeekend ? 'Holiday' : 'Working Day') }}')">
                                    <span class="text-xl font-black">{{ $day }}</span>
                                    <div class="w-1.5 h-1.5 rounded-full {{ $dotColor }} mt-2 shadow-sm"></div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Right Section: Detail Sidebar -->
                    <div id="side-panel" class="sidebar-dark w-full lg:w-96 p-12">
                        <div id="detail-date" class="text-3xl font-black italic tracking-tighter mb-2 border-b border-white/10 pb-4">
                            {{ now()->translatedFormat('d F Y') }}
                        </div>
                        
                        <div class="mt-10 space-y-8">
                            <div class="flex items-start gap-5">
                                <div id="detail-dot" class="w-5 h-5 rounded-full bg-green-500 shrink-0 mt-1 shadow-lg shadow-green-500/20"></div>
                                <div>
                                    <h4 id="detail-title" class="text-lg font-black uppercase tracking-widest mb-2 italic text-sipega-orange">Hari Kerja ASN</h4>
                                    <p id="detail-desc" class="text-sm text-gray-400 font-medium leading-relaxed italic">Pimpinan dan Pegawai beroperasi sesuai jam kerja standar SIPEGA.</p>
                                </div>
                            </div>

                            <!-- Dashboard Editor Form -->
                            <div class="pt-12 border-t border-white/5">
                                <h5 class="text-[10px] font-black text-gray-500 uppercase tracking-[0.3em] mb-6 italic">Pengaturan Hari Khusus</h5>
                                <form action="{{ route('admin.calendar.store') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="date" id="form-date" value="{{ now()->toDateString() }}">
                                    
                                    <div>
                                        <select name="type" required class="w-full bg-white/5 border-white/10 rounded-2xl text-[10px] font-bold py-4 px-6 focus:ring-sipega-orange focus:border-sipega-orange text-white">
                                            <option value="Working Day" class="text-black">Hari Kerja Khusus</option>
                                            <option value="Shared Leave" class="text-black">Cuti Bersama</option>
                                            <option value="Holiday" class="text-black">Hari Libur Nasional</option>
                                            <option value="Overtime" class="text-black">Lembur Kolektif</option>
                                        </select>
                                    </div>

                                    <div>
                                        <input type="text" name="description" placeholder="Keterangan..." required class="w-full bg-white/5 border-white/10 rounded-2xl text-[10px] font-bold py-4 px-6 focus:ring-sipega-orange focus:border-sipega-orange text-white placeholder-gray-500">
                                    </div>

                                    <button type="submit" class="w-full bg-sipega-orange hover:bg-orange-600 text-white font-black py-5 rounded-2xl text-[10px] uppercase tracking-[0.2em] shadow-2xl transition-all hover:-translate-y-1">
                                        Perbarui SIPEGA Calendar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDayDetail(date, desc, type) {
            const dateObj = new Date(date);
            const options = { day: '2-digit', month: 'long', year: 'numeric' };
            const formattedDate = dateObj.toLocaleDateString('id-ID', options);
            
            document.getElementById('detail-date').innerText = formattedDate.toUpperCase();
            document.getElementById('detail-desc').innerText = desc;
            document.getElementById('form-date').value = date;

            const dot = document.getElementById('detail-dot');
            const title = document.getElementById('detail-title');
            
            if (type === 'Holiday' || type === 'Shared Leave') {
                dot.className = 'w-5 h-5 rounded-full bg-red-500 shrink-0 mt-1 shadow-lg shadow-red-500/20';
                title.innerText = 'LIBUR / CUTI';
            } else {
                dot.className = 'w-5 h-5 rounded-full bg-green-500 shrink-0 mt-1 shadow-lg shadow-green-500/20';
                title.innerText = 'HARI KERJA ASN';
            }
        }
    </script>
</x-app-layout>
