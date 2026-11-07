<x-app-layout>
    <div class="bg-slate-100 min-h-screen py-6" x-data="{ periodType: 'tahunan', month: '{{ date('n') }}' }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- SUBNAVIGASI PIKET -->
            @include('piket.partials.nav')

            <!-- FLASH MESSAGE -->
            @if(session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-600 p-4 rounded-xl text-xs text-rose-900 font-bold shadow-sm">
                ✕ {{ session('error') }}
            </div>
            @endif

            <!-- CARD UTAMA -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-200 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-white text-lg shadow" style="background-color: #003366;">
                            ⚡
                        </div>
                        <div>
                            <h1 class="text-lg font-black uppercase text-slate-900">Generator Jadwal Piket Otomatis</h1>
                            <p class="text-xs text-slate-500">Penyusunan jadwal dinas pelayanan ULT & Perpustakaan berbasis rotasi adil dan bebas benturan tugas kedinasan.</p>
                        </div>
                    </div>
                    <a href="{{ route('piket.schedules.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-bold">← Kembali</a>
                </div>

                <form method="POST" action="{{ route('piket.schedules.store') }}" class="space-y-6 text-xs">
                    @csrf

                    <!-- 1. PILIH UNIT PELAYANAN -->
                    <div>
                        <label class="block font-black text-slate-900 mb-2 uppercase tracking-wider text-[11px]">
                            1. Pilih Unit Pelayanan
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($units as $u)
                                <label class="border-2 rounded-2xl p-4 flex items-start gap-3 cursor-pointer hover:bg-slate-50 transition border-slate-200 has-[:checked]:border-blue-700 has-[:checked]:bg-blue-50/50">
                                    <input type="radio" name="piket_unit_id" value="{{ $u->id }}" class="mt-0.5 text-blue-700 focus:ring-blue-600" {{ $loop->first ? 'checked' : '' }} required>
                                    <div>
                                        <div class="font-extrabold text-sm text-[#003366] flex items-center gap-1.5">
                                            <span>{{ $u->code === 'ULT' ? '🏢' : '📚' }}</span>
                                            <span>{{ $u->name }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">{{ $u->description }}</div>
                                        <div class="text-[10px] text-blue-800 font-bold mt-2 bg-white px-2 py-1 rounded-lg border border-slate-200 inline-block">
                                            Petugas: {{ $u->daily_officers_count }} Utama + {{ $u->daily_backups_count }} Cadangan / Hari • {{ $u->eligible_employees_count }} Pegawai Terdaftar
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 2. PILIH PERIODE PENJADWALAN -->
                    <div class="pt-4 border-t border-slate-100">
                        <label class="block font-black text-slate-900 mb-2 uppercase tracking-wider text-[11px]">
                            2. Periode Penjadwalan
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="border-2 rounded-xl p-3 flex items-center gap-3 cursor-pointer hover:bg-slate-50 border-slate-200 has-[:checked]:border-blue-700 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="period_type" value="tahunan" x-model="periodType" class="text-blue-700 focus:ring-blue-600">
                                <div>
                                    <div class="font-bold text-slate-900">Jadwal Tahunan Penuh (1 Tahun)</div>
                                    <div class="text-[10px] text-slate-500">Mencakup bulan Januari s.d Desember selama satu tahun anggaran.</div>
                                </div>
                            </label>

                            <label class="border-2 rounded-xl p-3 flex items-center gap-3 cursor-pointer hover:bg-slate-50 border-slate-200 has-[:checked]:border-blue-700 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="period_type" value="bulanan" x-model="periodType" class="text-blue-700 focus:ring-blue-600">
                                <div>
                                    <div class="font-bold text-slate-900">Jadwal Bulanan</div>
                                    <div class="text-[10px] text-slate-500">Menyusun jadwal untuk satu bulan berjalan tertentu.</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 3. TAHUN & BULAN -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tahun Anggaran Pelaksanaan</label>
                            <select name="year" class="w-full text-xs rounded-xl border-slate-300 font-bold bg-slate-50 py-2 focus:border-blue-600" required>
                                @for($y = date('Y'); $y <= date('Y') + 2; $y++)
                                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div x-show="periodType === 'bulanan'">
                            <label class="block font-bold text-slate-700 mb-1">Pilih Bulan</label>
                            <select name="month" x-model="month" class="w-full text-xs rounded-xl border-slate-300 font-bold bg-slate-50 py-2 focus:border-blue-600">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- 4. JUDUL PAKET JADWAL (OPSIONAL) -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Judul Paket Jadwal (Opsional / Otomatis Sistem)</label>
                        <input type="text" name="title" placeholder="Kosongkan untuk penamaan otomatis standar..." class="w-full text-xs rounded-xl border-slate-300 py-2 focus:border-blue-600">
                    </div>

                    <!-- KOTAK ALGORITMA PENJADWALAN CERDAS -->
                    <div class="bg-amber-50 rounded-2xl p-4 border border-amber-200/80 space-y-2">
                        <div class="font-black text-amber-950 uppercase text-[10px] flex items-center gap-1.5">
                            <span>🛡️</span>
                            <span>Algoritma Pembagian Jadwal Memastikan:</span>
                        </div>
                        <ul class="text-[11px] text-amber-900 space-y-1 list-disc list-inside">
                            <li><strong>Pemerataan Beban Piket:</strong> Algoritma menyeimbangkan total giliran piket antarpegawai secara proporsional.</li>
                            <li><strong>Anti-Benturan Kedinasan:</strong> Langsung mengecek data Surat Tugas (ST), Tugas Luar, dan Cuti ASN saat giliran disusun.</li>
                            <li><strong>Eliminasi Hari Libur:</strong> Melewati akhir pekan (Sabtu-Minggu) dan Hari Libur Nasional & Cuti Bersama secara otomatis.</li>
                            <li><strong>Petugas Cadangan Terpasang:</strong> Setiap hari dinas dilengkapi petugas cadangan untuk antisipasi berhalangan mendadak.</li>
                        </ul>
                    </div>

                    <!-- TOMBOL AKSI GENERATE -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <a href="{{ route('piket.schedules.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">
                            Batal
                        </a>
                        <button type="submit" class="px-7 py-3 rounded-xl font-black text-xs uppercase tracking-wider text-white shadow-lg transition flex items-center gap-2" style="background: linear-gradient(135deg, #003366 0%, #005a9c 100%);">
                            <span>⚡</span>
                            <span>Mulai Generate Jadwal Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
