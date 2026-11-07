<x-app-layout>
    <div class="bg-slate-100 min-h-screen py-6" x-data="{ showModal: false, outTime: '{{ now()->format('H:i') }}', returnTime: '{{ now()->addHours(2)->format('H:i') }}' }">
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
                            <div class="text-xs font-bold text-blue-100 tracking-wide uppercase">MiK • Monitoring Izin Karyawan</div>
                            <h1 class="text-2xl md:text-3xl font-black tracking-tight leading-none text-white uppercase drop-shadow-sm">KELUAR KANTOR</h1>
                            <div class="text-[11px] text-blue-100/90 mt-1 font-medium">Balai Penjaminan Mutu Pendidikan (BPMP) Provinsi Kalimantan Timur</div>
                        </div>
                    </div>

                    <!-- 4 Counter Badges di Header (High Contrast, Explicit Styling) -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <div class="rounded-xl px-4 py-2.5 text-center min-w-[95px] shadow-md border border-white/20" style="background-color: #d32f2f; color: #ffffff;">
                            <div class="text-[10px] font-black uppercase tracking-wider text-white/90">Belum direspon</div>
                            <div class="text-2xl font-black leading-none mt-1 text-white">{{ $stats['belum_direspon'] }}</div>
                        </div>
                        <div class="rounded-xl px-4 py-2.5 text-center min-w-[95px] shadow-md border border-white/20" style="background-color: #2e7d32; color: #ffffff;">
                            <div class="text-[10px] font-black uppercase tracking-wider text-white/90">Disetujui</div>
                            <div class="text-2xl font-black leading-none mt-1 text-white">{{ $stats['disetujui'] }}</div>
                        </div>
                        <div class="rounded-xl px-4 py-2.5 text-center min-w-[95px] shadow-md border border-white/20" style="background-color: #f57f17; color: #ffffff;">
                            <div class="text-[10px] font-black uppercase tracking-wider text-white/90">Tidak disetujui</div>
                            <div class="text-2xl font-black leading-none mt-1 text-white">{{ $stats['tidak_disetujui'] }}</div>
                        </div>
                        <div class="rounded-xl px-4 py-2.5 text-center min-w-[95px] shadow-md border border-white/20" style="background-color: #5c6bc0; color: #ffffff;">
                            <div class="text-[10px] font-black uppercase tracking-wider text-white/90">Dibatalkan</div>
                            <div class="text-2xl font-black leading-none mt-1 text-white">{{ $stats['dibatalkan'] }}</div>
                        </div>
                    </div>
                </div>

                <!-- SUB-INFO: TIDAK MEMPENGARUHI TUKIN & PERFORMA -->
                <div class="px-6 py-2.5 border-t border-white/20 text-xs text-blue-50 flex flex-wrap items-center justify-between gap-2" style="background: rgba(0, 35, 75, 0.45);">
                    <span class="flex items-center gap-1.5 font-medium">
                        <span class="text-amber-300 text-sm">💡</span>
                        <span><strong>Ketertiban Izin Keluar Kantor:</strong> Digunakan untuk penugasan/keperluan di luar area kantor BPMP. Tidak mempengaruhi perhitungan Tukin dan nilai performa SKP.</span>
                    </span>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('mik.index') }}" class="text-white hover:text-amber-200 font-bold text-xs underline underline-offset-2">← Dashboard MiK</a>
                        <span>•</span>
                        <a href="{{ route('mik.form_ruangan') }}" class="text-blue-100 hover:text-white font-bold text-xs bg-white/20 px-3 py-1 rounded-lg transition">Buka Form_Ruangan →</a>
                    </div>
                </div>
            </div>

            <!-- MAIN CONTENT: 2 COLUMNS (LEFT SIDEBAR FILTER + RIGHT TABLE) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- LEFT COLUMN: PENCARIAN DENGAN RENTANG TANGGAL & RENTANG JAM (3 cols) -->
                <div class="lg:col-span-3 space-y-4">
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-md space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <h3 class="text-sm font-black text-[#0070ba] flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#0070ba]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span>Pencarian Data :</span>
                            </h3>
                            @if(request()->anyFilled(['name', 'start_date', 'end_date', 'start_time', 'end_time', 'status']))
                                <span class="text-[9px] bg-amber-100 text-amber-800 font-black px-2 py-0.5 rounded-full">Filter Aktif</span>
                            @endif
                        </div>

                        <form method="GET" action="{{ route('mik.form_kantor') }}" class="space-y-3.5 text-xs">
                            <!-- NAMA PEGAWAI -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Nama Pegawai</label>
                                <select name="name" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba] bg-slate-50 font-medium">
                                    <option value="">-- Semua Nama Pegawai --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->name }}" {{ request('name') === $emp->name ? 'selected' : '' }}>
                                            {{ $emp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- RENTANG TANGGAL -->
                            <div class="space-y-1.5 pt-1">
                                <label class="block font-bold text-slate-700">Rentang Tanggal</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-[10px] text-slate-500 font-semibold block mb-0.5">Dari Tanggal</span>
                                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba] bg-slate-50">
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-500 font-semibold block mb-0.5">Sampai Tanggal</span>
                                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba] bg-slate-50">
                                    </div>
                                </div>
                            </div>

                            <!-- RENTANG JAM (BARU DITAMBAHKAN) -->
                            <div class="space-y-1.5 pt-1 border-t border-slate-100">
                                <label class="block font-bold text-slate-700 flex items-center justify-between">
                                    <span>Rentang Jam (Waktu)</span>
                                    <span class="text-[9px] text-blue-600 font-black bg-blue-50 px-1.5 py-0.5 rounded">Waktu Keluar / Kembali</span>
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="text-[10px] text-slate-500 font-semibold block mb-0.5">Dari Jam</span>
                                        <input type="time" name="start_time" value="{{ request('start_time') }}" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba] bg-slate-50 font-mono">
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-500 font-semibold block mb-0.5">Sampai Jam</span>
                                        <input type="time" name="end_time" value="{{ request('end_time') }}" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba] bg-slate-50 font-mono">
                                    </div>
                                </div>
                            </div>

                            <!-- STATUS -->
                            <div class="pt-1">
                                <label class="block font-bold text-slate-700 mb-1">Status Izin</label>
                                <select name="status" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba] bg-slate-50 font-medium">
                                    <option value="">-- Semua Status --</option>
                                    <option value="belum direspon" {{ request('status') === 'belum direspon' ? 'selected' : '' }}>belum direspon</option>
                                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>disetujui</option>
                                    <option value="tidak disetujui" {{ request('status') === 'tidak disetujui' ? 'selected' : '' }}>tidak disetujui</option>
                                    <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>dibatalkan</option>
                                </select>
                            </div>

                            <!-- BUTTON SUBMIT FILTER -->
                            <button type="submit" class="w-full py-2.5 rounded-xl font-bold shadow-md transition text-xs flex items-center justify-center gap-1.5 text-white" style="background-color: #1e293b;">
                                <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                <span>Terapkan Filter Rentang</span>
                            </button>

                            @if(request()->anyFilled(['name', 'start_date', 'end_date', 'start_time', 'end_time', 'status']))
                            <a href="{{ route('mik.form_kantor') }}" class="block text-center text-xs text-rose-600 hover:text-rose-800 font-bold pt-1">
                                ✕ Reset Semua Filter
                            </a>
                            @endif
                        </form>
                    </div>

                    <!-- Tombol Ajukan Izin Baru (High Visibility & Contrast) -->
                    <button type="button" @click="showModal = true" class="w-full py-3.5 rounded-2xl shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2 font-black text-xs uppercase tracking-wider text-white" style="background: linear-gradient(135deg, #0070ba 0%, #005a9c 100%);">
                        <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Buat Izin Keluar Kantor</span>
                    </button>
                </div>

                <!-- RIGHT COLUMN: TABLE (9 cols) -->
                <div class="lg:col-span-9 bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="text-white text-xs font-bold" style="background-color: #0070ba;">
                                <tr>
                                    <th class="px-3 py-3.5 text-center w-10 border-b border-blue-600">No.</th>
                                    <th class="px-4 py-3.5 border-b border-blue-600">Nama Pegawai ▾</th>
                                    <th class="px-4 py-3.5 border-b border-blue-600">Tanggal</th>
                                    <th class="px-4 py-3.5 border-b border-blue-600">Rentang Jam & Durasi</th>
                                    <th class="px-3 py-3.5 border-b border-blue-600 text-center">Status</th>
                                    <th class="px-4 py-3.5 border-b border-blue-600">Pemberi Izin (Responder)</th>
                                    <th class="px-3 py-3.5 border-b border-blue-600 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($permits as $index => $item)
                                    <tr class="hover:bg-blue-50/60 transition {{ $index % 2 == 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                                        <td class="px-3 py-3.5 text-center font-bold text-slate-500">
                                            {{ $permits->firstItem() + $index }}.
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="font-extrabold text-slate-900 text-xs">{{ $item->employee_name }}</div>
                                            <div class="text-[10px] text-blue-700 font-semibold mt-0.5">🏢 Tujuan: {{ $item->destination ?? '-' }}</div>
                                            <div class="text-[11px] text-slate-600 italic mt-0.5">"{{ $item->purpose }}"</div>
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap text-slate-800 font-medium">
                                            {{ $item->date->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            <div class="font-mono font-bold text-blue-900 text-xs flex items-center gap-1.5">
                                                <span class="inline-block w-2 h-2 rounded-full {{ $item->return_time ? 'bg-emerald-500' : 'bg-blue-500 animate-pulse' }}"></span>
                                                <span>{{ $item->time_range_text }}</span>
                                            </div>
                                            <div class="text-[10px] font-semibold text-emerald-700 mt-0.5 flex items-center gap-1">
                                                <span>⏱️</span>
                                                <span>{{ $item->duration_text }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold lowercase shadow-sm
                                                @if($item->status === 'disetujui') bg-emerald-100 text-emerald-800 border border-emerald-300
                                                @elseif($item->status === 'tidak disetujui') bg-amber-100 text-amber-800 border border-amber-300
                                                @elseif($item->status === 'dibatalkan') bg-slate-200 text-slate-700 border border-slate-300
                                                @else bg-rose-100 text-rose-800 border border-rose-300 @endif">
                                                {{ $item->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap font-semibold text-slate-800">
                                            <div class="text-xs">{{ $item->responder_role ?? 'Kasubbag. Umum' }}</div>
                                            @if($item->responder_name)
                                                <div class="text-[10px] text-slate-500 font-normal">({{ $item->responder_name }})</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                @if(!$item->return_time && $item->status === 'disetujui')
                                                    <form method="POST" action="{{ route('mik.return', $item->id) }}">
                                                        @csrf
                                                        <button type="submit" class="px-2.5 py-1 rounded text-[10px] font-bold text-white shadow transition" style="background-color: #2e7d32;" title="Klik saat pegawai sudah tiba kembali di kantor">
                                                            ✓ Catat Kembali
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($item->status === 'belum direspon' && ($item->user_id === auth()->id() || in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan'])))
                                                    <form method="POST" action="{{ route('mik.cancel', $item->id) }}" onsubmit="return confirm('Batalkan izin keluar kantor ini?')">
                                                        @csrf
                                                        <button type="submit" class="px-2 py-1 bg-slate-100 hover:bg-rose-100 text-slate-700 hover:text-rose-700 rounded text-[10px] font-bold border border-slate-300 transition">
                                                            Batalkan
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center text-slate-400 italic">
                                            Tidak ada data izin keluar kantor yang sesuai filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($permits->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50">
                        {{ $permits->links() }}
                    </div>
                    @endif
                </div>

            </div>

        </div>

        <!-- ================= MODAL PENGAJUAN IZIN KELUAR KANTOR ================= -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModal = false"></div>
                <div x-show="showModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white shadow-sm" style="background-color: #0070ba;">🏢</span>
                            <div>
                                <h3 class="text-sm font-black uppercase text-slate-900">Formulir Izin Keluar Kantor</h3>
                                <div class="text-[10px] text-slate-500">Penugasan & Keperluan Luar Kantor BPMP Kalimantan Timur</div>
                            </div>
                        </div>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>
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
                                        <input type="time" name="out_time" x-model="outTime" class="w-full text-xs rounded-xl border-slate-300 bg-white font-mono font-bold text-blue-900" required>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-600 font-semibold block mb-0.5">Est. Selesai / Kembali</span>
                                        <input type="time" name="estimated_return_time" x-model="returnTime" class="w-full text-xs rounded-xl border-slate-300 bg-white font-mono font-bold text-emerald-900">
                                    </div>
                                </div>
                                <div class="text-[10px] text-blue-700 font-semibold mt-1.5 flex items-center gap-1">
                                    <span>⏱️ Rentang Waktu:</span>
                                    <span class="font-mono font-bold" x-text="outTime + ' s.d ' + (returnTime || 'selesai')"></span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tujuan Keluar Kantor</label>
                            <input type="text" name="destination" placeholder="Contoh: Bank BPD Kaltimtara / Kantor Dinas Pendidikan / BPJS" class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba]" required>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Keperluan / Alasan</label>
                            <textarea name="purpose" rows="2" placeholder="Tuliskan keperluan keluar kantor secara jelas..." class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba]" required></textarea>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tujukan Persetujuan Ke (Pemberi Izin)</label>
                            <select name="responder_role" class="w-full text-xs rounded-xl border-slate-300 font-bold bg-slate-50">
                                <option value="Kasubbag. Umum" selected>Kasubbag. Umum (Soerjo Adi Poernomo,SS.M.Pd)</option>
                                <option value="Kepala">Kepala Balai (Dr. Jarwoko, M.Pd)</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold">Batal</button>
                            <button type="submit" class="px-5 py-2 text-white rounded-xl font-bold shadow-md transition" style="background-color: #0070ba;">Kirim Izin Kantor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
