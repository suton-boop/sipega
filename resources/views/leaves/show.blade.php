<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('leaves.index') }}" class="text-xs text-sipega-navy font-bold hover:underline">← Kembali ke Daftar Cuti</a>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-sipega-orange font-bold font-mono">{{ $leave->request_number }}</span>
                </div>
                <h2 class="text-2xl font-black text-sipega-navy tracking-tight">Detail Permohonan Cuti ASN</h2>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Tombol Cetak Formulir BKN -->
                <a href="{{ route('leaves.print', ['id' => $leave->id, 'view' => 'form']) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-sipega-navy hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Formulir BKN</span>
                </a>
                <!-- Tombol Cetak Surat Pengantar -->
                <a href="{{ route('leaves.print', ['id' => $leave->id, 'view' => 'letter']) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-sipega-navy text-xs font-bold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Surat Permohonan</span>
                </a>
                <!-- Unduh PDF -->
                <a href="{{ route('leaves.pdf', ['id' => $leave->id, 'view' => 'form']) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold rounded-xl transition border border-rose-200">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>PDF</span>
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

            <!-- Top Card: Alur Persetujuan & Status -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 pb-4">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-gray-400">Status Permohonan Saat Ini:</div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xl font-black text-sipega-navy">{{ $leave->status }}</span>
                            @if($leave->status === 'Disetujui')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">Selesai ✓</span>
                            @elseif($leave->status === 'Disetujui Atasan')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-800 border border-blue-200">Menunggu PPK</span>
                            @elseif(in_array($leave->status, ['Ditolak', 'Tidak Disetujui']))
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-800 border border-rose-200">Ditolak</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-200">Menunggu Atasan</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-black uppercase tracking-wider text-gray-400">Tanggal Pengajuan:</div>
                        <div class="text-sm font-bold text-gray-700 mt-1">{{ $leave->request_date->translatedFormat('l, d F Y') }}</div>
                    </div>
                </div>

                <!-- 3-Step Recommendation Flow Indicator -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Step 1: Atasan Langsung -->
                    <div class="p-4 rounded-xl border {{ $leave->supervisor_decision !== 'pending' ? ($leave->supervisor_decision === 'DISETUJUI' ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200') : 'bg-slate-50 border-gray-200' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-500">1. Atasan Langsung</span>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ $leave->supervisor_decision === 'DISETUJUI' ? 'bg-emerald-600 text-white' : ($leave->supervisor_decision !== 'pending' ? 'bg-rose-600 text-white' : 'bg-amber-100 text-amber-800') }}">
                                {{ $leave->supervisor_decision === 'pending' ? 'Menunggu' : $leave->supervisor_decision }}
                            </span>
                        </div>
                        <div class="text-xs font-bold text-sipega-navy">{{ $leave->supervisor_name ?? ($leave->supervisor->name ?? 'Belum Ditentukan') }}</div>
                        <div class="text-[10px] text-gray-500">NIP. {{ $leave->supervisor_nip ?? ($leave->supervisor->nip ?? '-') }}</div>
                        @if($leave->supervisor_notes)
                            <div class="text-[10px] text-gray-600 mt-2 bg-white/70 p-2 rounded border border-gray-100 italic">"{{ $leave->supervisor_notes }}"</div>
                        @endif
                    </div>

                    <!-- Step 2: Tim Kepegawaian Satker -->
                    <div class="p-4 rounded-xl border bg-slate-50 border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-500">2. Kepegawaian Satker</span>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                Terverifikasi Otomatis
                            </span>
                        </div>
                        <div class="text-xs font-bold text-sipega-navy">Subbagian Umum BPMP Kaltim</div>
                        <div class="text-[10px] text-gray-500">Rekonsiliasi Kuota N, N-1, N-2 Sesuai BKN</div>
                        <div class="text-[10px] text-gray-600 mt-2 bg-white/70 p-2 rounded border border-gray-100">
                            Status kuota tersinkronisasi: N={{ $leave->quota_n }} hari
                        </div>
                    </div>

                    <!-- Step 3: PPK / Kepala -->
                    <div class="p-4 rounded-xl border {{ $leave->approver_decision !== 'pending' ? ($leave->approver_decision === 'DISETUJUI' ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200') : 'bg-slate-50 border-gray-200' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-500">3. Pejabat Berwenang (PPK)</span>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full {{ $leave->approver_decision === 'DISETUJUI' ? 'bg-emerald-600 text-white' : ($leave->approver_decision !== 'pending' ? 'bg-rose-600 text-white' : 'bg-amber-100 text-amber-800') }}">
                                {{ $leave->approver_decision === 'pending' ? 'Menunggu' : $leave->approver_decision }}
                            </span>
                        </div>
                        <div class="text-xs font-bold text-sipega-navy">{{ $leave->approver_name ?? ($leave->approver->name ?? 'Dr. Jarwoko, M.Pd') }}</div>
                        <div class="text-[10px] text-gray-500">NIP. {{ $leave->approver_nip ?? ($leave->approver->nip ?? '-') }}</div>
                        @if($leave->approver_notes)
                            <div class="text-[10px] text-gray-600 mt-2 bg-white/70 p-2 rounded border border-gray-100 italic">"{{ $leave->approver_notes }}"</div>
                        @endif
                    </div>
                </div>

                <!-- Action Modals / Buttons for Supervisors & Approvers -->
                @if($canSupervisorAction)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="font-bold text-xs text-amber-900 mb-2 flex items-center gap-1.5">
                        <span>⚡ Tindakan Diperlukan: Anda adalah Atasan Langsung dari pemohon. Berikan pertimbangan pemberian cuti:</span>
                    </div>
                    <form method="POST" action="{{ route('leaves.process_approval', $leave->id) }}" class="flex flex-col md:flex-row gap-3 items-end">
                        @csrf
                        <input type="hidden" name="action_type" value="supervisor">
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-gray-600 mb-1">Catatan Pertimbangan (Opsional)</label>
                            <input type="text" name="notes" placeholder="Tuliskan catatan pertimbangan jika ada..." class="w-full text-xs rounded-xl border-gray-200">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" name="decision" value="DISETUJUI" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition">
                                ✓ Setujui (DISETUJUI)
                            </button>
                            <button type="submit" name="decision" value="PERUBAHAN" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">
                                Perubahan
                            </button>
                            <button type="submit" name="decision" value="DITANGGUHKAN" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition">
                                Ditangguhkan
                            </button>
                            <button type="submit" name="decision" value="TIDAK DISETUJUI" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition">
                                Tidak Disetujui
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                @if($canApproverAction && ($leave->supervisor_decision === 'DISETUJUI' || in_array(auth()->user()->role, ['Admin', 'Pimpinan'])))
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <div class="font-bold text-xs text-emerald-900 mb-2 flex items-center gap-1.5">
                        <span>⚡ Tindakan Diperlukan: Anda adalah Pejabat yang Berwenang Memberikan Cuti (PPK). Tetapkan keputusan cuti:</span>
                    </div>
                    <form method="POST" action="{{ route('leaves.process_approval', $leave->id) }}" class="flex flex-col md:flex-row gap-3 items-end">
                        @csrf
                        <input type="hidden" name="action_type" value="approver">
                        <div class="flex-1">
                            <label class="block text-[10px] font-bold text-gray-600 mb-1">Catatan Keputusan Resmi (Opsional)</label>
                            <input type="text" name="notes" placeholder="Tuliskan catatan keputusan jika ada..." class="w-full text-xs rounded-xl border-gray-200">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" name="decision" value="DISETUJUI" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider rounded-xl transition shadow">
                                ✓ Terbitkan Keputusan (DISETUJUI)
                            </button>
                            <button type="submit" name="decision" value="PERUBAHAN" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition">
                                Perubahan
                            </button>
                            <button type="submit" name="decision" value="DITANGGUHKAN" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition">
                                Ditangguhkan
                            </button>
                            <button type="submit" name="decision" value="TIDAK DISETUJUI" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition">
                                Tolak
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>

            <!-- Asisten AI Verification Summary Box -->
            <div class="bg-gradient-to-r from-slate-900 to-sipega-navy rounded-2xl p-6 text-white shadow-xl border border-white/10">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🤖</span>
                        <h4 class="text-xs font-black uppercase tracking-wider text-white">Hasil Audit & Verifikasi Asisten AI SIPEGA</h4>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $leave->ai_validation_status === 'Memenuhi Syarat' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' }}">
                        {{ $leave->ai_validation_status }}
                    </span>
                </div>
                <p class="text-xs text-slate-200 leading-relaxed bg-white/5 p-4 rounded-xl border border-white/5">
                    {{ $leave->ai_validation_summary }}
                </p>
                @if($leave->attachment_path)
                <div class="mt-4 flex items-center justify-between bg-white/10 p-3 rounded-xl">
                    <div class="flex items-center gap-2 text-xs">
                        <span>📎</span>
                        <span>Dokumen Terlampir: <strong>{{ $leave->attachment_name ?? 'Bukti Pendukung' }}</strong></span>
                    </div>
                    <a href="{{ asset('storage/' . $leave->attachment_path) }}" target="_blank" class="text-xs font-bold text-sipega-orange hover:underline">
                        Lihat Berkas →
                    </a>
                </div>
                @endif
            </div>

            <!-- Formulir BKN View Preview (8 Bagian) -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-base font-black text-sipega-navy uppercase tracking-wider">
                        Ringkasan Isi Formulir Permintaan dan Pemberian Cuti
                    </h3>
                    <p class="text-xs text-gray-500">Sesuai Format Lampiran Peraturan BKN No. 24 Tahun 2017 jo. No. 7 Tahun 2021</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs text-gray-700">
                    <!-- I. Data Pegawai -->
                    <div class="space-y-3 p-4 bg-slate-50 rounded-xl">
                        <div class="font-black text-sipega-navy uppercase border-b border-gray-200 pb-2">I. DATA PEGAWAI</div>
                        <div class="grid grid-cols-3 gap-2">
                            <span class="text-gray-500">Nama:</span>
                            <span class="col-span-2 font-bold">{{ $leave->employee_name }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <span class="text-gray-500">NIP:</span>
                            <span class="col-span-2 font-mono font-bold">{{ $leave->employee_nip }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <span class="text-gray-500">Jabatan:</span>
                            <span class="col-span-2">{{ $leave->employee_position }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <span class="text-gray-500">Status & Masa Kerja:</span>
                            <span class="col-span-2 font-semibold">{{ $leave->employee_status }} | {{ $leave->work_duration }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <span class="text-gray-500">Unit Kerja:</span>
                            <span class="col-span-2">{{ $leave->work_unit }}</span>
                        </div>
                    </div>

                    <!-- II & IV. Jenis & Lamanya Cuti -->
                    <div class="space-y-3 p-4 bg-slate-50 rounded-xl">
                        <div class="font-black text-sipega-navy uppercase border-b border-gray-200 pb-2">II & IV. JENIS & LAMANYA CUTI</div>
                        <div class="grid grid-cols-3 gap-2">
                            <span class="text-gray-500">Jenis Cuti:</span>
                            <span class="col-span-2 font-black text-sipega-navy">{{ $leave->leave_type_label }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <span class="text-gray-500">Lamanya Cuti:</span>
                            <span class="col-span-2 font-bold text-sipega-orange">{{ $leave->duration_amount }} {{ $leave->duration_unit }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <span class="text-gray-500">Tanggal:</span>
                            <span class="col-span-2 font-medium">{{ $leave->start_date->translatedFormat('d F Y') }} s.d {{ $leave->end_date->translatedFormat('d F Y') }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <span class="text-gray-500">Alasan:</span>
                            <span class="col-span-2 italic text-gray-800">{{ $leave->reason }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <span class="text-gray-500">Alamat Selama Cuti:</span>
                            <span class="col-span-2">{{ $leave->leave_address }} (Telp: {{ $leave->phone_number }})</span>
                        </div>
                    </div>
                </div>

                <!-- V. Catatan Cuti Snapshot Table -->
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="bg-slate-100 px-4 py-2.5 font-black text-xs uppercase text-sipega-navy border-b border-gray-200">
                        V. CATATAN CUTI (Snapshot Saat Pengajuan)
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200 text-xs">
                        <div class="p-4 space-y-2">
                            <div class="font-bold text-gray-700">1. CUTI TAHUNAN:</div>
                            <div class="grid grid-cols-3 gap-2 text-center text-[11px]">
                                <div class="bg-slate-50 p-2 rounded border border-gray-100">
                                    <div class="text-[9px] text-gray-400">Tahun N-2 ({{ $leave->year_n2 }})</div>
                                    <div class="font-black text-gray-700">{{ $leave->quota_n2 }} hari</div>
                                </div>
                                <div class="bg-slate-50 p-2 rounded border border-gray-100">
                                    <div class="text-[9px] text-gray-400">Tahun N-1 ({{ $leave->year_n1 }})</div>
                                    <div class="font-black text-gray-700">{{ $leave->quota_n1 }} hari</div>
                                </div>
                                <div class="bg-slate-50 p-2 rounded border border-gray-100">
                                    <div class="text-[9px] text-gray-400">Tahun N ({{ $leave->year_n }})</div>
                                    <div class="font-black text-sipega-navy">{{ $leave->quota_n }} hari</div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 space-y-1.5 text-gray-600">
                            <div>2. Cuti Besar: <strong>{{ $leave->note_cuti_besar ?? '-' }}</strong></div>
                            <div>3. Cuti Sakit: <strong>{{ $leave->note_cuti_sakit ?? '-' }}</strong></div>
                            <div>4. Cuti Melahirkan: <strong>{{ $leave->note_cuti_melahirkan ?? '-' }}</strong></div>
                            <div>5. Cuti Karena Alasan Penting: <strong>{{ $leave->note_cuti_alasan_penting ?? '-' }}</strong></div>
                            <div>6. Cuti di Luar Tanggungan Negara: <strong>{{ $leave->note_cltn ?? '-' }}</strong></div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
