<x-app-layout>
    <div class="bg-slate-100 min-h-screen py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- SUBNAVIGASI PIKET -->
            @include('piket.partials.nav')

            <!-- HEADER & FILTER -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📅</span>
                        <h1 class="text-xl font-black text-slate-900 uppercase">Kalender Piket Pelayanan ULT & Perpustakaan</h1>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Tampilan visual kalender bulanan interaktif dengan indikator status warna dinamis.</p>
                </div>

                <!-- NAVIGASI BULAN & TAHUN -->
                <form method="GET" action="{{ route('piket.schedules.calendar') }}" class="flex flex-wrap items-center gap-2.5 text-xs">
                    <select name="unit_id" class="text-xs rounded-xl border-slate-300 font-medium py-1.5 focus:border-blue-600" onchange="this.form.submit()">
                        <option value="">Semua Unit (ULT & Perpus)</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>

                    <select name="month" class="text-xs rounded-xl border-slate-300 font-bold py-1.5 focus:border-blue-600" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>

                    <select name="year" class="text-xs rounded-xl border-slate-300 font-bold py-1.5 focus:border-blue-600" onchange="this.form.submit()">
                        @for($y = date('Y') - 1; $y <= date('Y') + 2; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>

            <!-- LEGENDA STATUS WARNA (PERSIS SPESIFIKASI 4.G) -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs font-bold">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 shadow-sm"></span>
                        <span class="text-slate-700">Hijau: Jadwal Normal</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-rose-500 shadow-sm"></span>
                        <span class="text-slate-700">Merah: Konflik ST/Cuti (Perlu Pengganti)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-blue-500 shadow-sm"></span>
                        <span class="text-slate-700">Biru: Petugas Sudah Diganti</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-amber-500 shadow-sm"></span>
                        <span class="text-slate-700">Kuning: Menunggu Konfirmasi / Tukar</span>
                    </div>
                </div>

                <div class="text-[11px] text-slate-400 font-normal">
                    Klik pada tanggal/petugas untuk aksi cepat
                </div>
            </div>

            <!-- KALENDER BULANAN (GRID 7 HARI) -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
                <!-- Header Hari -->
                <div class="grid grid-cols-7 text-center font-black text-xs uppercase py-3 border-b border-slate-200 text-white" style="background-color: #003366;">
                    <div>Senin</div>
                    <div>Selasa</div>
                    <div>Rabu</div>
                    <div>Kamis</div>
                    <div>Jumat</div>
                    <div class="text-rose-300">Sabtu</div>
                    <div class="text-rose-300">Minggu</div>
                </div>

                @php
                    $firstDayOfWeek = $startDate->dayOfWeekIso; // 1 (Senin) - 7 (Minggu)
                    $daysInMonth = $startDate->daysInMonth;
                    $prevMonthDays = $firstDayOfWeek - 1;
                @endphp

                <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-slate-200 bg-slate-50">
                    <!-- Hari kosong bulan sebelumnya -->
                    @for($i = 0; $i < $prevMonthDays; $i++)
                        <div class="p-2 min-h-[120px] bg-slate-100/60 opacity-40"></div>
                    @endfor

                    <!-- Hari dalam bulan -->
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $currDate = \Carbon\Carbon::create($year, $month, $day);
                            $dateKey = $currDate->toDateString();
                            $dayAssignments = $assignments->get($dateKey, collect());
                            $isWeekend = $currDate->isWeekend();
                            $isToday = $currDate->isToday();
                        @endphp

                        <div class="p-2 min-h-[130px] transition {{ $isWeekend ? 'bg-slate-100/80' : 'bg-white hover:bg-blue-50/30' }} {{ $isToday ? 'ring-2 ring-blue-600 inset-0' : '' }}">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-extrabold text-xs {{ $isWeekend ? 'text-rose-600' : 'text-slate-800' }} {{ $isToday ? 'bg-blue-600 text-white w-5 h-5 rounded-full flex items-center justify-center font-mono' : '' }}">
                                    {{ $day }}
                                </span>
                                @if($isWeekend)
                                    <span class="text-[9px] font-bold text-rose-500 uppercase">Libur</span>
                                @elseif($dayAssignments->isNotEmpty())
                                    <span class="text-[9px] font-bold text-slate-400 font-mono">{{ $dayAssignments->count() }} Shift</span>
                                @endif
                            </div>

                            <!-- DAFTAR PETUGAS DI TANGGAL INI -->
                            <div class="space-y-1.5">
                                @forelse($dayAssignments as $asgn)
                                    <div class="p-1.5 rounded-lg border text-[10px] leading-tight space-y-0.5 shadow-sm
                                        @if($asgn->status === 'conflict') bg-rose-50 border-rose-300 text-rose-950
                                        @elseif($asgn->status === 'replaced') bg-blue-50 border-blue-300 text-blue-950
                                        @elseif($asgn->status === 'swapped') bg-purple-50 border-purple-300 text-purple-950
                                        @else bg-emerald-50 border-emerald-300 text-emerald-950 @endif">
                                        <div class="flex items-center justify-between font-black">
                                            <span class="truncate max-w-[85px]">{{ $asgn->unit->code }}</span>
                                            <span class="w-2 h-2 rounded-full
                                                @if($asgn->status === 'conflict') bg-rose-500
                                                @elseif($asgn->status === 'replaced') bg-blue-500
                                                @elseif($asgn->status === 'swapped') bg-purple-500
                                                @else bg-emerald-500 @endif">
                                            </span>
                                        </div>
                                        <div class="font-extrabold truncate" title="{{ $asgn->activeOfficer->name }}">
                                            {{ $asgn->activeOfficer->name }}
                                        </div>
                                        @if($asgn->backupOfficer)
                                            <div class="text-[9px] text-slate-500 truncate" title="Cadangan: {{ $asgn->backupOfficer->name }}">
                                                Cdg: {{ $asgn->backupOfficer->name }}
                                            </div>
                                        @endif
                                        @if($asgn->has_conflict && !$asgn->is_replaced)
                                            <a href="{{ route('piket.replacements.create', $asgn->id) }}" class="block text-center mt-1 text-[9px] bg-rose-600 text-white font-bold px-1.5 py-0.5 rounded shadow">
                                                Ganti Petugas ⚡
                                            </a>
                                        @endif
                                    </div>
                                @empty
                                    @if(!$isWeekend)
                                        <div class="text-[9px] text-slate-400 italic py-2 text-center">-</div>
                                    @endif
                                @endforelse
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
