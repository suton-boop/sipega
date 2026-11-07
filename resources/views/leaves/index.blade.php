<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-sipega-orange/10 text-sipega-orange border border-sipega-orange/20">Modul Layanan Cuti ASN</span>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-gray-500 font-medium">Berdasarkan UU No. 20/2023 & PerBKN No. 24/2017 jo. No. 7/2021 & 7/2022</span>
                </div>
                <h2 class="text-2xl font-black text-sipega-navy tracking-tight">Kelola Data Cuti Pegawai</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('leaves.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sipega-navy hover:bg-slate-800 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-lg shadow-sipega-navy/20 hover:shadow-xl transition-all duration-200">
                    <svg class="w-4 h-4 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Ajukan Cuti Baru</span>
                </a>
                @if(in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator']))
                <a href="{{ route('leaves.balances') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-sipega-navy text-xs font-bold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Saldo Cuti Satker</span>
                </a>
                <button type="button" @click="showConcurrentModal = true" class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 text-xs font-bold rounded-xl shadow-sm transition">
                    <span>⚙️ Kuota Bersamaan ({{ \App\Models\Setting::get('leave_concurrent_percentage', 10) }}%)</span>
                </button>
                @endif
                <a href="{{ route('leaves.approvals') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Persetujuan Cuti</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showConcurrentModal: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Notifikasi Skenario Kuota Bersamaan Aktif -->
            @php
                $activeScenario = \App\Models\Setting::get('leave_concurrent_scenario', 'lembaga');
                $activePct = \App\Models\Setting::get('leave_concurrent_percentage', 10);
                $isStrict = (bool) \App\Models\Setting::get('leave_concurrent_strict', 0);
            @endphp
            <div class="bg-gradient-to-r from-purple-50 via-slate-50 to-amber-50 rounded-2xl p-4 border border-purple-200/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold text-sm shadow">
                        📊
                    </div>
                    <div>
                        <div class="font-bold text-gray-800 flex items-center gap-2">
                            <span>Aturan Kuota Cuti Bersamaan:</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $activeScenario === 'gugus_mutu' ? 'bg-purple-100 text-purple-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $activeScenario === 'gugus_mutu' ? 'Skenario 2: Kuota Gugus Mutu' : 'Skenario 1: Kuota Lembaga (Satker)' }}
                            </span>
                            <span class="text-[10px] font-black bg-slate-200 text-slate-800 px-2 py-0.5 rounded-full">
                                Toleransi: {{ $activePct }}% Pegawai
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">
                            {{ $activeScenario === 'gugus_mutu' 
                                ? 'Kapasitas cuti bersamaan dihitung proporsional per unit Gugus Mutu agar kekuatan tiap divisi minimal 90% tetap aktif.' 
                                : 'Kapasitas cuti bersamaan dihitung dari total seluruh pegawai BPMP Kalimantan Timur (maksimal ' . $activePct . '% cuti di hari yang sama).' }}
                        </p>
                    </div>
                </div>
                @if(in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan']))
                <button type="button" @click="showConcurrentModal = true" class="px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-sipega-navy font-bold rounded-xl text-[11px] shadow-sm transition whitespace-nowrap">
                    Ubah Skenario / % ⚙️
                </button>
                @endif
            </div>

            <!-- Banner Saldo Kuota Pegawai -->
            <div class="bg-gradient-to-r from-sipega-navy via-slate-900 to-sipega-navy rounded-2xl p-6 text-white shadow-xl relative overflow-hidden border border-white/10">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-sipega-orange/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-sipega-orange uppercase tracking-wider mb-1">
                            <span>Informasi Kuota Cuti Anda</span>
                            <span>•</span>
                            <span>Tahun {{ $currentYear }}</span>
                        </div>
                        <h3 class="text-xl font-black text-white">{{ auth()->user()->name }}</h3>
                        <p class="text-xs text-slate-300 mt-1">
                            NIP. {{ auth()->user()->nip ?? '-' }} | Jabatan: {{ auth()->user()->position ?? '-' }} | Masa Kerja: <strong class="text-amber-300">{{ auth()->user()->getMasaKerjaText() }}</strong> | Status: <strong class="text-emerald-400">{{ auth()->user()->effective_status_kepegawaian }}</strong>
                        </p>
                    </div>

                    <!-- Kuota Pills N, N-1, N-2 -->
                    <div class="flex items-center gap-3">
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 text-center min-w-[90px]">
                            <div class="text-[9px] uppercase tracking-wider text-slate-300 font-bold">N-2 ({{ $currentYear - 2 }})</div>
                            <div class="text-xl font-black text-amber-400 mt-0.5">{{ $myBalance->remaining_n2 }} <span class="text-[10px] font-normal text-slate-300">hari</span></div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-xl p-3 border border-white/10 text-center min-w-[90px]">
                            <div class="text-[9px] uppercase tracking-wider text-slate-300 font-bold">N-1 ({{ $currentYear - 1 }})</div>
                            <div class="text-xl font-black text-emerald-400 mt-0.5">{{ $myBalance->remaining_n1 }} <span class="text-[10px] font-normal text-slate-300">hari</span></div>
                        </div>
                        <div class="bg-sipega-orange/20 backdrop-blur-md rounded-xl p-3 border border-sipega-orange/40 text-center min-w-[100px]">
                            <div class="text-[9px] uppercase tracking-wider text-sipega-orange font-black">N ({{ $currentYear }})</div>
                            <div class="text-2xl font-black text-white mt-0.5">{{ $myBalance->remaining_quota }} <span class="text-[10px] font-normal text-slate-200">/ {{ $myBalance->initial_quota }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-gray-400">Total Pengajuan</div>
                        <div class="text-2xl font-black text-sipega-navy mt-1">{{ $stats['total'] }}</div>
                        <div class="text-[11px] text-gray-500 mt-0.5">Semua permohonan</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black">
                        📄
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-amber-500">Menunggu Proses</div>
                        <div class="text-2xl font-black text-amber-600 mt-1">{{ $stats['pending'] }}</div>
                        <div class="text-[11px] text-gray-500 mt-0.5">Atasan / Kepegawaian</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black">
                        ⏳
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-emerald-600">Disetujui PPK</div>
                        <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['approved'] }}</div>
                        <div class="text-[11px] text-gray-500 mt-0.5">Izin cuti terbit</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black">
                        ✅
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-rose-500">Ditolak / Gugur</div>
                        <div class="text-2xl font-black text-rose-600 mt-1">{{ $stats['rejected'] }}</div>
                        <div class="text-[11px] text-gray-500 mt-0.5">Melanggar aturan/syarat</div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-black">
                        ❌
                    </div>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                <form method="GET" action="{{ route('leaves.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Cari Nama / NIP / Alasan</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kata kunci..." class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Status Alur</label>
                        <select name="status" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy">
                            <option value="">Semua Status</option>
                            <option value="Diajukan" {{ request('status') === 'Diajukan' ? 'selected' : '' }}>Diajukan</option>
                            <option value="Disetujui Atasan" {{ request('status') === 'Disetujui Atasan' ? 'selected' : '' }}>Disetujui Atasan</option>
                            <option value="Disetujui" {{ request('status') === 'Disetujui' ? 'selected' : '' }}>Disetujui PPK</option>
                            <option value="Perubahan" {{ request('status') === 'Perubahan' ? 'selected' : '' }}>Perubahan</option>
                            <option value="Ditangguhkan" {{ request('status') === 'Ditangguhkan' ? 'selected' : '' }}>Ditangguhkan</option>
                            <option value="Ditolak" {{ request('status') === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Jenis Cuti</label>
                        <select name="leave_type" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy">
                            <option value="">Semua Jenis Cuti</option>
                            @foreach(\App\Models\LeaveRequest::LEAVE_TYPES as $key => $lbl)
                                <option value="{{ $key }}" {{ request('leave_type') === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-sipega-navy text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition">
                            Terapkan Filter
                        </button>
                        <a href="{{ route('leaves.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-xl transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table Data Cuti -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-slate-50 border-b border-gray-100 text-[10px] font-black uppercase tracking-wider text-sipega-navy">
                            <tr>
                                <th class="px-5 py-4">Tgl & No. Pengajuan</th>
                                <th class="px-5 py-4">Pegawai Pemohon</th>
                                <th class="px-5 py-4">Jenis Cuti & Durasi</th>
                                <th class="px-5 py-4">Pelaksanaan</th>
                                <th class="px-5 py-4">Status & AI Verifikasi</th>
                                <th class="px-5 py-4 text-center">Aksi & Dokumen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($leaves as $leave)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="font-bold text-sipega-navy">{{ $leave->request_date->translatedFormat('d M Y') }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $leave->request_number ?? 'REG-'.$leave->id }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-black text-sipega-navy flex items-center gap-1.5">
                                            <span>{{ $leave->employee_name }}</span>
                                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase {{ $leave->employee_status === 'PPPK' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                                {{ $leave->employee_status }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-gray-500">NIP. {{ $leave->employee_nip }}</div>
                                        <div class="text-[10px] text-gray-400 italic">{{ $leave->employee_position }} ({{ $leave->work_duration }})</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-slate-800">{{ $leave->leave_type_label }}</div>
                                        <div class="text-sipega-orange font-black text-xs">{{ $leave->duration_amount }} {{ $leave->duration_unit }}</div>
                                        <div class="text-[10px] text-gray-400 truncate max-w-xs" title="{{ $leave->reason }}">{{ $leave->reason }}</div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="text-xs font-semibold text-gray-700">{{ $leave->start_date->translatedFormat('d M Y') }}</div>
                                        <div class="text-[10px] text-gray-400">s.d {{ $leave->end_date->translatedFormat('d M Y') }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <!-- Badge Status Alur -->
                                        @if($leave->status === 'Disetujui')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <span>✓ Disetujui PPK</span>
                                            </span>
                                        @elseif($leave->status === 'Disetujui Atasan')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-100 text-blue-800 border border-blue-200">
                                                <span>⏳ Disetujui Atasan</span>
                                            </span>
                                        @elseif(in_array($leave->status, ['Ditolak', 'Tidak Disetujui']))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-200">
                                                <span>✕ Ditolak</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                                <span>● {{ $leave->status }}</span>
                                            </span>
                                        @endif

                                        <!-- Badge AI SIPEGA -->
                                        <div class="mt-1">
                                            @if($leave->ai_validation_status === 'Memenuhi Syarat')
                                                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                                                    <span>🤖 AI: Memenuhi Syarat</span>
                                                </span>
                                            @elseif($leave->ai_validation_status === 'Perlu Dokumen Tambahan')
                                                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">
                                                    <span>🤖 AI: Dokumen Wajib</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">
                                                    <span>🤖 AI: Melanggar Aturan</span>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- Detail -->
                                            <a href="{{ route('leaves.show', $leave->id) }}" class="p-2 text-sipega-navy hover:bg-slate-100 rounded-lg transition" title="Lihat Detail & Jejak Verifikasi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <!-- Cetak Formulir Lampiran BKN -->
                                            <a href="{{ route('leaves.print', $leave->id) }}" target="_blank" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Cetak Formulir BKN Resmi (Sesuai Lampiran)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            </a>
                                            <!-- Unduh PDF -->
                                            <a href="{{ route('leaves.pdf', $leave->id) }}" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Download PDF Formulir Cuti">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                        <div class="text-3xl mb-2">🏖️</div>
                                        <div class="font-bold text-gray-600">Belum Ada Data Pengajuan Cuti</div>
                                        <div class="text-xs text-gray-400 mt-1">Silakan klik tombol "Ajukan Cuti Baru" untuk membuat permohonan.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($leaves->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $leaves->links() }}
                </div>
                @endif
            </div>

            <!-- MODAL KONFIGURASI 2 SKENARIO KUOTA BERSAMAAN (5% - 10%) -->
            @if(in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan']))
            <div x-show="showConcurrentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showConcurrentModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showConcurrentModal = false"></div>

                    <div x-show="showConcurrentModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">⚙️</span>
                                <h3 class="text-sm font-black uppercase tracking-wider text-sipega-navy">Pengaturan Kuota Cuti Bersamaan</h3>
                            </div>
                            <button @click="showConcurrentModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">✕</button>
                        </div>

                        <form method="POST" action="{{ route('leaves.update_concurrent_settings') }}" class="space-y-4 text-xs">
                            @csrf

                            <div>
                                <label class="block font-bold text-gray-700 mb-2">Pilih Model Skenario Kuota:</label>
                                <div class="space-y-2">
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:bg-slate-50 cursor-pointer transition {{ $activeScenario === 'lembaga' ? 'bg-amber-50/60 border-amber-300' : '' }}">
                                        <input type="radio" name="leave_concurrent_scenario" value="lembaga" {{ $activeScenario === 'lembaga' ? 'checked' : '' }} class="mt-0.5 text-sipega-navy focus:ring-sipega-navy">
                                        <div>
                                            <div class="font-bold text-sipega-navy">Skenario 1: Berdasarkan Kuota Lembaga (Satker)</div>
                                            <div class="text-[11px] text-gray-500 mt-0.5">Kapasitas dihitung dari total seluruh pegawai BPMP Kaltim. Cocok jika beban tugas merata antar divisi.</div>
                                        </div>
                                    </label>

                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:bg-slate-50 cursor-pointer transition {{ $activeScenario === 'gugus_mutu' ? 'bg-purple-50/60 border-purple-300' : '' }}">
                                        <input type="radio" name="leave_concurrent_scenario" value="gugus_mutu" {{ $activeScenario === 'gugus_mutu' ? 'checked' : '' }} class="mt-0.5 text-purple-600 focus:ring-purple-600">
                                        <div>
                                            <div class="font-bold text-purple-900">Skenario 2: Berdasarkan Kuota Gugus Mutu (Unit Kerja)</div>
                                            <div class="text-[11px] text-gray-500 mt-0.5">Kapasitas dihitung proporsional per Gugus Mutu (GM 1 s.d GM 5 / TU). Menjamin tidak ada gugus mutu yang kosong/lumpuh.</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Ambang Batas Kuota (%):</label>
                                    <select name="leave_concurrent_percentage" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy font-bold">
                                        <option value="5" {{ (int)$activePct === 5 ? 'selected' : '' }}>5% Pegawai (Sangat Ketat)</option>
                                        <option value="7.5" {{ (float)$activePct == 7.5 ? 'selected' : '' }}>7.5% Pegawai (Moderat)</option>
                                        <option value="10" {{ (int)$activePct === 10 ? 'selected' : '' }}>10% Pegawai (Standar BKN)</option>
                                        <option value="15" {{ (int)$activePct === 15 ? 'selected' : '' }}>15% Pegawai (Fleksibel)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Mode Tindakan Sistem:</label>
                                    <div class="pt-2">
                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="leave_concurrent_strict" value="1" {{ $isStrict ? 'checked' : '' }} class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                                            <span class="text-[11px] font-semibold text-gray-700">Kunci/Tolak Otomatis jika Kuota Penuh</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50 p-3 rounded-xl text-[10px] text-gray-500 border border-gray-100">
                                💡 <em>Sesuai Perka BKN No. 24/2017 Romawi III.A.7, jika tidak menggunakan mode kunci otomatis, sistem memberikan peringatan AI dan rekomendasi kepada Atasan untuk menetapkan opsi <strong>PERUBAHAN</strong> atau <strong>DITANGGUHKAN</strong>.</em>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                                <button type="button" @click="showConcurrentModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-xs transition">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-sipega-navy hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition shadow">
                                    Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
