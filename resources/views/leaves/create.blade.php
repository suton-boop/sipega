<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('leaves.index') }}" class="text-xs text-sipega-navy font-bold hover:underline">← Kembali ke Data Cuti</a>
                    <span class="text-xs text-gray-400">•</span>
                    <span class="text-xs text-sipega-orange font-bold uppercase tracking-wider">Formulir Pengajuan Baru</span>
                </div>
                <h2 class="text-2xl font-black text-sipega-navy tracking-tight">Pengajuan Cuti Pegawai ASN</h2>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>AI Verifikator SIPEGA Aktif</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(isset($errors) && $errors->any())
            <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl text-xs text-rose-700">
                <div class="font-bold mb-1">Pengajuan Belum Dapat Disimpan:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form id="leaveForm" method="POST" action="{{ route('leaves.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <!-- Left Column: Form Fields (7 cols) -->
                    <div class="lg:col-span-7 space-y-6">

                        <!-- Section 1: Data Pegawai -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <h3 class="text-sm font-black uppercase tracking-wider text-sipega-navy flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-sipega-navy text-white flex items-center justify-center text-xs">1</span>
                                    <span>I. Data Pegawai Pemohon</span>
                                </h3>
                                <span class="text-[11px] font-bold text-sipega-orange uppercase tracking-wider">Format Standar BKN</span>
                            </div>

                            @if($isPrivileged && $employees->count() > 1)
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Pegawai (Khusus Operator / Admin)</label>
                                <select id="user_id_select" name="user_id" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy" onchange="window.location.href='{{ route('leaves.create') }}?user_id=' + this.value">
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ $targetUser->id === $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }} (NIP. {{ $emp->nip ?? '-' }}) - {{ $emp->effective_status_kepegawaian }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                                <input type="hidden" name="user_id" value="{{ $targetUser->id }}">
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap & Gelar</label>
                                    <input type="text" name="employee_name" id="employee_name" value="{{ old('employee_name', $targetUser->name) }}" class="w-full text-xs rounded-xl border-gray-200 bg-gray-50 text-gray-700" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">NIP Pegawai</label>
                                    <input type="text" name="employee_nip" id="employee_nip" value="{{ old('employee_nip', $targetUser->nip) }}" class="w-full text-xs rounded-xl border-gray-200 bg-gray-50 text-gray-700 font-mono" readonly>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Status Kepegawaian</label>
                                    <select name="employee_status" id="employee_status" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy" onchange="runAiValidation()">
                                        <option value="PNS" {{ old('employee_status', $targetUser->effective_status_kepegawaian) === 'PNS' ? 'selected' : '' }}>PNS (Pegawai Negeri Sipil)</option>
                                        <option value="PPPK" {{ old('employee_status', $targetUser->effective_status_kepegawaian) === 'PPPK' ? 'selected' : '' }}>PPPK (P3K)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Jabatan</label>
                                    <input type="text" name="employee_position" id="employee_position" value="{{ old('employee_position', $targetUser->position ?? 'Penelaah Informasi dan Komunikasi Publik') }}" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy" oninput="runAiValidation()">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Masa Kerja</label>
                                    <input type="text" name="work_duration" id="work_duration" value="{{ old('work_duration', $masaKerja) }}" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy" oninput="runAiValidation()">
                                    <p class="text-[9px] text-gray-400 mt-1">Dihitung otomatis dari NIP / TMT</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Unit Kerja</label>
                                <input type="text" name="work_unit" value="{{ old('work_unit', $targetUser->unit_kerja ?? 'BPMP Kalimantan Timur') }}" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy">
                            </div>
                        </div>

                        <!-- Section 2: Jenis & Waktu Cuti -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <h3 class="text-sm font-black uppercase tracking-wider text-sipega-navy flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-sipega-navy text-white flex items-center justify-center text-xs">2</span>
                                    <span>II. Rincian & Durasi Cuti</span>
                                </h3>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Jenis Cuti yang Diambil <span class="text-rose-500">*</span></label>
                                <select name="leave_type" id="leave_type" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy font-bold text-sipega-navy" onchange="runAiValidation()" required>
                                    <option value="cuti_tahunan" {{ old('leave_type') === 'cuti_tahunan' ? 'selected' : '' }}>1. Cuti Tahunan (12 hari kerja/tahun)</option>
                                    <option value="cuti_besar" {{ old('leave_type') === 'cuti_besar' ? 'selected' : '' }}>2. Cuti Besar (Maks. 3 Bulan - PNS masa kerja min. 5 thn)</option>
                                    <option value="cuti_sakit" {{ old('leave_type') === 'cuti_sakit' ? 'selected' : '' }}>3. Cuti Sakit (Wajib Surat Dokter)</option>
                                    <option value="cuti_melahirkan" {{ old('leave_type') === 'cuti_melahirkan' ? 'selected' : '' }}>4. Cuti Melahirkan (Kelahiran anak 1-3, maks 3 bln)</option>
                                    <option value="cuti_alasan_penting" {{ old('leave_type') === 'cuti_alasan_penting' ? 'selected' : '' }}>5. Cuti Karena Alasan Penting (Keluarga/Nikah/Bencana)</option>
                                    <option value="cuti_cltn" {{ old('leave_type') === 'cuti_cltn' ? 'selected' : '' }}>6. Cuti di Luar Tanggungan Negara (CLTN - min 3 thn)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Lamanya Cuti <span class="text-rose-500">*</span></label>
                                    <div class="flex gap-2">
                                        <input type="number" name="duration_amount" id="duration_amount" value="{{ old('duration_amount', 1) }}" min="1" max="365" class="w-24 text-xs rounded-xl border-gray-200 font-bold focus:border-sipega-navy focus:ring-sipega-navy" oninput="calculateDates(); runAiValidation();" required>
                                        <select name="duration_unit" id="duration_unit" class="flex-1 text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy" onchange="calculateDates(); runAiValidation();">
                                            <option value="hari">Hari Kerja</option>
                                            <option value="bulan">Bulan</option>
                                            <option value="tahun">Tahun</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Rentang Tanggal Pelaksanaan <span class="text-rose-500">*</span></label>
                                    <div class="flex items-center gap-2">
                                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy" onchange="calculateDates(); runAiValidation();" required>
                                        <span class="text-xs text-gray-400 font-bold">s.d</span>
                                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', now()->format('Y-m-d')) }}" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy" onchange="runAiValidation();" required>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">III. Alasan Cuti <span class="text-rose-500">*</span></label>
                                <textarea name="reason" id="reason" rows="3" placeholder="Contoh: Mengikuti Munas PB/PJSI di Jakarta..." class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy" oninput="runAiValidation()" required>{{ old('reason') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Dokumen Pendukung Wajib (Format PDF/JPG/PNG, Maks. 5MB)</label>
                                <input type="file" name="attachment" id="attachment" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sipega-navy file:text-white hover:file:bg-slate-800" onchange="toggleAttachmentCheck(this)">
                                <p id="docNotice" class="text-[11px] text-amber-600 mt-1 font-medium"></p>
                            </div>
                        </div>

                        <!-- Section 3: Alamat Selama Cuti & Pejabat -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <h3 class="text-sm font-black uppercase tracking-wider text-sipega-navy flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-sipega-navy text-white flex items-center justify-center text-xs">3</span>
                                    <span>VI. Alamat Selama Cuti & Alur Persetujuan</span>
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Selama Cuti <span class="text-rose-500">*</span></label>
                                    <textarea name="leave_address" id="leave_address" rows="2" placeholder="Contoh: Jakarta / Alamat domisili selama cuti..." class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy" required>{{ old('leave_address', 'Samarinda') }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Telepon / HP Aktif <span class="text-rose-500">*</span></label>
                                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $targetUser->phone ?? '085252728423') }}" placeholder="08xxxxxxxxxx" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy font-mono" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Atasan Langsung (Pertimbangan VII)</label>
                                    <select name="supervisor_id" id="supervisor_id" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy">
                                        <option value="">-- Pilih Atasan Langsung --</option>
                                        @foreach($supervisors as $spv)
                                            <option value="{{ $spv->id }}" {{ (old('supervisor_id') == $spv->id || stripos($spv->position, 'Kasubag') !== false || stripos($spv->name, 'Soerjo') !== false) ? 'selected' : '' }}>
                                                {{ $spv->name }} ({{ $spv->position ?? 'Pejabat Struktural' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Pejabat Pembina Kepegawaian / PPK (Keputusan VIII)</label>
                                    <select name="approver_id" id="approver_id" class="w-full text-xs rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy">
                                        @foreach($ppkOfficers as $ppk)
                                            <option value="{{ $ppk->id }}" {{ (old('approver_id') == $ppk->id || stripos($ppk->position, 'Kepala BPMP') !== false || stripos($ppk->name, 'Jarwoko') !== false) ? 'selected' : '' }}>
                                                {{ $ppk->name }} ({{ $ppk->position ?? 'Kepala Balai' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between pt-2">
                            <a href="{{ route('leaves.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-700">Batalkan</a>
                            <button type="submit" id="submitBtn" class="inline-flex items-center gap-2 px-6 py-3 bg-sipega-navy hover:bg-slate-800 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-lg transition">
                                <svg class="w-4 h-4 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Kirim Permohonan Cuti</span>
                            </button>
                        </div>

                    </div>

                    <!-- Right Column: Asisten AI SIPEGA Verifikator (5 cols sticky) -->
                    <div class="lg:col-span-5 space-y-6 sticky top-6">

                        <!-- Main AI Assistant Card -->
                        <div class="bg-gradient-to-b from-slate-900 to-sipega-navy rounded-2xl p-6 text-white shadow-2xl border border-white/10 relative overflow-hidden">
                            <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-sipega-orange/20 border border-sipega-orange/40 flex items-center justify-center text-lg">
                                        🤖
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black uppercase tracking-wider text-white">Asisten AI Layanan Cuti</h4>
                                        <div class="text-[10px] text-slate-300">Sistem Verifikasi Otomatis Regulasi ASN</div>
                                    </div>
                                </div>
                                <span id="aiStatusBadge" class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    Memenuhi Syarat
                                </span>
                            </div>

                            <!-- Live Validation Output -->
                            <div class="space-y-4 text-xs">
                                <!-- Summary Status -->
                                <div class="bg-white/5 rounded-xl p-3 border border-white/5">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-sipega-orange mb-1">Ringkasan Validasi Masa Kerja & Kuota:</div>
                                    <p id="aiSummary" class="text-[11px] text-slate-200 leading-relaxed">
                                        Pegawai {{ $targetUser->name }} berstatus {{ $targetUser->effective_status_kepegawaian }}. Masa kerja terverifikasi: {{ $masaKerja }}. Sisa cuti tahunan {{ $balance->remaining_quota }} hari kerja.
                                    </p>
                                </div>

                                <!-- Alerts / Errors (if any) -->
                                <div id="aiErrorsContainer" class="hidden bg-rose-500/20 border border-rose-500/40 rounded-xl p-3 text-rose-200">
                                    <div class="text-[10px] font-black uppercase tracking-wider text-rose-300 mb-1 flex items-center gap-1.5">
                                        <span>⚠️ Pelanggaran Regulasi:</span>
                                    </div>
                                    <ul id="aiErrorsList" class="list-disc list-inside space-y-1 text-[11px]"></ul>
                                </div>

                                <!-- Required Docs (if any) -->
                                <div id="aiDocsContainer" class="hidden bg-amber-500/20 border border-amber-500/40 rounded-xl p-3 text-amber-200">
                                    <div class="text-[10px] font-black uppercase tracking-wider text-amber-300 mb-1 flex items-center gap-1.5">
                                        <span>📑 Dokumen Pendukung Wajib:</span>
                                    </div>
                                    <ul id="aiDocsList" class="list-disc list-inside space-y-1 text-[11px]"></ul>
                                </div>

                                <!-- Approval Recommendation Flow -->
                                <div class="bg-white/5 rounded-xl p-3 border border-white/5">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-300 mb-1.5">Alur Rekomendasi Persetujuan:</div>
                                    <div class="text-[11px] font-semibold text-emerald-300">
                                        (1) Atasan Langsung ➔ (2) Tim Kepegawaian Satker ➔ (3) Pejabat Pembina Kepegawaian (PPK).
                                    </div>
                                </div>

                                <!-- Snapshot Catatan Cuti (Tabel N, N-1, N-2) -->
                                <div class="bg-black/30 rounded-xl p-3 border border-white/5">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-300 mb-2">Snapshot Catatan Cuti (Formulir BKN):</div>
                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div class="bg-white/5 rounded p-1.5">
                                            <div class="text-[8px] text-slate-400">N-2 ({{ $currentYear - 2 }})</div>
                                            <div id="quotaN2Text" class="text-sm font-black text-amber-400">{{ $balance->remaining_n2 }}</div>
                                        </div>
                                        <div class="bg-white/5 rounded p-1.5">
                                            <div class="text-[8px] text-slate-400">N-1 ({{ $currentYear - 1 }})</div>
                                            <div id="quotaN1Text" class="text-sm font-black text-emerald-400">{{ $balance->remaining_n1 }}</div>
                                        </div>
                                        <div class="bg-white/5 rounded p-1.5">
                                            <div class="text-[8px] text-slate-400">N ({{ $currentYear }})</div>
                                            <div id="quotaNText" class="text-sm font-black text-white">{{ $balance->remaining_quota }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Audit Kuota Bersamaan (Threshold 5% - 10%): 2 Skenario -->
                                <div class="bg-white/5 rounded-xl p-3.5 border border-white/10 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="text-[10px] font-black uppercase tracking-wider text-amber-300 flex items-center gap-1.5">
                                            <span>📊 Kuota Cuti Bersamaan (<span id="concurrentPctBadge">10</span>%)</span>
                                        </div>
                                        <span id="activeScenarioBadge" class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-white/20 text-white">
                                            Skenario Lembaga
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 text-[10px]">
                                        <!-- Skenario 1: Lembaga -->
                                        <div id="cardScenarioInst" class="bg-white/5 p-2.5 rounded-lg border border-white/10 space-y-1">
                                            <div class="font-bold text-slate-300 flex items-center justify-between">
                                                <span>1. Kuota Lembaga</span>
                                                <span id="badgeInstActive" class="text-[7px] px-1 bg-amber-400 text-slate-900 font-black rounded hidden">AKTIF</span>
                                            </div>
                                            <div class="text-[9px] text-slate-400">Satker BPMP Kaltim</div>
                                            <div class="text-xs font-black text-white mt-1">
                                                <span id="instCurrentCount">0</span> / <span id="instMaxQuota">2</span> <span class="text-[9px] font-normal text-slate-300">pegawai</span>
                                            </div>
                                            <div id="instStatusText" class="text-[9px] font-bold text-emerald-400">Sisa: 2 slot</div>
                                        </div>

                                        <!-- Skenario 2: Gugus Mutu -->
                                        <div id="cardScenarioGm" class="bg-white/5 p-2.5 rounded-lg border border-white/10 space-y-1">
                                            <div class="font-bold text-slate-300 flex items-center justify-between">
                                                <span>2. Gugus Mutu</span>
                                                <span id="badgeGmActive" class="text-[7px] px-1 bg-amber-400 text-slate-900 font-black rounded hidden">AKTIF</span>
                                            </div>
                                            <div id="gmNameDisplay" class="text-[9px] text-slate-400 truncate">{{ $targetUser->gugus_mutu ?? 'Non-Gugus' }}</div>
                                            <div class="text-xs font-black text-white mt-1">
                                                <span id="gmCurrentCount">0</span> / <span id="gmMaxQuota">1</span> <span class="text-[9px] font-normal text-slate-300">pegawai</span>
                                            </div>
                                            <div id="gmStatusText" class="text-[9px] font-bold text-emerald-400">Sisa: 1 slot</div>
                                        </div>
                                    </div>

                                    <!-- Alert Konflik Bersamaan jika ada -->
                                    <div id="concurrentPeersBox" class="hidden text-[10px] bg-amber-500/10 border border-amber-500/20 p-2 rounded text-amber-200">
                                        <div class="font-bold text-amber-300 mb-0.5">Pegawai lain cuti di tanggal ini:</div>
                                        <div id="concurrentPeersList" class="text-[9px] text-slate-200"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Live Draft Letter Box Collapsible -->
                        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm" x-data="{ openDraft: false }">
                            <button type="button" @click="openDraft = !openDraft" class="w-full flex items-center justify-between text-left">
                                <span class="text-xs font-black uppercase tracking-wider text-sipega-navy flex items-center gap-1.5">
                                    <span>📜 Draf Surat Izin Permintaan Cuti</span>
                                </span>
                                <span class="text-xs text-sipega-orange font-bold" x-text="openDraft ? 'Tutup ▲' : 'Lihat Draf ▼'"></span>
                            </button>
                            <div x-show="openDraft" x-cloak class="mt-3 pt-3 border-t border-gray-100">
                                <pre id="aiDraftLetter" class="text-[10px] font-mono text-gray-700 bg-slate-50 p-3 rounded-xl whitespace-pre-wrap leading-relaxed max-h-72 overflow-y-auto border border-gray-100">Memuat draf resmi...</pre>
                            </div>
                        </div>

                    </div>

                </div>
            </form>

        </div>
    </div>

    @push('scripts')
    <script>
        let hasAttachmentFile = false;

        function toggleAttachmentCheck(input) {
            hasAttachmentFile = input.files && input.files.length > 0;
            runAiValidation();
        }

        function calculateDates() {
            const startInput = document.getElementById('start_date');
            const endInput = document.getElementById('end_date');
            const durationInput = document.getElementById('duration_amount');
            const unitSelect = document.getElementById('duration_unit');

            if (!startInput.value || !durationInput.value) return;

            const days = parseInt(durationInput.value) || 1;
            const unit = unitSelect.value;
            const startDate = new Date(startInput.value);

            if (unit === 'hari') {
                // Tambahkan hari kerja sederhana (estimasi kalender -1)
                const endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + (days - 1));
                endInput.value = endDate.toISOString().split('T')[0];
            } else if (unit === 'bulan') {
                const endDate = new Date(startDate);
                endDate.setMonth(startDate.getMonth() + days);
                endInput.value = endDate.toISOString().split('T')[0];
            }
        }

        async function runAiValidation() {
            const payload = {
                user_id: {{ $targetUser->id }},
                employee_status: document.getElementById('employee_status').value,
                employee_position: document.getElementById('employee_position').value,
                leave_type: document.getElementById('leave_type').value,
                duration_amount: document.getElementById('duration_amount').value,
                duration_unit: document.getElementById('duration_unit').value,
                start_date: document.getElementById('start_date').value,
                end_date: document.getElementById('end_date').value,
                reason: document.getElementById('reason').value,
                work_duration: document.getElementById('work_duration').value,
                has_attachment: hasAttachmentFile
            };

            try {
                const response = await fetch('{{ route("leaves.ai_check") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                // Update Status Badge
                const badge = document.getElementById('aiStatusBadge');
                badge.textContent = data.status;
                if (data.status === 'Memenuhi Syarat') {
                    badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
                } else if (data.status === 'Perlu Dokumen Tambahan') {
                    badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30';
                } else {
                    badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30';
                }

                // Update Summary
                document.getElementById('aiSummary').textContent = data.summary;

                // Update Errors
                const errContainer = document.getElementById('aiErrorsContainer');
                const errList = document.getElementById('aiErrorsList');
                errList.innerHTML = '';
                if (data.errors && data.errors.length > 0) {
                    data.errors.forEach(err => {
                        const li = document.createElement('li');
                        li.textContent = err;
                        errList.appendChild(li);
                    });
                    errContainer.classList.remove('hidden');
                } else {
                    errContainer.classList.add('hidden');
                }

                // Update Docs
                const docContainer = document.getElementById('aiDocsContainer');
                const docList = document.getElementById('aiDocsList');
                const docNotice = document.getElementById('docNotice');
                docList.innerHTML = '';
                if (data.required_docs && data.required_docs.length > 0) {
                    data.required_docs.forEach(doc => {
                        const li = document.createElement('li');
                        li.textContent = doc;
                        docList.appendChild(li);
                    });
                    docContainer.classList.remove('hidden');
                    docNotice.textContent = 'Wajib: ' + data.required_docs.join(', ');
                } else {
                    docContainer.classList.add('hidden');
                    docNotice.textContent = '';
                }

                // Update Draft Letter
                if (data.draft_letter) {
                    document.getElementById('aiDraftLetter').textContent = data.draft_letter;
                }

                // Update Audit Kuota Bersamaan (2 Skenario: Lembaga vs Gugus Mutu)
                if (data.concurrent_audit) {
                    const ca = data.concurrent_audit;
                    document.getElementById('concurrentPctBadge').textContent = ca.percentage;
                    const scenarioBadge = document.getElementById('activeScenarioBadge');
                    scenarioBadge.textContent = ca.active_scenario === 'gugus_mutu' ? 'Skenario Gugus Mutu Aktif' : 'Skenario Lembaga Aktif';
                    if (ca.active_scenario === 'gugus_mutu') {
                        scenarioBadge.className = 'px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-purple-500/30 text-purple-200 border border-purple-400/30';
                        document.getElementById('badgeGmActive').classList.remove('hidden');
                        document.getElementById('badgeInstActive').classList.add('hidden');
                    } else {
                        scenarioBadge.className = 'px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-amber-500/30 text-amber-200 border border-amber-400/30';
                        document.getElementById('badgeInstActive').classList.remove('hidden');
                        document.getElementById('badgeGmActive').classList.add('hidden');
                    }

                    // Skenario 1: Lembaga
                    document.getElementById('instCurrentCount').textContent = ca.institution.current_count;
                    document.getElementById('instMaxQuota').textContent = ca.institution.max_quota;
                    const instStatus = document.getElementById('instStatusText');
                    if (ca.institution.is_exceeded) {
                        instStatus.textContent = 'Penuh / Melebihi Kuota!';
                        instStatus.className = 'text-[9px] font-bold text-rose-400';
                    } else {
                        instStatus.textContent = 'Sisa: ' + ca.institution.remaining_slots + ' slot';
                        instStatus.className = 'text-[9px] font-bold text-emerald-400';
                    }

                    // Skenario 2: Gugus Mutu
                    document.getElementById('gmNameDisplay').textContent = ca.gugus_mutu.name;
                    document.getElementById('gmCurrentCount').textContent = ca.gugus_mutu.current_count;
                    document.getElementById('gmMaxQuota').textContent = ca.gugus_mutu.max_quota;
                    const gmStatus = document.getElementById('gmStatusText');
                    if (ca.gugus_mutu.is_exceeded) {
                        gmStatus.textContent = 'Penuh / Melebihi Kuota!';
                        gmStatus.className = 'text-[9px] font-bold text-rose-400';
                    } else {
                        gmStatus.textContent = 'Sisa: ' + ca.gugus_mutu.remaining_slots + ' slot';
                        gmStatus.className = 'text-[9px] font-bold text-emerald-400';
                    }

                    // Peers list
                    const peersBox = document.getElementById('concurrentPeersBox');
                    const peersList = document.getElementById('concurrentPeersList');
                    if (ca.institution.peers && ca.institution.peers.length > 0) {
                        peersBox.classList.remove('hidden');
                        const peerText = ca.institution.peers.map(p => `${p.name} (${p.gugus_mutu}): ${p.period}`).join(' • ');
                        peersList.textContent = peerText;
                    } else {
                        peersBox.classList.add('hidden');
                    }
                }

            } catch (error) {
                console.error('AI check error:', error);
            }
        }

        // Jalankan validasi pertama kali saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            runAiValidation();
        });
    </script>
    @endpush
</x-app-layout>
