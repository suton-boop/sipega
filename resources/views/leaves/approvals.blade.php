<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">Panel Approval Berjenjang</span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-500 font-medium">Atasan Langsung & Pejabat Pembina Kepegawaian (PPK)</span>
                </div>
                <h2 class="text-2xl font-black text-sipega-navy tracking-tight">Persetujuan Cuti Pegawai</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('leaves.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl shadow-sm hover:bg-gray-50 transition">
                    ← Kembali ke Data Cuti
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl text-xs text-emerald-800 font-bold">
                ✓ {{ session('success') }}
            </div>
            @endif

            <!-- Tab Switcher -->
            <div class="flex items-center gap-3 border-b border-gray-200 pb-2">
                <a href="{{ route('leaves.approvals', ['tab' => 'pending']) }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl transition {{ $tab === 'pending' ? 'bg-sipega-navy text-white shadow' : 'text-gray-600 hover:bg-gray-100' }}">
                    <span>Perlu Tindakan / Menunggu</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'pending' ? 'bg-amber-400 text-sipega-navy font-black' : 'bg-gray-200 text-gray-700' }}">
                        {{ $counts['pending'] }}
                    </span>
                </a>
                <a href="{{ route('leaves.approvals', ['tab' => 'done']) }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl transition {{ $tab === 'done' ? 'bg-sipega-navy text-white shadow' : 'text-gray-600 hover:bg-gray-100' }}">
                    <span>Riwayat Keputusan</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'done' ? 'bg-white/20 text-white font-black' : 'bg-gray-200 text-gray-700' }}">
                        {{ $counts['done'] }}
                    </span>
                </a>
            </div>

            <!-- List of Leave Approvals -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-slate-50 border-b border-gray-100 text-[10px] font-black uppercase tracking-wider text-sipega-navy">
                            <tr>
                                <th class="px-5 py-4">Tgl & No. Pengajuan</th>
                                <th class="px-5 py-4">Pegawai Pemohon</th>
                                <th class="px-5 py-4">Jenis & Durasi Cuti</th>
                                <th class="px-5 py-4">Pertimbangan Atasan</th>
                                <th class="px-5 py-4">Keputusan PPK</th>
                                <th class="px-5 py-4 text-center">Tindakan Cepat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pendingApprovals as $leave)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="font-bold text-sipega-navy">{{ $leave->request_date->translatedFormat('d M Y') }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $leave->request_number ?? 'REG-'.$leave->id }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-black text-sipega-navy">{{ $leave->employee_name }}</div>
                                        <div class="text-[11px] text-gray-500">NIP. {{ $leave->employee_nip }} ({{ $leave->employee_status }})</div>
                                        <div class="text-[10px] text-gray-400 italic">{{ $leave->employee_position }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-gray-800">{{ $leave->leave_type_label }}</div>
                                        <div class="text-sipega-orange font-black">{{ $leave->duration_amount }} {{ $leave->duration_unit }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $leave->start_date->translatedFormat('d M') }} s.d {{ $leave->end_date->translatedFormat('d M Y') }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($leave->supervisor_decision === 'DISETUJUI')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ Disetujui
                                            </span>
                                            <div class="text-[9px] text-gray-400 mt-0.5">{{ $leave->supervisor_name }}</div>
                                        @elseif($leave->supervisor_decision === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                ⏳ Menunggu Atasan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                {{ $leave->supervisor_decision }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($leave->approver_decision === 'DISETUJUI')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ Disetujui PPK
                                            </span>
                                        @elseif($leave->approver_decision === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                ⏳ Menunggu PPK
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                {{ $leave->approver_decision }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('leaves.show', $leave->id) }}" class="px-3 py-1.5 bg-sipega-navy hover:bg-slate-800 text-white font-bold rounded-lg transition text-[11px]">
                                                Proses / Review →
                                            </a>
                                            <a href="{{ route('leaves.print', $leave->id) }}" target="_blank" class="p-1.5 text-gray-500 hover:text-emerald-600 rounded-lg" title="Cetak Formulir BKN">
                                                🖨️
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                        <div class="text-3xl mb-2">🎉</div>
                                        <div class="font-bold text-gray-600">Tidak Ada Permohonan Cuti Menunggu Persetujuan</div>
                                        <div class="text-xs text-gray-400 mt-1">Semua permohonan cuti telah selesai diproses.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pendingApprovals->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $pendingApprovals->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
