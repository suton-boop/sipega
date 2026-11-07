<x-app-layout>
    <div class="bg-slate-100 min-h-screen py-6" x-data="{ 
        showModalKantor: false, 
        showModalRuangan: false,
        outTimeKantor: '{{ now()->format('H:i') }}',
        returnTimeKantor: '{{ now()->addHours(2)->format('H:i') }}',
        outTimeRuangan: '{{ now()->format('H:i') }}',
        returnTimeRuangan: '{{ now()->addHours(1)->format('H:i') }}'
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- FLASH MESSAGE -->
            @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-xl text-xs text-emerald-900 font-bold shadow-sm">
                ✓ {{ session('success') }}
            </div>
            @endif

            <!-- MIK TOP BLUE HEADER (ELEGANT & HIGH CONTRAST) -->
            <div class="rounded-2xl shadow-lg overflow-hidden border border-blue-900/20" style="background: linear-gradient(135deg, #005293 0%, #0070ba 50%, #008be3 100%); color: #ffffff;">
                <div class="px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-full border-4 border-white/90 flex items-center justify-center font-black text-2xl text-white shadow-inner" style="background: rgba(255,255,255,0.15);">
                            <span class="w-4 h-4 rounded-full bg-white shadow"></span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-blue-100 tracking-wide uppercase">Sistem Kepegawaian SIPEGA</div>
                            <h1 class="text-2xl md:text-3xl font-black tracking-tight leading-none text-white uppercase drop-shadow-sm flex items-baseline gap-2">
                                <span>MiK</span>
                                <span class="text-sm font-semibold tracking-normal text-blue-100 lowercase">(Monitoring Izin Karyawan)</span>
                            </h1>
                            <div class="text-[11px] text-blue-100/90 mt-1 font-medium">Balai Penjaminan Mutu Pendidikan (BPMP) Provinsi Kalimantan Timur</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <a href="{{ route('mik.form_kantor') }}" class="px-3.5 py-2 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center gap-1.5" style="background: rgba(255,255,255,0.2);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Form_Kantor</span>
                        </a>
                        <a href="{{ route('mik.form_ruangan') }}" class="px-3.5 py-2 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center gap-1.5" style="background: rgba(255,255,255,0.2);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                            <span>Form_Ruangan</span>
                        </a>
                        <a href="{{ route('mik.index') }}" class="px-4 py-2 bg-white text-blue-900 text-xs font-black rounded-xl hover:bg-blue-50 transition shadow">
                            Refresh Data
                        </a>
                    </div>
                </div>

                <!-- SUB-INFO: TIDAK MEMPENGARUHI TUKIN & PERFORMA -->
                <div class="px-6 py-2.5 border-t border-white/20 text-xs text-blue-50 flex flex-wrap items-center justify-between gap-2" style="background: rgba(0, 35, 75, 0.45);">
                    <span class="flex items-center gap-1.5 font-medium">
                        <span class="text-amber-300 text-sm">💡</span>
                        <span><strong>Catatan Ketertiban:</strong> Modul MiK semata-mata untuk penegakan ketertiban jam kerja. <strong>Tidak masuk dalam perhitungan potongan tunjangan (Tukin) dan tidak mengurangi nilai performa SKP.</strong></span>
                    </span>
                    <a href="{{ route('mik.approvals') }}" class="text-white hover:text-amber-200 font-bold text-xs underline underline-offset-2">Persetujuan Izin Atasan →</a>
                </div>
            </div>

            <!-- ACTION BUTTONS: AJUKAN IZIN -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <button type="button" @click="showModalKantor = true" class="px-4 py-2.5 rounded-xl shadow-md transition flex items-center gap-2 text-xs font-bold text-white" style="background-color: #0070ba;">
                        <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Izin Keluar Kantor</span>
                    </button>
                    <button type="button" @click="showModalRuangan = true" class="px-4 py-2.5 rounded-xl shadow-md transition flex items-center gap-2 text-xs font-bold text-white" style="background-color: #2e7d32;">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Izin Keluar Ruangan</span>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('mik.approvals') }}" class="px-4 py-2.5 rounded-xl shadow-md transition flex items-center gap-1.5 text-xs font-bold text-white" style="background-color: #1e293b;">
                        <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Konsol Persetujuan (Ketua GM, Kasubag & Kepala)</span>
                    </a>
                </div>
            </div>

            <!-- ================= SECTION 1: KANTOR ================= -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-200 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- 4 Counter Badges (Persis Gambar 1) -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- Belum direspon (Merah) -->
                        <div class="rounded-xl px-4 py-2 text-center min-w-[95px] shadow-sm border border-red-700/20" style="background-color: #d32f2f; color: #ffffff;">
                            <div class="text-[10px] font-bold text-white/90">Belum direspon</div>
                            <div class="text-2xl font-black mt-0.5 leading-none text-white">{{ $statsKantor['belum_direspon'] }}</div>
                        </div>
                        <!-- Disetujui (Hijau) -->
                        <div class="rounded-xl px-4 py-2 text-center min-w-[95px] shadow-sm border border-green-700/20" style="background-color: #2e7d32; color: #ffffff;">
                            <div class="text-[10px] font-bold text-white/90">Disetujui</div>
                            <div class="text-2xl font-black mt-0.5 leading-none text-white">{{ $statsKantor['disetujui'] }}</div>
                        </div>
                        <!-- Tidak disetujui (Kuning Emas) -->
                        <div class="rounded-xl px-4 py-2 text-center min-w-[95px] shadow-sm border border-amber-600/20" style="background-color: #f57f17; color: #ffffff;">
                            <div class="text-[10px] font-bold text-white/90">Tidak disetujui</div>
                            <div class="text-2xl font-black mt-0.5 leading-none text-white">{{ $statsKantor['tidak_disetujui'] }}</div>
                        </div>
                        <!-- Dibatalkan (Ungu) -->
                        <div class="rounded-xl px-4 py-2 text-center min-w-[95px] shadow-sm border border-indigo-700/20" style="background-color: #5c6bc0; color: #ffffff;">
                            <div class="text-[10px] font-bold text-white/90">Dibatalkan</div>
                            <div class="text-2xl font-black mt-0.5 leading-none text-white">{{ $statsKantor['dibatalkan'] }}</div>
                        </div>

                        <!-- Pill Button Persetujuan -->
                        <a href="{{ route('mik.approvals', ['type' => 'kantor']) }}" class="px-5 py-2.5 rounded-full font-bold text-xs shadow-md border border-white/40 text-white transition hover:scale-105" style="background: linear-gradient(135deg, #0070ba 0%, #1565c0 100%);">
                            Persetujuan Kantor →
                        </a>
                    </div>

                    <!-- Label Besar KANTOR di Kanan (Persis Gambar 1) -->
                    <div class="text-right">
                        <a href="{{ route('mik.form_kantor') }}" class="text-2xl md:text-3xl font-black text-[#0070ba] tracking-tight hover:underline flex items-center gap-2 justify-end">
                            <span>KANTOR</span>
                            <span class="text-xs font-bold text-slate-400">↗</span>
                        </a>
                        <div class="text-[11px] text-slate-500">Izin keluar area kantor BPMP</div>
                    </div>
                </div>

                <!-- TABLE KANTOR -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="text-white text-xs font-bold" style="background-color: #0070ba;">
                            <tr>
                                <th class="px-4 py-3 border-b border-blue-600">Nama Pegawai ▾</th>
                                <th class="px-4 py-3 border-b border-blue-600">Tanggal</th>
                                <th class="px-4 py-3 border-b border-blue-600">Rentang Jam & Durasi</th>
                                <th class="px-3 py-3 border-b border-blue-600 text-center">Status</th>
                                <th class="px-4 py-3 border-b border-blue-600">Pemberi Izin</th>
                                <th class="px-3 py-3 border-b border-blue-600 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($recentKantor as $item)
                                <tr class="hover:bg-blue-50/60 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-extrabold text-slate-900">{{ $item->employee_name }}</div>
                                        <div class="text-[10px] text-blue-700 font-semibold">{{ $item->destination ? '🏢 ' . $item->destination : '' }}</div>
                                        <div class="text-[11px] text-slate-600 italic">"{{ $item->purpose }}"</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-800 font-medium">
                                        {{ $item->date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-mono font-bold text-blue-900 text-xs">
                                            {{ $item->time_range_text }}
                                        </div>
                                        <div class="text-[10px] font-semibold text-emerald-700 mt-0.5">
                                            ⏱️ {{ $item->duration_text }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded text-[10px] font-bold lowercase shadow-sm
                                            @if($item->status === 'disetujui') bg-emerald-100 text-emerald-800 border border-emerald-300
                                            @elseif($item->status === 'tidak disetujui') bg-amber-100 text-amber-800 border border-amber-300
                                            @elseif($item->status === 'dibatalkan') bg-slate-200 text-slate-700 border border-slate-300
                                            @else bg-rose-100 text-rose-800 border border-rose-300 @endif">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-semibold text-slate-800">
                                        {{ $item->responder_role ?? 'Kasubbag. Umum' }}
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        @if(!$item->return_time && $item->status === 'disetujui')
                                            <form method="POST" action="{{ route('mik.return', $item->id) }}">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded text-[10px] font-bold text-white shadow" style="background-color: #2e7d32;">
                                                    ✓ Catat Kembali
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">Tidak ada data izin keluar kantor</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ================= SECTION 2: RUANGAN ================= -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-200 space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- 4 Counter Badges (Persis Gambar 1) -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <div class="rounded-xl px-4 py-2 text-center min-w-[95px] shadow-sm border border-red-700/20" style="background-color: #d32f2f; color: #ffffff;">
                            <div class="text-[10px] font-bold text-white/90">Belum direspon</div>
                            <div class="text-2xl font-black mt-0.5 leading-none text-white">{{ $statsRuangan['belum_direspon'] }}</div>
                        </div>
                        <div class="rounded-xl px-4 py-2 text-center min-w-[95px] shadow-sm border border-green-700/20" style="background-color: #2e7d32; color: #ffffff;">
                            <div class="text-[10px] font-bold text-white/90">Disetujui</div>
                            <div class="text-2xl font-black mt-0.5 leading-none text-white">{{ $statsRuangan['disetujui'] }}</div>
                        </div>
                        <div class="rounded-xl px-4 py-2 text-center min-w-[95px] shadow-sm border border-amber-600/20" style="background-color: #f57f17; color: #ffffff;">
                            <div class="text-[10px] font-bold text-white/90">Tidak disetujui</div>
                            <div class="text-2xl font-black mt-0.5 leading-none text-white">{{ $statsRuangan['tidak_disetujui'] }}</div>
                        </div>
                        <div class="rounded-xl px-4 py-2 text-center min-w-[95px] shadow-sm border border-indigo-700/20" style="background-color: #5c6bc0; color: #ffffff;">
                            <div class="text-[10px] font-bold text-white/90">Dibatalkan</div>
                            <div class="text-2xl font-black mt-0.5 leading-none text-white">{{ $statsRuangan['dibatalkan'] }}</div>
                        </div>

                        <!-- Pill Button Persetujuan -->
                        <a href="{{ route('mik.approvals', ['type' => 'ruangan']) }}" class="px-5 py-2.5 rounded-full font-bold text-xs shadow-md border border-white/40 text-white transition hover:scale-105" style="background: linear-gradient(135deg, #0070ba 0%, #1565c0 100%);">
                            Persetujuan Ruangan →
                        </a>
                    </div>

                    <!-- Label Besar RUANGAN di Kanan (Persis Gambar 1) -->
                    <div class="text-right">
                        <a href="{{ route('mik.form_ruangan') }}" class="text-2xl md:text-3xl font-black text-[#0070ba] tracking-tight hover:underline flex items-center gap-2 justify-end">
                            <span>RUANGAN</span>
                            <span class="text-xs font-bold text-slate-400">↗</span>
                        </a>
                        <div class="text-[11px] text-slate-500">Izin pergerakan antar ruangan internal</div>
                    </div>
                </div>

                <!-- TABLE RUANGAN -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="text-white text-xs font-bold" style="background-color: #0070ba;">
                            <tr>
                                <th class="px-4 py-3 border-b border-blue-600">Nama Pegawai ▾</th>
                                <th class="px-4 py-3 border-b border-blue-600">Tanggal</th>
                                <th class="px-4 py-3 border-b border-blue-600">Rentang Jam & Durasi</th>
                                <th class="px-3 py-3 border-b border-blue-600 text-center">Status</th>
                                <th class="px-4 py-3 border-b border-blue-600">Pemberi Izin</th>
                                <th class="px-3 py-3 border-b border-blue-600 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($recentRuangan as $item)
                                <tr class="hover:bg-blue-50/60 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-extrabold text-slate-900">{{ $item->employee_name }}</div>
                                        <div class="text-[10px] text-blue-700 font-semibold">{{ $item->destination ? '🚪 Menuju: ' . $item->destination : '' }}</div>
                                        <div class="text-[11px] text-slate-600 italic">"{{ $item->purpose }}"</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-800 font-medium">
                                        {{ $item->date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-mono font-bold text-blue-900 text-xs">
                                            {{ $item->time_range_text }}
                                        </div>
                                        <div class="text-[10px] font-semibold text-emerald-700 mt-0.5">
                                            ⏱️ {{ $item->duration_text }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded text-[10px] font-bold lowercase shadow-sm
                                            @if($item->status === 'disetujui') bg-emerald-100 text-emerald-800 border border-emerald-300
                                            @elseif($item->status === 'tidak disetujui') bg-amber-100 text-amber-800 border border-amber-300
                                            @elseif($item->status === 'dibatalkan') bg-slate-200 text-slate-700 border border-slate-300
                                            @else bg-rose-100 text-rose-800 border border-rose-300 @endif">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-semibold text-slate-800">
                                        {{ $item->responder_role ?? 'Ketua Gugus Mutu' }}
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        @if(!$item->return_time && $item->status === 'disetujui')
                                            <form method="POST" action="{{ route('mik.return', $item->id) }}">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded text-[10px] font-bold text-white shadow" style="background-color: #2e7d32;">
                                                    ✓ Catat Kembali
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">Tidak ada data izin keluar ruangan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ================= MODAL PENGAJUAN IZIN KELUAR KANTOR ================= -->
        <div x-show="showModalKantor" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModalKantor" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModalKantor = false"></div>
                <div x-show="showModalKantor" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white shadow-sm" style="background-color: #0070ba;">🏢</span>
                            <div>
                                <h3 class="text-sm font-black uppercase text-slate-900">Formulir Izin Keluar Kantor</h3>
                                <div class="text-[10px] text-slate-500">Penugasan & Keperluan Luar Kantor BPMP Kalimantan Timur</div>
                            </div>
                        </div>
                        <button @click="showModalKantor = false" class="text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>
                    </div>

                    <form method="POST" action="{{ route('mik.store') }}" class="space-y-4 text-xs">
                        @csrf
                        <input type="hidden" name="type" value="kantor">

                        @if(in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator']))
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Pegawai</label>
                            <select name="user_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba] font-medium bg-slate-50">
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ auth()->id() === $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->gugus_mutu ?? 'Non-GM' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Nama Pemohon</label>
                                <input type="text" value="{{ auth()->user()->name }}" class="w-full text-xs rounded-xl border-slate-200 bg-slate-100 text-slate-700 font-bold" readonly>
                            </div>
                        @endif

                        <!-- RENTANG JAM & TANGGAL -->
                        <div class="bg-blue-50/60 p-3.5 rounded-xl border border-blue-200/60 space-y-3">
                            <div>
                                <label class="block font-bold text-blue-950 mb-1">Tanggal Izin</label>
                                <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" class="w-full text-xs rounded-xl border-slate-300 bg-white font-medium" required>
                            </div>
                            
                            <div>
                                <label class="block font-bold text-blue-950 mb-1">Rentang Jam Keluar & Estimasi Kembali</label>
                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <span class="text-[10px] text-slate-600 font-semibold block mb-0.5">Waktu Keluar</span>
                                        <input type="time" name="out_time" x-model="outTimeKantor" class="w-full text-xs rounded-xl border-slate-300 bg-white font-mono font-bold text-blue-900" required>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-600 font-semibold block mb-0.5">Est. Selesai / Kembali</span>
                                        <input type="time" name="estimated_return_time" x-model="returnTimeKantor" class="w-full text-xs rounded-xl border-slate-300 bg-white font-mono font-bold text-emerald-900">
                                    </div>
                                </div>
                                <div class="text-[10px] text-blue-700 font-semibold mt-1.5 flex items-center gap-1">
                                    <span>⏱️ Rentang Waktu:</span>
                                    <span class="font-mono font-bold" x-text="outTimeKantor + ' s.d ' + (returnTimeKantor || 'selesai')"></span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tujuan Keluar Kantor</label>
                            <input type="text" name="destination" placeholder="Contoh: Bank BPD Kaltimtara / Kantor Dinas Pendidikan" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba]" required>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Keperluan / Alasan</label>
                            <textarea name="purpose" rows="2" placeholder="Tuliskan keperluan keluar kantor..." class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba]" required></textarea>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tujukan Persetujuan Ke (Pemberi Izin)</label>
                            <select name="responder_role" class="w-full text-xs rounded-xl border-slate-300 font-bold bg-slate-50">
                                <option value="Kasubbag. Umum" selected>Kasubbag. Umum (Soerjo Adi Poernomo,SS.M.Pd)</option>
                                <option value="Kepala">Kepala Balai (Dr. Jarwoko, M.Pd)</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showModalKantor = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold">Batal</button>
                            <button type="submit" class="px-5 py-2 text-white rounded-xl font-bold shadow-md transition" style="background-color: #0070ba;">Kirim Izin Kantor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ================= MODAL PENGAJUAN IZIN KELUAR RUANGAN ================= -->
        <div x-show="showModalRuangan" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModalRuangan" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModalRuangan = false"></div>
                <div x-show="showModalRuangan" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white shadow-sm" style="background-color: #2e7d32;">🚪</span>
                            <div>
                                <h3 class="text-sm font-black uppercase text-slate-900">Formulir Izin Keluar Ruangan</h3>
                                <div class="text-[10px] text-slate-500">Monitoring internal ketertiban pegawai BPMP Kalimantan Timur</div>
                            </div>
                        </div>
                        <button @click="showModalRuangan = false" class="text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>
                    </div>

                    <form method="POST" action="{{ route('mik.store') }}" class="space-y-4 text-xs">
                        @csrf
                        <input type="hidden" name="type" value="ruangan">

                        @if(in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator']))
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Pegawai</label>
                            <select name="user_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba] font-medium bg-slate-50">
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ auth()->id() === $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->gugus_mutu ?? 'Non-GM' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Nama Pemohon</label>
                                <input type="text" value="{{ auth()->user()->name }}" class="w-full text-xs rounded-xl border-slate-200 bg-slate-100 text-slate-700 font-bold" readonly>
                            </div>
                        @endif

                        <!-- RENTANG JAM & TANGGAL -->
                        <div class="bg-emerald-50/60 p-3.5 rounded-xl border border-emerald-200/60 space-y-3">
                            <div>
                                <label class="block font-bold text-emerald-950 mb-1">Tanggal Izin</label>
                                <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" class="w-full text-xs rounded-xl border-slate-300 bg-white font-medium" required>
                            </div>
                            
                            <div>
                                <label class="block font-bold text-emerald-950 mb-1">Rentang Jam Keluar & Estimasi Kembali</label>
                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <span class="text-[10px] text-slate-600 font-semibold block mb-0.5">Waktu Keluar</span>
                                        <input type="time" name="out_time" x-model="outTimeRuangan" class="w-full text-xs rounded-xl border-slate-300 bg-white font-mono font-bold text-emerald-900" required>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-600 font-semibold block mb-0.5">Est. Selesai / Kembali</span>
                                        <input type="time" name="estimated_return_time" x-model="returnTimeRuangan" class="w-full text-xs rounded-xl border-slate-300 bg-white font-mono font-bold text-emerald-900">
                                    </div>
                                </div>
                                <div class="text-[10px] text-emerald-800 font-semibold mt-1.5 flex items-center gap-1">
                                    <span>⏱️ Rentang Waktu:</span>
                                    <span class="font-mono font-bold" x-text="outTimeRuangan + ' s.d ' + (returnTimeRuangan || 'selesai')"></span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tujuan Ruangan / Area di Lingkungan Kantor</label>
                            <input type="text" name="destination" placeholder="Contoh: Musholla Al-Ikhlas / Poliklinik / Ruang Rapat / Arsip" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba]" required>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Keperluan / Alasan</label>
                            <textarea name="purpose" rows="2" placeholder="Tuliskan keperluan keluar ruangan..." class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba]" required></textarea>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tujukan Persetujuan Ke (Pemberi Izin)</label>
                            <select name="responder_role" class="w-full text-xs rounded-xl border-slate-300 font-bold bg-slate-50">
                                <option value="Ketua Gugus 1">Ketua Gugus 1 (PAUD & Kesetaraan)</option>
                                <option value="Ketua Gugus 2">Ketua Gugus 2 (Sekolah Dasar / SD)</option>
                                <option value="Ketua Gugus 3" selected>Ketua Gugus 3 (SMP)</option>
                                <option value="Ketua Gugus 4">Ketua Gugus 4 (SMA, SMK & SLB)</option>
                                <option value="Ketua Gugus 5">Ketua Gugus 5 (Tata Usaha & Kemitraan)</option>
                                <option value="Kasubbag. Umum">Kasubbag. Umum (Soerjo Adi Poernomo,SS.M.Pd)</option>
                                <option value="Koordinator Rumah Tangga">Koordinator Rumah Tangga</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showModalRuangan = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold">Batal</button>
                            <button type="submit" class="px-5 py-2 text-white rounded-xl font-bold shadow-md transition" style="background-color: #2e7d32;">Kirim Izin Ruangan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
