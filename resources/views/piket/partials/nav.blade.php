<!-- SUBNAVIGASI 12 MENU MANAJEMEN PIKET ULT & PERPUSTAKAAN -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-3 mb-6">
    <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-2 px-2">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg flex items-center justify-center font-bold text-white text-xs" style="background-color: #003366;">🛡️</span>
            <span class="text-xs font-black uppercase text-slate-800 tracking-wider">Modul Manajemen Piket Tahunan • ULT & Perpustakaan</span>
        </div>
        <div class="text-[11px] text-slate-500 font-medium">BPMP Provinsi Kalimantan Timur</div>
    </div>
    
    <div class="flex flex-wrap items-center gap-1.5 text-xs">
        <a href="{{ route('piket.dashboard') }}" class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 {{ request()->routeIs('piket.dashboard') ? 'bg-[#003366] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            <span>📊</span>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('piket.officers.index') }}" class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 {{ request()->routeIs('piket.officers.*') ? 'bg-[#003366] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            <span>👥</span>
            <span>Data Petugas</span>
        </a>
        <a href="{{ route('piket.schedules.index') }}" class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 {{ request()->routeIs('piket.schedules.index', 'piket.schedules.show', 'piket.schedules.create') ? 'bg-[#003366] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            <span>🗓️</span>
            <span>Jadwal Tahunan</span>
        </a>
        <a href="{{ route('piket.schedules.calendar') }}" class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 {{ request()->routeIs('piket.schedules.calendar') ? 'bg-[#003366] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            <span>📅</span>
            <span>Kalender Piket</span>
        </a>
        <a href="{{ route('piket.conflicts.dinas') }}" class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 {{ request()->routeIs('piket.conflicts.dinas') ? 'bg-[#003366] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            <span>✈️</span>
            <span>Penugasan Kedinasan</span>
        </a>
        <a href="{{ route('piket.conflicts.index') }}" class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 {{ request()->routeIs('piket.conflicts.index') ? 'bg-[#003366] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            <span>⚠️</span>
            <span>Deteksi Konflik</span>
        </a>
        <a href="{{ route('piket.replacements.index') }}" class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 {{ request()->routeIs('piket.replacements.*') ? 'bg-[#003366] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            <span>🔄</span>
            <span>Penggantian Petugas</span>
        </a>
        <a href="{{ route('piket.swaps.index') }}" class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 {{ request()->routeIs('piket.swaps.*') ? 'bg-[#003366] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            <span>🔀</span>
            <span>Tukar Jadwal</span>
        </a>
        <a href="{{ route('piket.monitoring.index') }}" class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 {{ request()->routeIs('piket.monitoring.*') ? 'bg-[#003366] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            <span>⏱️</span>
            <span>Monitoring & Presensi</span>
        </a>
        <a href="{{ route('piket.reports.index') }}" class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 {{ request()->routeIs('piket.reports.*') ? 'bg-[#003366] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            <span>📑</span>
            <span>Laporan & Rekap</span>
        </a>
        <a href="{{ route('piket.schedules.settings') }}" class="px-3 py-1.5 rounded-xl font-bold transition flex items-center gap-1.5 {{ request()->routeIs('piket.schedules.settings') ? 'bg-[#003366] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
            <span>⚙️</span>
            <span>Pengaturan Unit</span>
        </a>
    </div>
</div>
