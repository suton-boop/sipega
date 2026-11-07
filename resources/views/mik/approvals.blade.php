<x-app-layout>
    <div class="bg-slate-100 min-h-screen py-6" x-data="{ modalAction: false, selectedPermit: null, targetStatus: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- FLASH MESSAGE -->
            @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-xl text-xs text-emerald-900 font-bold shadow-sm">
                ✓ {{ session('success') }}
            </div>
            @endif

            <!-- TOP BANNER (HIGH CONTRAST & ELEGANT) -->
            <div class="rounded-2xl shadow-lg overflow-hidden border border-blue-900/20" style="background: linear-gradient(135deg, #005293 0%, #0070ba 50%, #008be3 100%); color: #ffffff;">
                <div class="px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-full border-4 border-white/90 flex items-center justify-center font-black text-2xl text-white shadow-inner" style="background: rgba(255,255,255,0.15);">
                            <span class="w-4 h-4 rounded-full bg-white shadow"></span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-blue-100 tracking-wide uppercase">MiK • Monitoring Izin Karyawan</div>
                            <h1 class="text-2xl md:text-3xl font-black tracking-tight leading-none text-white uppercase drop-shadow-sm">Persetujuan Izin Karyawan</h1>
                            <div class="text-[11px] text-blue-100/90 mt-1 font-medium">
                                Otoritas: <strong>Ketua Gugus Mutu 1-5</strong>, <strong>Kasubbag. Umum</strong>, dan <strong>Kepala Balai</strong>
                            </div>
                        </div>
                    </div>

                    <!-- PENDING COUNTERS (HIGH VISIBILITY) -->
                    <div class="flex items-center gap-2.5">
                        <div class="rounded-xl px-4 py-2 text-center min-w-[95px] shadow-md border border-white/20" style="background-color: #d32f2f; color: #ffffff;">
                            <div class="text-[10px] font-black uppercase tracking-wider text-white/90">Menunggu</div>
                            <div class="text-2xl font-black leading-none mt-1 text-white">{{ $counts['pending_all'] }}</div>
                        </div>
                        <div class="rounded-xl px-4 py-2 text-center min-w-[95px] shadow-md border border-white/20" style="background-color: #1565c0; color: #ffffff;">
                            <div class="text-[10px] font-black uppercase tracking-wider text-white/90">Izin Kantor</div>
                            <div class="text-2xl font-black leading-none mt-1 text-white">{{ $counts['pending_kantor'] }}</div>
                        </div>
                        <div class="rounded-xl px-4 py-2 text-center min-w-[95px] shadow-md border border-white/20" style="background-color: #4527a0; color: #ffffff;">
                            <div class="text-[10px] font-black uppercase tracking-wider text-white/90">Izin Ruangan</div>
                            <div class="text-2xl font-black leading-none mt-1 text-white">{{ $counts['pending_ruangan'] }}</div>
                        </div>
                    </div>
                </div>

                <!-- SUB-INFO BAR -->
                <div class="px-6 py-2.5 border-t border-white/20 text-xs text-blue-50 flex flex-wrap items-center justify-between gap-2" style="background: rgba(0, 35, 75, 0.45);">
                    <span class="flex items-center gap-1.5 font-medium">
                        <span class="text-amber-300 text-sm">💡</span>
                        <span>Ketertiban Pegawai: Modul persetujuan ini tidak mempengaruhi kalkulasi Tunjangan Kinerja (Tukin) dan skor SKP.</span>
                    </span>
                    <a href="{{ route('mik.index') }}" class="text-white hover:text-amber-200 font-bold text-xs underline underline-offset-2">← Kembali ke Dashboard MiK</a>
                </div>
            </div>

            <!-- FILTER TABS & SEARCH (WITH RENTANG JAM) -->
            <div class="bg-white rounded-2xl p-4 shadow-md border border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('mik.approvals') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-sm {{ !request('type') ? 'text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}" style="{{ !request('type') ? 'background-color: #0070ba;' : '' }}">
                        Semua Izin ({{ $counts['pending_all'] }} antrian)
                    </a>
                    <a href="{{ route('mik.approvals', ['type' => 'kantor']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-sm {{ request('type') === 'kantor' ? 'text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}" style="{{ request('type') === 'kantor' ? 'background-color: #0070ba;' : '' }}">
                        🏢 Keluar Kantor ({{ $counts['pending_kantor'] }})
                    </a>
                    <a href="{{ route('mik.approvals', ['type' => 'ruangan']) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-sm {{ request('type') === 'ruangan' ? 'text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}" style="{{ request('type') === 'ruangan' ? 'background-color: #0070ba;' : '' }}">
                        🚪 Keluar Ruangan ({{ $counts['pending_ruangan'] }})
                    </a>
                </div>

                <form method="GET" action="{{ route('mik.approvals') }}" class="flex flex-wrap items-center gap-2 text-xs">
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif

                    <!-- Rentang Jam Filter -->
                    <div class="flex items-center gap-1 bg-slate-50 border border-slate-300 rounded-xl px-2 py-1">
                        <span class="text-[10px] text-slate-500 font-bold">Jam:</span>
                        <input type="time" name="start_time" value="{{ request('start_time') }}" class="text-xs border-0 p-0 bg-transparent font-mono focus:ring-0" placeholder="Mulai">
                        <span class="text-slate-400">-</span>
                        <input type="time" name="end_time" value="{{ request('end_time') }}" class="text-xs border-0 p-0 bg-transparent font-mono focus:ring-0" placeholder="Sampai">
                        <button type="submit" class="text-[10px] bg-slate-800 text-white px-2 py-0.5 rounded font-bold hover:bg-black">Cari</button>
                    </div>

                    <select name="status" class="text-xs rounded-xl border-slate-300 py-1.5 focus:border-[#0070ba] font-medium bg-slate-50" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="belum direspon" {{ request('status') === 'belum direspon' ? 'selected' : '' }}>Menunggu (belum direspon)</option>
                        <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="tidak disetujui" {{ request('status') === 'tidak disetujui' ? 'selected' : '' }}>Tidak disetujui</option>
                        <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>

                    @if(request()->anyFilled(['status', 'start_time', 'end_time']))
                        <a href="{{ route('mik.approvals', request('type') ? ['type' => request('type')] : []) }}" class="text-xs text-rose-600 hover:text-rose-800 font-bold">Reset</a>
                    @endif
                </form>
            </div>

            <!-- TABLE OF PERMITS -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="text-white text-xs font-bold" style="background-color: #0070ba;">
                            <tr>
                                <th class="px-3 py-3.5 text-center w-10 border-b border-blue-600">No.</th>
                                <th class="px-4 py-3.5 border-b border-blue-600">Tipe Izin</th>
                                <th class="px-4 py-3.5 border-b border-blue-600">Nama Pegawai</th>
                                <th class="px-4 py-3.5 border-b border-blue-600">Tanggal</th>
                                <th class="px-4 py-3.5 border-b border-blue-600">Rentang Jam & Durasi</th>
                                <th class="px-4 py-3.5 border-b border-blue-600">Tujuan & Alasan</th>
                                <th class="px-3 py-3.5 border-b border-blue-600">Target Responder</th>
                                <th class="px-3 py-3.5 border-b border-blue-600 text-center">Status</th>
                                <th class="px-4 py-3.5 border-b border-blue-600 text-center">Persetujuan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($permits as $index => $item)
                                <tr class="hover:bg-blue-50/60 transition {{ $index % 2 == 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                                    <td class="px-3 py-3.5 text-center font-bold text-slate-500">
                                        {{ $permits->firstItem() + $index }}.
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if($item->type === 'kantor')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-blue-100 text-blue-900 border border-blue-300">
                                                🏢 KELUAR KANTOR
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                                                🚪 KELUAR RUANGAN
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="font-extrabold text-slate-900 text-xs">{{ $item->employee_name }}</div>
                                        <div class="text-[10px] text-slate-500">
                                            {{ $item->user->gugus_mutu ?? $item->user->jabatan ?? '-' }}
                                        </div>
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
                                    <td class="px-4 py-3.5 max-w-xs">
                                        @if($item->destination)
                                            <div class="font-bold text-slate-800 text-xs">{{ $item->destination }}</div>
                                        @endif
                                        <div class="text-[11px] text-slate-600 italic">"{{ $item->purpose }}"</div>
                                        @if($item->responder_notes)
                                            <div class="mt-1 text-[10px] text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                                Catatan: {{ $item->responder_notes }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3.5 whitespace-nowrap">
                                        <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg text-[10px] border border-slate-200">
                                            {{ $item->responder_role ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold lowercase shadow-sm
                                            @if($item->status === 'disetujui') bg-emerald-100 text-emerald-800 border border-emerald-300
                                            @elseif($item->status === 'tidak disetujui') bg-amber-100 text-amber-800 border border-amber-300
                                            @elseif($item->status === 'dibatalkan') bg-slate-200 text-slate-700 border border-slate-300
                                            @else bg-rose-100 text-rose-800 border border-rose-300 @endif">
                                            {{ $item->status }}
                                        </span>
                                        @if($item->responder_name)
                                            <div class="text-[9px] text-slate-500 mt-0.5">Oleh: {{ $item->responder_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                        @if($item->status === 'belum direspon')
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button type="button" 
                                                    @click="modalAction = true; selectedPermit = {{ json_encode($item) }}; targetStatus = 'disetujui'"
                                                    class="px-3 py-1.5 rounded-lg text-[11px] font-bold text-white transition shadow-sm flex items-center gap-1"
                                                    style="background-color: #2e7d32;">
                                                    <span>✓</span> Setujui
                                                </button>
                                                <button type="button" 
                                                    @click="modalAction = true; selectedPermit = {{ json_encode($item) }}; targetStatus = 'tidak disetujui'"
                                                    class="px-3 py-1.5 rounded-lg text-[11px] font-bold text-white transition shadow-sm flex items-center gap-1"
                                                    style="background-color: #f57f17;">
                                                    <span>✕</span> Tolak
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-xs italic font-medium">Selesai direspon</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-12 text-center text-slate-400 italic">
                                        Tidak ada permohonan izin yang sesuai filter.
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

        <!-- ================= MODAL ACTION (SETUJUI / TOLAK) ================= -->
        <div x-show="modalAction" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalAction" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalAction = false"></div>
                <div x-show="modalAction" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-black uppercase" :class="targetStatus === 'disetujui' ? 'text-emerald-700' : 'text-amber-700'">
                            Konfirmasi <span x-text="targetStatus === 'disetujui' ? 'Persetujuan Izin' : 'Penolakan Izin'"></span>
                        </h3>
                        <button @click="modalAction = false" class="text-slate-400 hover:text-slate-700 font-bold text-lg">✕</button>
                    </div>

                    <template x-if="selectedPermit">
                        <form method="POST" :action="'/mik/' + selectedPermit.id + '/approve'" class="space-y-4 text-xs">
                            @csrf
                            <input type="hidden" name="status" :value="targetStatus">

                            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Nama Pegawai:</span>
                                    <strong class="text-slate-900" x-text="selectedPermit.employee_name"></strong>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Tipe Izin:</span>
                                    <strong class="uppercase text-blue-700" x-text="'Izin ' + selectedPermit.type"></strong>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Rentang Jam:</span>
                                    <span class="font-mono text-slate-800 font-bold" x-text="selectedPermit.out_time + ' s.d ' + (selectedPermit.estimated_return_time || 'selesai')"></span>
                                </div>
                                <div>
                                    <span class="text-slate-500">Keperluan:</span>
                                    <p class="font-medium text-slate-900 mt-0.5" x-text="selectedPermit.purpose"></p>
                                </div>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Catatan / Arahan Atasan (Opsional)</label>
                                <textarea name="note" rows="2" placeholder="Tuliskan catatan respon jika ada..." class="w-full text-xs rounded-xl border-slate-300 focus:border-[#0070ba]"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                <button type="button" @click="modalAction = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold">Batal</button>
                                <button type="submit" 
                                    class="px-5 py-2 text-white rounded-xl font-bold shadow-md transition"
                                    :style="targetStatus === 'disetujui' ? 'background-color: #2e7d32;' : 'background-color: #f57f17;'">
                                    Konfirmasi <span x-text="targetStatus === 'disetujui' ? 'Setujui' : 'Tolak'"></span>
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
