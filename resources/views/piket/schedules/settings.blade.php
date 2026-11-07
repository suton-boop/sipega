<x-app-layout>
    <div class="bg-slate-100 min-h-screen py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- SUBNAVIGASI PIKET -->
            @include('piket.partials.nav')

            <!-- FLASH MESSAGE -->
            @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-xl text-xs text-emerald-900 font-bold shadow-sm">
                ✓ {{ session('success') }}
            </div>
            @endif

            <!-- HEADER -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-lg shadow" style="background-color: #003366;">
                        ⚙️
                    </div>
                    <div>
                        <h1 class="text-lg font-black uppercase text-slate-900">Pengaturan Master Unit Pelayanan Piket</h1>
                        <p class="text-xs text-slate-500">Konfigurasi parameter operasional, jam pelayanan, kuota petugas harian, dan petugas cadangan untuk ULT & Perpustakaan.</p>
                    </div>
                </div>
            </div>

            <!-- FORM SETIAP UNIT -->
            <div class="space-y-6">
                @foreach($units as $u)
                    <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-200 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $u->code === 'ULT' ? '🏢' : '📚' }}</span>
                                <h2 class="text-base font-black text-[#003366] uppercase">{{ $u->name }} ({{ $u->code }})</h2>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Unit Aktif
                            </span>
                        </div>

                        <form method="POST" action="{{ route('piket.schedules.update_unit_settings', $u->id) }}" class="space-y-4 text-xs">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Nama Unit Pelayanan</label>
                                    <input type="text" name="name" value="{{ $u->name }}" class="w-full text-xs rounded-xl border-slate-300 font-bold" required>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Lokasi Ruangan Pelayanan</label>
                                    <input type="text" name="location" value="{{ $u->location }}" class="w-full text-xs rounded-xl border-slate-300" placeholder="e.g. Gedung A Lantai 1">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Jam Buka Pelayanan</label>
                                    <input type="time" name="start_time" value="{{ substr($u->start_time, 0, 5) }}" class="w-full text-xs rounded-xl border-slate-300 font-mono font-bold" required>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Jam Tutup Pelayanan</label>
                                    <input type="time" name="end_time" value="{{ substr($u->end_time, 0, 5) }}" class="w-full text-xs rounded-xl border-slate-300 font-mono font-bold" required>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Kuota Petugas Utama / Hari</label>
                                    <input type="number" name="daily_officers_count" value="{{ $u->daily_officers_count }}" min="1" max="10" class="w-full text-xs rounded-xl border-slate-300 font-mono font-bold text-center" required>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Kuota Cadangan / Hari</label>
                                    <input type="number" name="daily_backups_count" value="{{ $u->daily_backups_count }}" min="0" max="5" class="w-full text-xs rounded-xl border-slate-300 font-mono font-bold text-center" required>
                                </div>
                            </div>

                            <div class="flex items-center justify-end pt-3 border-t border-slate-100">
                                <button type="submit" class="px-5 py-2 rounded-xl text-white font-bold shadow transition" style="background-color: #003366;">
                                    Simpan Pengaturan {{ $u->code }}
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
