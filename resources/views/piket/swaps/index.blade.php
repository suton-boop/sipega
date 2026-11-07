<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight tracking-tight flex items-center gap-2">
                    <span>🔀</span> Pengajuan & Persetujuan Tukar Jadwal Piket
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Fasilitas pertukaran jadwal antarpetugas dengan validasi otomatis benturan agenda kedua belah pihak
                </p>
            </div>
            <div>
                <button type="button" onclick="document.getElementById('modalTukar').classList.remove('hidden')" class="px-4 py-2 bg-sipega-navy hover:bg-slate-800 text-white rounded-lg text-xs font-black shadow-sm transition flex items-center gap-2">
                    <span>+</span> Ajukan Tukar Jadwal
                </button>
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

            @if(session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg shadow-sm text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- JADWAL PIKET PRIBADI MENDATANG (RINGKASAN) -->
            <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white p-5 rounded-2xl shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-xs font-black uppercase tracking-wider text-sky-300">Jadwal Piket Anda Mendatang</div>
                    <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded font-mono">{{ $myUpcomingAssignments->count() }} Tugas</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    @forelse($myUpcomingAssignments->take(4) as $assign)
                        <div class="p-3 bg-white/10 rounded-xl border border-white/10 text-xs">
                            <div class="font-black text-sky-200">{{ $assign->unit->code }} - {{ $assign->date->translatedFormat('d M Y') }}</div>
                            <div class="text-[10px] text-slate-300 mt-0.5">{{ $assign->role_label }}</div>
                        </div>
                    @empty
                        <div class="col-span-4 text-xs text-slate-300 italic">
                            Anda belum memiliki jadwal piket mendatang dalam waktu dekat.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TABEL PENGAJUAN TUKAR JADWAL -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-800">Daftar Pengajuan Pertukaran Jadwal</h3>
                        <p class="text-xs text-slate-500">Pertukaran jadwal membutuhkan persetujuan Admin/Pimpinan sebelum aktif berlaku.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-black tracking-wider text-[10px]">
                            <tr>
                                <th class="py-3 px-4">Tanggal Pengajuan</th>
                                <th class="py-3 px-4">Pegawai Pemohon</th>
                                <th class="py-3 px-4">Jadwal Ditukar</th>
                                <th class="py-3 px-4">Pegawai Rekan & Jadwal Rekan</th>
                                <th class="py-3 px-4">Alasan</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                @if($isAdmin)
                                    <th class="py-3 px-4 text-center">Tindakan Admin</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($swaps as $s)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                                        {{ $s->created_at ? $s->created_at->translatedFormat('d M Y H:i') : '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800">{{ $s->requester->name ?? '-' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $s->requester->nip ?? '-' }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-indigo-700">
                                            📅 {{ $s->requester_date ? $s->requester_date->translatedFormat('d M Y') : '-' }}
                                        </div>
                                        <div class="text-[10px] text-slate-500">
                                            {{ $s->requesterAssignment->unit->code ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800">{{ $s->targetUser->name ?? '-' }}</div>
                                        <div class="font-bold text-emerald-700 mt-0.5">
                                            📅 {{ $s->target_date ? $s->target_date->translatedFormat('d M Y') : '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <div class="text-slate-700 italic">"{{ $s->reason }}"</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        @if($s->admin_approval_status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ Disetujui
                                            </span>
                                        @elseif($s->admin_approval_status === 'rejected')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                ✕ Ditolak
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                ⏳ Menunggu
                                            </span>
                                        @endif
                                    </td>
                                    @if($isAdmin)
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            @if($s->admin_approval_status === 'pending')
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <form method="POST" action="{{ route('piket.swaps.approval', $s->id) }}" onsubmit="return confirm('Setujui pertukaran jadwal ini? Petugas pada kedua tanggal jadwal akan otomatis ditukar.')">
                                                        @csrf
                                                        <input type="hidden" name="decision" value="approved">
                                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold transition">
                                                            Setujui
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="{{ route('piket.swaps.approval', $s->id) }}" onsubmit="return confirm('Tolak permohonan tukar jadwal ini?')">
                                                        @csrf
                                                        <input type="hidden" name="decision" value="rejected">
                                                        <button type="submit" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-[11px] font-bold transition">
                                                            Tolak
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-[10px] text-slate-400 italic">Telah Diproses</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isAdmin ? 7 : 6 }}" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-1">🔀</div>
                                        <div class="font-bold text-slate-600 text-sm">Belum ada pengajuan tukar jadwal</div>
                                        <p class="text-xs text-slate-400 mt-1">Gunakan tombol "Ajukan Tukar Jadwal" di atas bila Anda berhalangan hadir dan ingin bertukar hari dengan rekan kerja.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($swaps->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $swaps->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- MODAL PENGAJUAN TUKAR JADWAL -->
    <div id="modalTukar" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100">
            <div class="p-5 bg-sipega-navy text-white flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black">Formulir Pengajuan Tukar Jadwal</h3>
                    <p class="text-[11px] text-slate-300">Pilih jadwal Anda dan pilih jadwal rekan yang akan diajak bertukar</p>
                </div>
                <button type="button" onclick="document.getElementById('modalTukar').classList.add('hidden')" class="text-white/70 hover:text-white text-xl font-bold">
                    ✕
                </button>
            </div>

            <form method="POST" action="{{ route('piket.swaps.store') }}" class="p-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">1. Pilih Jadwal Anda yang Akan Ditukar:</label>
                    <select name="requester_assignment_id" required class="w-full text-xs rounded-xl border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium p-2.5">
                        <option value="">-- Pilih Jadwal Anda --</option>
                        @foreach($myUpcomingAssignments as $myA)
                            <option value="{{ $myA->id }}">
                                {{ $myA->unit->code }} - {{ $myA->date->translatedFormat('l, d M Y') }} ({{ $myA->role_label }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">2. Pilih Jadwal Rekan Tujuan (Pengganti):</label>
                    <select name="target_assignment_id" required class="w-full text-xs rounded-xl border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium p-2.5">
                        <option value="">-- Pilih Jadwal Rekan Kerja --</option>
                        @foreach($otherAssignments as $otherA)
                            <option value="{{ $otherA->id }}">
                                {{ $otherA->activeOfficer->name ?? 'Pegawai' }} | {{ $otherA->unit->code }} - {{ $otherA->date->translatedFormat('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">3. Alasan Pertukaran Jadwal:</label>
                    <textarea name="reason" rows="3" required placeholder="Tuliskan alasan mengapa mengajukan tukar jadwal..."
                              class="w-full text-xs rounded-xl border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium p-3"></textarea>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-800">
                    💡 <strong>Pemeriksaan Otomatis:</strong> Sistem akan memeriksa apakah Anda maupun rekan target memiliki benturan kegiatan kedinasan pada tanggal masing-masing sebelum pengajuan disetujui.
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalTukar').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-sipega-navy hover:bg-slate-800 text-white rounded-lg text-xs font-black shadow transition">
                        Kirim Pengajuan Tukar
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
