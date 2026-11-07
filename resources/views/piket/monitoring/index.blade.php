<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight tracking-tight flex items-center gap-2">
                    <span>📡</span> Monitoring Pelaksanaan Piket & Presensi Harian
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Pemantauan kehadiran petugas di loket pelayanan ULT dan Perpustakaan serta pencatatan jumlah tamu/pengunjung
                </p>
            </div>
            <div class="text-xs font-mono bg-white px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 font-bold shadow-sm">
                Hari Ini: {{ \Carbon\Carbon::today('Asia/Makassar')->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @include('piket.partials.nav')

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg shadow-sm text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- STATISTIK RINGKAS BULANAN -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Petugas Terjadwal Hari Ini</div>
                        <div class="text-3xl font-black text-slate-800 mt-1">{{ $assignments->count() }} Orang</div>
                        <div class="text-xs text-slate-500 mt-0.5">ULT & Perpustakaan</div>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                        👥
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Kehadiran Bulan Ini</div>
                        <div class="text-3xl font-black text-emerald-600 mt-1">{{ $monthlyHadirCount }} Shift</div>
                        <div class="text-xs text-slate-500 mt-0.5">Presensi terlaksana</div>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                        ✅
                    </div>
                </div>

                <div class="bg-gradient-to-br from-sipega-navy to-slate-900 text-white p-5 rounded-2xl shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wider text-sky-300">Tamu / Pengunjung Terlayani</div>
                        <div class="text-3xl font-black mt-1">{{ $monthlyVisitorsCount }} Orang</div>
                        <div class="text-xs text-slate-300 mt-0.5">Bulan {{ \Carbon\Carbon::parse($date)->translatedFormat('F Y') }}</div>
                    </div>
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center text-2xl">
                        📈
                    </div>
                </div>
            </div>

            <!-- FILTER TANGGAL DAN UNIT -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('piket.monitoring.index') }}" class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Pilih Tanggal:</label>
                        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="text-xs rounded-lg border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium">
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Unit Pelayanan:</label>
                        <select name="unit_id" onchange="this.form.submit()" class="text-xs rounded-lg border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium">
                            <option value="">Semua Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <a href="{{ route('piket.monitoring.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-700 ml-auto">
                        Hari Ini
                    </a>
                </form>
            </div>

            <!-- DAFTAR PETUGAS TERJADWAL PADA TANGGAL INI -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-800">
                            Petugas Pelayanan Tanggal {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                        </h3>
                        <p class="text-xs text-slate-500">Isi status presensi dan catatan pelayanan harian di bawah ini.</p>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($assignments as $assign)
                        @php
                            $att = $assign->attendance;
                        @endphp
                        <div class="rounded-2xl border-2 {{ $att && $att->attendance_status === 'hadir' ? 'border-emerald-500 bg-emerald-50/20' : 'border-slate-200 bg-white' }} p-5 shadow-sm space-y-4 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black {{ $assign->unit->code === 'ULT' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $assign->unit->name }}
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-500">{{ $assign->role_label }}</span>
                                </div>

                                <div class="mt-3 flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-sipega-navy text-white flex items-center justify-center font-black text-sm shrink-0">
                                        {{ substr($assign->activeOfficer->name ?? 'P', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-800 text-sm leading-snug">
                                            {{ $assign->activeOfficer->name ?? '-' }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                            NIP: {{ $assign->activeOfficer->nip ?? '-' }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $assign->activeOfficer->unit_kerja ?? 'BPMP Kaltim' }}
                                        </div>
                                    </div>
                                </div>

                                @if($assign->is_replaced)
                                    <div class="mt-2 p-2 rounded bg-amber-50 border border-amber-200 text-[10px] text-amber-800">
                                        Menggantikan: <strong>{{ $assign->primaryOfficer->name ?? '-' }}</strong>
                                    </div>
                                @endif

                                <!-- STATUS PRESENSI -->
                                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-slate-500 font-bold">Status Kehadiran:</span>
                                    @if($att)
                                        @if($att->attendance_status === 'hadir')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">
                                                ✓ Hadir ({{ $att->check_in ?? '-' }})
                                            </span>
                                        @elseif($att->attendance_status === 'tidak_hadir')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800">
                                                ✕ Tidak Hadir
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-800">
                                                {{ ucfirst($att->attendance_status) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            Belum Dicatat
                                        </span>
                                    @endif
                                </div>

                                @if($att && $att->visitors_served_count > 0)
                                    <div class="text-[11px] text-slate-600 font-medium mt-1">
                                        Tamu terlayani: <strong>{{ $att->visitors_served_count }} orang</strong>
                                    </div>
                                @endif
                            </div>

                            <!-- TOMBOL INPUT PRESENSI & CATATAN -->
                            <div class="pt-3 border-t border-slate-100">
                                <button type="button" onclick="openPresensiModal('{{ $assign->id }}', '{{ addslashes($assign->activeOfficer->name ?? '') }}', '{{ $assign->unit->name }}', '{{ $att->attendance_status ?? 'hadir' }}', '{{ $att->check_in ?? '08:00' }}', '{{ $att->check_out ?? '16:00' }}', '{{ $att->visitors_served_count ?? 0 }}', '{{ addslashes($att->service_notes ?? '') }}', '{{ addslashes($att->issues_notes ?? '') }}')" 
                                        class="w-full py-2 bg-slate-100 hover:bg-sipega-navy hover:text-white text-slate-700 rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5 shadow-sm">
                                    <span>📝</span> Input / Edit Presensi
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 py-12 text-center text-slate-400">
                            <div class="text-4xl mb-2">🏖️</div>
                            <div class="font-bold text-slate-600 text-sm">Tidak ada jadwal piket pada tanggal ini</div>
                            <p class="text-xs text-slate-400 mt-1">Tanggal ini mungkin merupakan hari libur/akhir pekan, atau belum di-generate jadwal.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL PRESENSI & LAPORAN HARIAN -->
    <div id="modalPresensi" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100">
            <div class="p-5 bg-sipega-navy text-white flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black">Catatan Presensi & Pelayanan Piket</h3>
                    <p class="text-[11px] text-slate-300" id="modalSub">Input rekap kehadiran petugas dan jumlah tamu</p>
                </div>
                <button type="button" onclick="document.getElementById('modalPresensi').classList.add('hidden')" class="text-white/70 hover:text-white text-xl font-bold">
                    ✕
                </button>
            </div>

            <form id="formPresensi" method="POST" action="" class="p-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Kehadiran:</label>
                    <select name="attendance_status" id="in_status" class="w-full text-xs rounded-xl border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium p-2.5">
                        <option value="hadir">✓ Hadir Bertugas</option>
                        <option value="tidak_hadir">✕ Tidak Hadir (Alpa/Tanpa Keterangan)</option>
                        <option value="digantikan">🔄 Digantikan</option>
                        <option value="izin">📋 Izin Resmi</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jam Mulai (Check-in):</label>
                        <input type="time" name="check_in" id="in_check_in" class="w-full text-xs rounded-xl border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium p-2.5">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jam Selesai (Check-out):</label>
                        <input type="time" name="check_out" id="in_check_out" class="w-full text-xs rounded-xl border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium p-2.5">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Pengunjung / Tamu Terlayani:</label>
                    <input type="number" name="visitors_served_count" id="in_visitors" min="0" value="0" class="w-full text-xs rounded-xl border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium p-2.5">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Layanan:</label>
                    <textarea name="service_notes" id="in_notes" rows="2" placeholder="Uraian ringkas pelayanan hari ini (contoh: Konsultasi SPMB, peminjaman buku perpustakaan)..."
                              class="w-full text-xs rounded-xl border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium p-2.5"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kendala / Isu (Jika ada):</label>
                    <textarea name="issues_notes" id="in_issues" rows="2" placeholder="Catatan kendala teknis atau sarpras jika ada..."
                              class="w-full text-xs rounded-xl border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium p-2.5"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalPresensi').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-sipega-navy hover:bg-slate-800 text-white rounded-lg text-xs font-black shadow transition">
                        Simpan Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPresensiModal(assignId, officerName, unitName, status, inTime, outTime, visitors, notes, issues) {
            document.getElementById('modalSub').innerText = 'Petugas: ' + officerName + ' (' + unitName + ')';
            document.getElementById('formPresensi').action = '/piket/monitoring/' + assignId;
            document.getElementById('in_status').value = status;
            document.getElementById('in_check_in').value = inTime;
            document.getElementById('in_check_out').value = outTime;
            document.getElementById('in_visitors').value = visitors;
            document.getElementById('in_notes').value = notes;
            document.getElementById('in_issues').value = issues;
            document.getElementById('modalPresensi').classList.remove('hidden');
        }
    </script>
</x-app-layout>
