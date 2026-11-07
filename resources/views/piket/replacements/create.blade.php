<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('piket.conflicts.index') }}" class="w-8 h-8 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 flex items-center justify-center font-bold text-sm transition">
                ←
            </a>
            <div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight tracking-tight flex items-center gap-2">
                    <span>🔄</span> Konsol Penggantian Petugas Piket (3 Tingkat Prioritas)
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Pastikan tidak ada kekosongan meja pelayanan di {{ $assignment->unit->name }} ({{ $assignment->unit->code }})
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen" x-data="{ selectedOfficer: null, selectedPriority: null, selectedName: '' }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg shadow-sm text-sm font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- KARTU INFORMASI JADWAL YANG BERMASALAH -->
            <div class="bg-gradient-to-r from-sipega-navy to-slate-900 text-white p-6 rounded-2xl shadow-lg border border-slate-700">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $assignment->unit->code === 'ULT' ? 'bg-sky-400 text-slate-900' : 'bg-emerald-400 text-slate-900' }}">
                                {{ $assignment->unit->name }}
                            </span>
                            <span class="text-xs text-slate-300 font-semibold">{{ $assignment->role_label }}</span>
                        </div>
                        <div class="text-2xl font-black mt-2">
                            {{ $assignment->date->translatedFormat('l, d F Y') }}
                        </div>
                        <div class="text-xs text-slate-300 mt-1">
                            Petugas Awal Terjadwal: <strong class="text-white">{{ $assignment->activeOfficer->name ?? $assignment->primaryOfficer->name }}</strong> 
                            ({{ $assignment->activeOfficer->nip ?? 'Non-NIP' }})
                        </div>
                    </div>

                    @if($assignment->conflicts->where('status', 'pending')->count() > 0)
                        <div class="bg-rose-500/20 border border-rose-400/30 p-3.5 rounded-xl max-w-sm">
                            <div class="text-xs font-black text-rose-300 uppercase tracking-wider flex items-center gap-1.5">
                                <span>🚨</span> Indikasi Benturan Kegiatan
                            </div>
                            @foreach($assignment->conflicts->where('status', 'pending') as $conf)
                                <div class="text-xs text-white font-semibold mt-1">{{ $conf->description }}</div>
                                @if($conf->conflict_ref_number)
                                    <div class="text-[10px] text-rose-200 font-mono mt-0.5">Ref: {{ $conf->conflict_ref_number }}</div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- MEKANISME 3 TINGKAT PRIORITAS -->
            <form method="POST" action="{{ route('piket.replacements.store', $assignment->id) }}" class="space-y-6">
                @csrf

                <input type="hidden" name="replacement_officer_id" :value="selectedOfficer">
                <input type="hidden" name="priority_level" :value="selectedPriority">

                <!-- 1. PRIORITAS 1: PETUGAS CADANGAN TERDAFTAR -->
                <div class="bg-white rounded-2xl shadow-sm border-2 transition overflow-hidden" :class="selectedPriority === 'prioritas_1_cadangan' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-200'">
                    <div class="p-4 bg-blue-50/70 border-b border-blue-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center font-black text-xs">1</span>
                            <div>
                                <h3 class="text-sm font-black text-blue-900">Prioritas 1: Petugas Cadangan Terdaftar</h3>
                                <p class="text-[11px] text-blue-700">Mekanisme resmi pertama: aktifkan petugas cadangan yang telah ditetapkan pada jadwal ini</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-black bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full uppercase">Otomatis / Utama</span>
                    </div>

                    <div class="p-5">
                        @if($recommendations['tier1_backup'])
                            @php
                                $cadangan = $recommendations['tier1_backup'];
                                $isAvailable = $recommendations['tier1_backup_available'];
                            @endphp

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl border {{ $isAvailable ? 'border-blue-200 bg-blue-50/30' : 'border-rose-200 bg-rose-50/30' }} gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl {{ $isAvailable ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700' }} flex items-center justify-center font-black text-base">
                                        {{ substr($cadangan->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-black text-slate-800 text-sm">{{ $cadangan->name }}</div>
                                        <div class="text-xs text-slate-500 font-mono">NIP: {{ $cadangan->nip ?? '-' }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $cadangan->unit_kerja ?? 'Pegawai BPMP Kaltim' }}</div>
                                    </div>
                                </div>

                                <div>
                                    @if($isAvailable)
                                        <button type="button" 
                                            @click="selectedOfficer = '{{ $cadangan->id }}'; selectedPriority = 'prioritas_1_cadangan'; selectedName = '{{ $cadangan->name }} (Prioritas 1: Cadangan)'"
                                            class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2"
                                            :class="selectedOfficer === '{{ $cadangan->id }}' ? 'bg-emerald-600 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white shadow'">
                                            <span x-show="selectedOfficer === '{{ $cadangan->id }}'">✓ Terpilih</span>
                                            <span x-show="selectedOfficer !== '{{ $cadangan->id }}'">Pilih Cadangan Ini</span>
                                        </button>
                                    @else
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-800">
                                                🚨 Tidak Tersedia
                                            </span>
                                            <div class="text-[10px] text-rose-600 mt-1 max-w-xs text-right font-medium">
                                                {{ $recommendations['tier1_conflict_reason'] }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-500">
                                Jadwal ini tidak memiliki petugas cadangan awal yang terdaftar. Silakan gunakan <strong>Prioritas 2</strong> atau <strong>Prioritas 3</strong> di bawah.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 2. PRIORITAS 2: PETUGAS TERSEDIA (ROTASI ADIL) -->
                <div class="bg-white rounded-2xl shadow-sm border-2 transition overflow-hidden" :class="selectedPriority === 'prioritas_2_tersedia' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200'">
                    <div class="p-4 bg-emerald-50/70 border-b border-emerald-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-black text-xs">2</span>
                            <div>
                                <h3 class="text-sm font-black text-emerald-900">Prioritas 2: Petugas Tersedia (Rotasi Adil)</h3>
                                <p class="text-[11px] text-emerald-700">Pegawai eligible yang tidak ada jadwal dinas/cuti pada tanggal ini, diurutkan dari yang paling sedikit bertugas</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-black bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full uppercase">Pemerataan Tugas</span>
                    </div>

                    <div class="p-5">
                        @if(count($recommendations['tier2_available']) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-72 overflow-y-auto p-1">
                                @foreach($recommendations['tier2_available'] as $pegawai)
                                    <div class="p-3 rounded-xl border border-slate-200 hover:border-emerald-300 transition flex items-center justify-between gap-3 cursor-pointer"
                                         :class="selectedOfficer === '{{ $pegawai->id }}' ? 'bg-emerald-50 border-emerald-500 ring-1 ring-emerald-500' : 'bg-white'"
                                         @click="selectedOfficer = '{{ $pegawai->id }}'; selectedPriority = 'prioritas_2_tersedia'; selectedName = '{{ $pegawai->name }} (Prioritas 2: Rotasi Adil)'">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-black text-xs shrink-0">
                                                {{ substr($pegawai->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <div class="text-xs font-black text-slate-800">{{ $pegawai->name }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $pegawai->unit_kerja ?? 'BPMP Kaltim' }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <span class="text-[10px] font-black px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                                {{ $pegawai->piket_count ?? 0 }}x Piket
                                            </span>
                                            <div class="text-[9px] text-emerald-600 font-bold mt-0.5">Bebas Tugas</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-500">
                                Tidak ada pegawai cadangan dari daftar eligible yang bebas jadwal pada tanggal ini.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 3. PRIORITAS 3: PENUNJUKAN MANUAL PEGAWAI LAIN -->
                <div class="bg-white rounded-2xl shadow-sm border-2 transition overflow-hidden" :class="selectedPriority === 'prioritas_3_manual' ? 'border-purple-500 ring-2 ring-purple-500/20' : 'border-slate-200'">
                    <div class="p-4 bg-purple-50/70 border-b border-purple-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-purple-600 text-white flex items-center justify-center font-black text-xs">3</span>
                            <div>
                                <h3 class="text-sm font-black text-purple-900">Prioritas 3: Penunjukan Bebas Pegawai BPMP (Diskresi Admin)</h3>
                                <p class="text-[11px] text-purple-700">Digunakan jika Prioritas 1 & 2 tidak memungkinkan. Pilih pegawai dari seluruh satker BPMP Kaltim.</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-black bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full uppercase">Diskresi Pimpinan</span>
                    </div>

                    <div class="p-5 space-y-3">
                        <label class="block text-xs font-bold text-slate-700">Pilih Pegawai Pengganti Bebas:</label>
                        <select @change="if($event.target.value) { selectedOfficer = $event.target.value; selectedPriority = 'prioritas_3_manual'; selectedName = $event.target.options[$event.target.selectedIndex].text + ' (Prioritas 3: Penunjukan)'; }"
                                class="w-full text-xs rounded-xl border-slate-300 focus:ring-purple-500 focus:border-purple-500 font-medium p-2.5">
                            <option value="">-- Pilih Pegawai Bebas --</option>
                            @foreach($recommendations['tier3_all_employees'] as $emp)
                                <option value="{{ $emp->id }}" :selected="selectedOfficer === '{{ $emp->id }}'">
                                    {{ $emp->name }} (NIP: {{ $emp->nip ?? '-' }}) - {{ $emp->unit_kerja ?? 'BPMP Kaltim' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 italic">
                            Catatan: Sistem akan tetap memvalidasi apakah pegawai yang ditunjuk bebas dari Surat Tugas dan Cuti pada tanggal tersebut saat tombol konfirmasi ditekan.
                        </p>
                    </div>
                </div>

                <!-- ALASAN PENGGANTIAN RESMI -->
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                    <label class="block text-xs font-bold text-slate-700">
                        Alasan Pergantian Petugas Piket <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="reason" rows="3" required placeholder="Tuliskan alasan resmi penetapan pengganti (contoh: Petugas utama mendapatkan Surat Tugas dinas luar kota No. 042/ST/2026)..."
                              class="w-full text-xs rounded-xl border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium p-3"></textarea>

                    <!-- RINGKASAN PILIHAN -->
                    <div x-show="selectedOfficer" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Petugas Terpilih:</div>
                            <div class="text-sm font-black text-slate-800" x-text="selectedName"></div>
                        </div>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow transition flex items-center gap-2">
                            <span>✓</span> Konfirmasi & Tetapkan Pengganti
                        </button>
                    </div>

                    <div x-show="!selectedOfficer" class="text-xs text-rose-600 font-bold italic">
                        * Silakan pilih salah satu calon pengganti dari Prioritas 1, 2, atau 3 di atas untuk mengaktifkan tombol konfirmasi.
                    </div>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
