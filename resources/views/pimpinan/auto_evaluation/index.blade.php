<x-app-layout>
    <div class="bg-slate-100 min-h-screen py-8" x-data="{ 
        enabled: {{ $settings['enabled'] ? 'true' : 'false' }},
        score: {{ $settings['score'] }},
        showConfirmModal: false
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- FLASH MESSAGE -->
            @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-2xl text-xs text-emerald-900 font-bold shadow-md flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-base">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif

            <!-- TOP BANNER EXECUTIVE LEVEL PIMPINAN -->
            <div class="rounded-2xl shadow-xl overflow-hidden border border-slate-700" style="background: linear-gradient(135deg, #0f172a 0%, #003366 60%, #005a9c 100%); color: #ffffff;">
                <div class="px-6 py-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-black text-2xl text-white shadow-lg border border-amber-400/40" style="background: linear-gradient(135deg, #FF8C00 0%, #e65100 100%);">
                            ⚡
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="bg-amber-400 text-slate-950 text-[9px] font-black uppercase px-2.5 py-0.5 rounded-full tracking-widest shadow-sm">
                                    KHUSUS LEVEL PIMPINAN
                                </span>
                                <span class="text-xs text-blue-200">Sistem Informasi Kepegawaian (SIPEGA)</span>
                            </div>
                            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white mt-1">
                                Otomatisasi Penilaian Kinerja Pegawai
                            </h1>
                            <p class="text-xs text-blue-100/90 max-w-2xl mt-1 leading-relaxed">
                                Fitur delegasi pimpinan untuk membantu Kasubag saat memiliki tingkat kesibukan tinggi. Sistem secara otomatis memberikan penilaian terstandar pada seluruh pegawai di jam <strong>24:00 WITA</strong>.
                            </p>
                        </div>
                    </div>

                    <!-- STATUS BADGE -->
                    <div class="text-right flex flex-col items-end">
                        <div class="text-[10px] uppercase font-bold text-blue-200 tracking-wider">Status Otomatisasi</div>
                        <div class="mt-1 flex items-center gap-2 px-4 py-2 rounded-xl shadow-inner border {{ $settings['enabled'] ? 'bg-emerald-500/20 border-emerald-400 text-emerald-300' : 'bg-rose-500/20 border-rose-400 text-rose-300' }}">
                            <span class="w-3 h-3 rounded-full {{ $settings['enabled'] ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                            <span class="text-sm font-black tracking-wider uppercase">
                                {{ $settings['enabled'] ? 'AKTIF (ON)' : 'NONAKTIF (OFF)' }}
                            </span>
                        </div>
                        <div class="text-[10px] text-blue-200/80 mt-1">
                            Target Jam: <strong>{{ $settings['time'] }} WITA</strong> • Nilai: <strong>{{ $settings['score'] }}/100</strong>
                        </div>
                    </div>
                </div>

                <!-- SUB-INFO BAR -->
                <div class="px-6 py-3 border-t border-white/10 text-xs text-blue-100 flex flex-wrap items-center justify-between gap-3" style="background: rgba(0, 0, 0, 0.25);">
                    <div class="flex items-center gap-2">
                        <span>ℹ️</span>
                        <span>Terakhir dieksekusi: <strong>{{ $settings['last_run'] ? \Carbon\Carbon::parse($settings['last_run'])->translatedFormat('d M Y, H:i') . ' WITA (' . $settings['last_count'] . ' pegawai)' : 'Belum pernah dijalankan' }}</strong></span>
                        @if($settings['last_actor'])
                            <span>• Oleh: {{ $settings['last_actor'] }}</span>
                        @endif
                    </div>
                    <a href="{{ route('leader.agenda.index') }}" class="text-amber-300 hover:text-white font-bold text-xs underline underline-offset-2 flex items-center gap-1">
                        <span>Buka Penilaian Manual (Kasubag/Leader)</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

            <!-- STATISTIK HARI INI -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Total Pegawai BPMP</div>
                    <div class="text-3xl font-black text-slate-900 mt-1">{{ $stats['total_employees'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Pegawai aktif satker</div>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="text-[11px] font-bold text-emerald-600 uppercase">Sudah Dinilai Hari Ini</div>
                    <div class="text-3xl font-black text-emerald-600 mt-1">{{ $stats['evaluated_today'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $stats['today_formatted'] }}</div>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="text-[11px] font-bold text-amber-600 uppercase">Belum Dinilai (Antrian)</div>
                    <div class="text-3xl font-black text-amber-600 mt-1">{{ $stats['unrated_today'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Memerlukan respon atasan</div>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="text-[11px] font-bold text-blue-600 uppercase">Belum Ada Agenda</div>
                    <div class="text-3xl font-black text-blue-600 mt-1">{{ $stats['no_agenda_today'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">Bisa di-cover otomatis</div>
                </div>
            </div>

            <!-- MAIN GRID: FORM KONFIGURASI + ACTION ON-DEMAND -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- KOLOM KIRI: FORM PENGATURAN (7 cols) -->
                <div class="lg:col-span-7 bg-white rounded-2xl p-6 shadow-md border border-slate-200 space-y-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white shadow" style="background-color: #003366;">⚙️</span>
                            <div>
                                <h2 class="text-base font-black text-slate-900">Konfigurasi Penilaian Otomatis</h2>
                                <p class="text-[11px] text-slate-500">Atur logika auto-menilai untuk aplikasi SIPEGA</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('pimpinan.auto_evaluation.update') }}" class="space-y-5 text-xs">
                        @csrf

                        <!-- 1. CHECKBOX TOGGLE AUTO MENILAI (PERSYARATAN UTAMA) -->
                        <div class="p-4 rounded-2xl border-2 transition-all flex items-start gap-4 cursor-pointer"
                             :class="enabled ? 'bg-emerald-50/70 border-emerald-500 shadow-sm' : 'bg-slate-50 border-slate-200'"
                             @click="enabled = !enabled">
                            <input type="checkbox" name="enabled" value="1" x-model="enabled" class="w-5 h-5 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <div>
                                <label class="font-black text-sm text-slate-900 block cursor-pointer">
                                    Aktifkan Penilaian Otomatis Harian Aplikasi (Auto Menilai)
                                </label>
                                <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">
                                    Jika dicentang, sistem SIPEGA akan otomatis mengeksekusi penilaian pada jam yang ditentukan (misal: jam 24:00) untuk seluruh pegawai yang belum sempat dinilai secara manual oleh Kasubag.
                                </p>
                            </div>
                        </div>

                        <!-- 2. JAM EKSEKUSI OTOMATIS (DEFAULT 24:00) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">
                                    Jam Eksekusi Otomatis Harian
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="time" name="time" value="{{ $settings['time'] }}" class="w-full text-xs rounded-xl border-slate-300 font-mono font-bold text-blue-900 bg-slate-50 focus:border-blue-600">
                                    <span class="text-xs font-bold text-slate-500">WITA</span>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">Rekomendasi: <strong>24:00 WITA</strong> (akhir hari kerja).</span>
                            </div>

                            <!-- 3. NILAI YANG DIBERIKAN (0-100) -->
                            <div>
                                <label class="block font-bold text-slate-700 mb-1 flex items-center justify-between">
                                    <span>Nilai yang Diberikan</span>
                                    <span class="text-xs font-black text-blue-700" x-text="score + ' / 100'"></span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="score" min="50" max="100" x-model="score" class="w-24 text-xs rounded-xl border-slate-300 font-mono font-black text-center text-blue-900 bg-slate-50 focus:border-blue-600" required>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="score = 85" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-[10px] font-bold text-slate-700">85</button>
                                        <button type="button" @click="score = 90" class="px-2 py-1 rounded bg-blue-100 hover:bg-blue-200 text-[10px] font-bold text-blue-900">90</button>
                                        <button type="button" @click="score = 95" class="px-2 py-1 rounded bg-emerald-100 hover:bg-emerald-200 text-[10px] font-bold text-emerald-900">95</button>
                                        <button type="button" @click="score = 100" class="px-2 py-1 rounded bg-amber-100 hover:bg-amber-200 text-[10px] font-bold text-amber-900">100</button>
                                    </div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">Skor standar: 90 (Kategori: <strong>Sangat Baik / Hijau</strong>).</span>
                            </div>
                        </div>

                        <!-- 4. CAKUPAN PENILAIAN (SCOPE) -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Cakupan Pegawai yang Dinilai Otomatis</label>
                            <select name="scope" class="w-full text-xs rounded-xl border-slate-300 bg-slate-50 font-medium focus:border-blue-600">
                                <option value="all_unrated" {{ $settings['scope'] === 'all_unrated' ? 'selected' : '' }}>
                                    ✓ Seluruh agenda hari ini yang belum memiliki nilai (Rekomendasi Utama)
                                </option>
                                <option value="unrated_submitted" {{ $settings['scope'] === 'unrated_submitted' ? 'selected' : '' }}>
                                    Hanya pegawai yang sudah klik kirim realisasi sore tapi belum sempat dinilai Kasubag
                                </option>
                                <option value="all_employees" {{ $settings['scope'] === 'all_employees' ? 'selected' : '' }}>
                                    Seluruh pegawai aktif BPMP tanpa terkecuali (Otomatis dibuatkan agenda jika kosong)
                                </option>
                            </select>
                        </div>

                        <!-- 5. CHECKBOX REALISASI OTOMATIS -->
                        <div class="flex items-center gap-2 p-3 bg-blue-50/50 rounded-xl border border-blue-100">
                            <input type="checkbox" name="auto_realization" value="1" id="auto_real" {{ $settings['auto_realization'] ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <label for="auto_real" class="text-xs text-slate-700 cursor-pointer font-semibold">
                                Otomatis tandai tugas tuntas terlaksana jika pegawai lupa melakukan realisasi sore sebelum jam 24:00.
                            </label>
                        </div>

                        <!-- 6. CATATAN / UMPAN BALIK ATASAN OTOMATIS -->
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Catatan / Umpan Balik Atasan (Tercatat di e-Kinerja)</label>
                            <textarea name="feedback" rows="2" class="w-full text-xs rounded-xl border-slate-300 bg-slate-50 focus:border-blue-600 font-medium" required>{{ $settings['feedback'] }}</textarea>
                            <span class="text-[10px] text-slate-400 mt-0.5 block">Catatan ini akan otomatis muncul pada laporan penilaian agenda pegawai terkait.</span>
                        </div>

                        <div class="pt-2 flex items-center justify-end">
                            <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-white shadow-md transition flex items-center gap-2 text-xs" style="background: linear-gradient(135deg, #003366 0%, #005a9c 100%);">
                                <span>💾</span>
                                <span>Simpan Konfigurasi Auto-Penilaian</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- KOLOM KANAN: EKSEKUSI LANGSUNG ON-DEMAND (5 cols) -->
                <div class="lg:col-span-5 space-y-6">
                    
                    <!-- KOTAK EKSEKUSI SEKARANG -->
                    <div class="rounded-2xl p-6 shadow-md border border-amber-300 space-y-4" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">⚡</span>
                            <div>
                                <h3 class="text-sm font-black text-amber-950 uppercase tracking-tight">Eksekusi Instan (Hari Ini)</h3>
                                <p class="text-[11px] text-amber-800">Bantu Kasubag sekarang jika antrian tugas sangat padat</p>
                            </div>
                        </div>

                        <p class="text-xs text-slate-700 leading-relaxed">
                            Pimpinan dapat langsung memberikan penilaian massal kepada <strong>seluruh pegawai yang belum dinilai hari ini</strong> secara instan dengan satu klik, tanpa perlu menunggu jam 24:00.
                        </p>

                        <div class="bg-white/80 p-3.5 rounded-xl border border-amber-200 text-xs space-y-1">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Nilai yang akan diberikan:</span>
                                <strong class="text-blue-900 font-mono text-sm">{{ $settings['score'] }} / 100</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Antrian belum dinilai hari ini:</span>
                                <strong class="text-amber-700 font-mono text-sm">{{ $stats['unrated_today'] }} Pegawai</strong>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('pimpinan.auto_evaluation.run_now') }}" onsubmit="return confirm('Apakah Anda yakin ingin mengeksekusi penilaian otomatis untuk seluruh pegawai yang belum dinilai hari ini?')">
                            @csrf
                            <input type="hidden" name="target_date" value="{{ now('Asia/Makassar')->toDateString() }}">
                            <button type="submit" class="w-full py-3.5 rounded-xl font-black text-xs uppercase tracking-wider text-white shadow-lg transition flex items-center justify-center gap-2" style="background: linear-gradient(135deg, #e65100 0%, #FF8C00 100%);">
                                <span class="text-base">⚡</span>
                                <span>Nilai Otomatis Seluruh Pegawai Sekarang</span>
                            </button>
                        </form>
                    </div>

                    <!-- KOTAK INFORMASI REGULASI & KASUBAG -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 space-y-3">
                        <h4 class="text-xs font-black uppercase text-slate-900 flex items-center gap-2">
                            <span>🛡️</span>
                            <span>Manfaat Pengaturan Bagi Kasubag</span>
                        </h4>
                        <ul class="text-xs text-slate-600 space-y-2 list-disc list-inside">
                            <li><strong>Mencegah Keterlambatan Skor SKP:</strong> Menjaga kepatuhan e-Kinerja pegawai agar tidak terpotong karena kelalaian atasan yang berhalangan/sibuk.</li>
                            <li><strong>Otoritas Terkendali:</strong> Hanya akun <strong>Level Pimpinan</strong> yang berhak mengaktifkan, menonaktifkan, atau mengubah nilai default.</li>
                            <li><strong>Audit Trail Transparan:</strong> Setiap eksekusi otomatis tercatat di log sistem dengan nama pimpinan yang mendelegasikan.</li>
                        </ul>
                    </div>

                </div>

            </div>

            <!-- RIWAYAT / LOG EKSEKUSI AUTO-PENILAIAN -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Riwayat Eksekusi Auto-Penilaian</h3>
                        <p class="text-[11px] text-slate-500">Log transparansi penilaian otomatis yang telah dijalankan sistem</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="text-white text-xs font-bold" style="background-color: #003366;">
                            <tr>
                                <th class="px-4 py-3 text-center w-12 border-b border-blue-900">No.</th>
                                <th class="px-4 py-3 border-b border-blue-900">Waktu Eksekusi</th>
                                <th class="px-4 py-3 border-b border-blue-900">Tanggal Target</th>
                                <th class="px-4 py-3 border-b border-blue-900 text-center">Tipe Eksekusi</th>
                                <th class="px-4 py-3 border-b border-blue-900 text-center">Skor Diberikan</th>
                                <th class="px-4 py-3 border-b border-blue-900 text-center">Total Pegawai</th>
                                <th class="px-4 py-3 border-b border-blue-900">Pemicu (Delegasi)</th>
                                <th class="px-4 py-3 border-b border-blue-900">Ringkasan Pegawai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($logs as $index => $log)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-center font-bold text-slate-400">
                                        {{ $logs->firstItem() + $index }}.
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-slate-800">
                                        {{ $log->executed_at->translatedFormat('d M Y, H:i') }} WITA
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                                        {{ $log->target_date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        @if($log->trigger_type === 'cron_24h')
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-blue-100 text-blue-900 border border-blue-200">
                                                Jam 24:00 (Jadwal)
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                                Eksekusi Langsung
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-black text-emerald-700 text-sm">
                                        {{ $log->score_given }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-800">
                                        {{ $log->total_evaluated }} Pegawai
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-semibold text-slate-700">
                                        {{ $log->triggered_by_name ?? 'Sistem' }}
                                    </td>
                                    <td class="px-4 py-3 max-w-xs text-[11px] text-slate-500 truncate" title="{{ $log->affected_summary }}">
                                        {{ $log->affected_summary ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-slate-400 italic">
                                        Belum ada riwayat eksekusi auto-penilaian yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $logs->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
