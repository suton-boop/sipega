<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-extrabold text-2xl text-sipega-navy leading-none tracking-tighter uppercase">
                        Agenda & e-Kinerja Companion
                    </h2>
                    <span class="px-3 py-1 bg-gradient-to-r from-sipega-navy to-blue-900 text-white text-[10px] font-black uppercase tracking-widest rounded-full shadow-sm">
                        7 Kolom BKN ⚡
                    </span>
                </div>
                <p class="text-[11px] font-bold text-sipega-orange uppercase tracking-[0.25em] mt-1 italic font-sans">
                    Pencatatan Harian • Input Suara • Auto Link Google Drive
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Date Selector Form -->
                <form method="GET" action="{{ route('agenda.index') }}" class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-2xl border border-gray-200 shadow-sm">
                    <span class="text-xs">📅</span>
                    <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="border-0 p-0 text-xs font-bold text-sipega-navy focus:ring-0 cursor-pointer">
                </form>

                <!-- Tombol Kelola RHK -->
                <button type="button" onclick="openModal('rhkModal')" class="bg-white hover:bg-slate-50 text-sipega-navy border border-gray-200 text-xs font-black px-4 py-2 rounded-2xl shadow-sm transition flex items-center gap-1.5">
                    <span>🎯</span> Kelola RHK ({{ $userRhks->count() }})
                </button>

                <!-- Tombol Atur Drive -->
                <button type="button" onclick="openModal('driveModal')" class="bg-white hover:bg-slate-50 {{ $user->drive_folder_link ? 'text-green-700 border-green-200' : 'text-orange-600 border-orange-200' }} border text-xs font-black px-4 py-2 rounded-2xl shadow-sm transition flex items-center gap-1.5">
                    <span>{{ $user->drive_folder_link ? '📁' : '⚠️' }}</span> Google Drive
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl font-bold flex items-center gap-3 shadow-sm text-sm">
                    <span class="bg-emerald-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-black">✓</span> 
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl font-bold flex items-center gap-3 shadow-sm text-sm">
                    <span class="bg-rose-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-black">✕</span> 
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl shadow-sm">
                    <ul class="list-disc pl-5 text-xs font-bold space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- CARD 1: SKOR KESIAPAN e-KINERJA & SUMMARY BANNER -->
            <div class="bg-gradient-to-br from-sipega-navy via-[#00264d] to-slate-900 rounded-[2.5rem] p-6 lg:p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-sipega-orange/15 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase tracking-widest text-sipega-orange">STATUS HARIAN e-KINERJA</span>
                            <span class="text-white/40">•</span>
                            <span class="text-xs text-white/80 font-bold">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-black tracking-tight leading-tight">
                            @if($readinessScore >= 80)
                                Siap Disalin ke e-Kinerja BKN! ✨
                            @elseif($readinessScore >= 50)
                                Sedang Disusun — Masih Ada Kolom Belum Lengkap 📝
                            @else
                                Belum Ada Catatan Kegiatan Hari Ini 🎯
                            @endif
                        </h3>
                        <p class="text-xs text-white/60 max-w-2xl font-medium">
                            Sistem otomatis menyusun 7 format kolom e-Kinerja, mengunggah bukti ke Google Drive, dan menyediakan tombol salin 1-klik untuk pengisian logbook e-Kinerja ASN.
                        </p>
                    </div>

                    <!-- Readiness Score Gauge Widget -->
                    <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md px-6 py-4 rounded-3xl border border-white/10">
                        <div class="text-center">
                            <div class="text-3xl font-black {{ $readinessColor === 'Hijau' ? 'text-emerald-400' : ($readinessColor === 'Kuning' ? 'text-amber-400' : 'text-rose-400') }}">
                                {{ $readinessScore }}%
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-wider text-white/60">Skor Kesiapan</span>
                        </div>
                        <div class="h-10 w-px bg-white/15"></div>
                        <div class="text-xs space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $readinessColor === 'Hijau' ? 'bg-emerald-400' : ($readinessColor === 'Kuning' ? 'bg-amber-400' : 'bg-rose-400') }}"></span>
                                <span class="font-bold">{{ $readinessColor === 'Hijau' ? 'Lengkap & Siap' : ($readinessColor === 'Kuning' ? 'Cek Bukti/Jam' : 'Kosong') }}</span>
                            </div>
                            <div class="text-[10px] text-white/60 font-semibold">
                                {{ $myAgendaToday ? $myAgendaToday->items->count() : 0 }} Kegiatan Tercatat
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Drive Connection Status Strip -->
                <div class="mt-6 pt-5 border-t border-white/10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $user->drive_folder_link ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                        <span class="text-white/70">Folder Drive:</span>
                        @if($user->drive_folder_link)
                            <a href="{{ $user->drive_folder_link }}" target="_blank" class="text-emerald-300 hover:text-emerald-200 underline font-semibold flex items-center gap-1">
                                <span>Terhubung</span> ↗
                            </a>
                        @else
                            <button type="button" onclick="openModal('driveModal')" class="text-amber-300 hover:text-amber-200 underline font-semibold">
                                Belum Disetel (Klik untuk Pasang Link)
                            </button>
                        @endif
                    </div>
                    <div class="text-[11px] text-white/50">
                        API Drive: <span class="text-emerald-300 font-bold">{{ $driveConfigured ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                </div>
            </div>

            <!-- CARD 2: CATAT KEGIATAN CEPAT (TEKS, SUARA 🎙️, & UPLOAD FOTO) -->
            <div class="bg-white rounded-[2.5rem] p-6 lg:p-8 shadow-xl border border-gray-100">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xl font-black text-sipega-navy uppercase tracking-tight flex items-center gap-2">
                                <span>🎙️</span> Catat Kegiatan & Logbook Harian
                            </h4>
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-wider rounded-md border border-emerald-300">
                                Voice Pro v2.2
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 font-medium mt-1">
                            Gunakan tombol suara untuk dikte uraian kegiatan atau ketik manual. Teks yang sudah ada akan otomatis dilanjutkan (tidak terhapus).
                        </p>
                    </div>
                </div>

                <!-- DEDICATED VOICE RECORDER TOOLBAR PANEL -->
                <div class="bg-slate-900 text-white p-4 sm:p-5 rounded-2xl mb-5 shadow-lg border border-slate-800">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <!-- Left Status & Wave Indicator -->
                        <div class="flex items-center gap-3">
                            <div id="voicePulseCircle" class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-lg border border-slate-700 shrink-0">
                                🎙️
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span id="voiceStatusTitle" class="text-xs font-black uppercase tracking-wider text-slate-300">
                                        Panel Rekam Suara (Dikte Otomatis)
                                    </span>
                                    <span id="voiceDurationBadge" class="hidden font-mono text-[10px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-800 px-2 py-0.5 rounded">
                                        00:00
                                    </span>
                                </div>
                                <p id="voiceStatusSubtitle" class="text-xs text-slate-400 mt-0.5">
                                    Klik tombol <b class="text-emerald-400">[Mulai Rekam]</b> untuk mulai bicara. Teks sebelumnya akan aman dilanjutkan.
                                </p>
                            </div>
                        </div>

                        <!-- Right Control Buttons (Play, Pause, Stop, Clear) -->
                        <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto justify-start sm:justify-end">
                            <!-- Mulai / Lanjutkan -->
                            <button type="button" id="btnVoicePlay" onclick="voiceStart()" class="px-4 py-2 rounded-xl text-xs font-black bg-emerald-500 hover:bg-emerald-600 text-white flex items-center gap-1.5 transition shadow cursor-pointer">
                                <span>▶</span>
                                <span id="txtVoicePlay">Mulai Rekam</span>
                            </button>

                            <!-- Jeda (Pause) -->
                            <button type="button" id="btnVoicePause" onclick="voicePause()" disabled class="px-4 py-2 rounded-xl text-xs font-black bg-amber-500 hover:bg-amber-600 text-white disabled:opacity-30 disabled:cursor-not-allowed flex items-center gap-1.5 transition shadow">
                                <span>⏸</span>
                                <span>Jeda</span>
                            </button>

                            <!-- Selesai (Stop) -->
                            <button type="button" id="btnVoiceStop" onclick="voiceStop()" disabled class="px-4 py-2 rounded-xl text-xs font-black bg-rose-600 hover:bg-rose-700 text-white disabled:opacity-30 disabled:cursor-not-allowed flex items-center gap-1.5 transition shadow">
                                <span>⏹</span>
                                <span>Selesai</span>
                            </button>

                            <!-- Bersihkan (Clear) -->
                            <button type="button" onclick="voiceClear()" title="Bersihkan isi kotak uraian" class="px-3 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition cursor-pointer">
                                🧹 Bersihkan
                            </button>
                        </div>
                    </div>
                </div>

                <form action="{{ route('agenda.quick_add') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <input type="hidden" name="date" value="{{ $selectedDate }}">

                    <!-- Voice Input Box & Uraian Kegiatan -->
                    <div class="relative">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-black text-sipega-navy uppercase tracking-wider">
                                Uraian Kegiatan (Kolom 5 e-Kinerja)
                            </label>
                            <span class="text-[11px] text-emerald-600 font-bold flex items-center gap-1">
                                <span>✓</span> Auto-Lanjut Teks Aktif
                            </span>
                        </div>
                        <div class="relative">
                            <textarea 
                                id="voiceInputTextarea"
                                name="plan_description" 
                                required 
                                rows="3" 
                                class="w-full bg-slate-50 border border-gray-200 focus:border-sipega-orange focus:ring-0 rounded-2xl p-4 text-sm font-semibold text-gray-800 placeholder-gray-400 transition"
                                placeholder="Contoh: Mengikuti rapat koordinasi persiapan pendampingan sekolah dan menyusun notulen rapat..."
                            ></textarea>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1.5 italic">
                            💡 Tips Suara: Ucapkan kegiatan Anda, contoh: <span class="text-sipega-orange font-bold">"Dari jam 9 sampai 11 saya melakukan telaah berkas SKP pegawai"</span>. Jam otomatis terisi!
                        </p>
                    </div>

                    <!-- Grid: RHK, Jam Mulai, Jam Selesai -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Dropdown RHK -->
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="text-xs font-black text-sipega-navy uppercase tracking-wider">
                                    RHK Terkait (Kolom 1)
                                </label>
                                <button type="button" onclick="openModal('rhkModal')" class="text-[10px] text-sipega-orange font-bold hover:underline">
                                    + Tambah RHK
                                </button>
                            </div>
                            @if($userRhks->count() > 0)
                                <select name="rhk_text" class="w-full bg-slate-50 border border-gray-200 focus:border-sipega-orange focus:ring-0 rounded-2xl p-3 text-xs font-semibold text-gray-800">
                                    <option value="">-- Pilih Rencana Hasil Kerja --</option>
                                    @foreach($userRhks as $rhk)
                                        <option value="{{ $rhk->rhk_desc }}">
                                            [{{ $rhk->classification }}] {{ \Illuminate\Support\Str::limit($rhk->rhk_desc, 65) }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" name="rhk_text" placeholder="Ketik atau kelola daftar RHK..." class="w-full bg-slate-50 border border-gray-200 focus:border-sipega-orange focus:ring-0 rounded-2xl p-3 text-xs font-semibold text-gray-800">
                            @endif
                        </div>

                        <!-- Jam Mulai -->
                        <div>
                            <label class="block text-xs font-black text-sipega-navy uppercase tracking-wider mb-1.5">
                                Jam Mulai (Kolom 3)
                            </label>
                            <input type="time" id="startTimeInput" name="start_time" value="08:00" class="w-full bg-slate-50 border border-gray-200 focus:border-sipega-orange focus:ring-0 rounded-2xl p-3 text-xs font-bold text-gray-800">
                        </div>

                        <!-- Jam Selesai -->
                        <div>
                            <label class="block text-xs font-black text-sipega-navy uppercase tracking-wider mb-1.5">
                                Jam Selesai (Kolom 4)
                            </label>
                            <input type="time" id="endTimeInput" name="end_time" value="10:00" class="w-full bg-slate-50 border border-gray-200 focus:border-sipega-orange focus:ring-0 rounded-2xl p-3 text-xs font-bold text-gray-800">
                        </div>
                    </div>

                    <!-- Grid: Realisasi / Output & Upload Foto Bukti -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Realisasi Kegiatan -->
                        <div>
                            <label class="block text-xs font-black text-sipega-navy uppercase tracking-wider mb-1.5">
                                Realisasi / Output Kegiatan (Kolom 6)
                            </label>
                            <input 
                                type="text" 
                                name="realization_notes" 
                                placeholder="Contoh: Tersusunnya 1 draf notulen dan daftar hadir kegiatan..." 
                                class="w-full bg-slate-50 border border-gray-200 focus:border-sipega-orange focus:ring-0 rounded-2xl p-3 text-xs font-semibold text-gray-800"
                            >
                        </div>

                        <!-- Upload Foto Bukti Dukung (Direct Google Drive Upload) -->
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="text-xs font-black text-sipega-navy uppercase tracking-wider">
                                    Upload Foto Bukti (Kolom 7 ☁️)
                                </label>
                                <span class="text-[10px] text-emerald-600 font-bold">Auto Watermark & Auto Drive</span>
                            </div>
                            <input 
                                type="file" 
                                name="proof_file" 
                                accept="image/*,.pdf" 
                                class="block w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-slate-100 file:text-sipega-navy hover:file:bg-slate-200 cursor-pointer"
                            >
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-sipega-navy hover:bg-slate-800 text-white font-black text-xs uppercase tracking-wider px-6 py-3.5 rounded-2xl shadow-md transition flex items-center gap-2">
                            <span>⚡</span> Simpan Kegiatan & Siapkan Link Drive
                        </button>
                    </div>
                </form>
            </div>

            <!-- CARD 3: TABEL 7 KOLOM e-KINERJA BKN (DILENGKAPI TOMBOL 1-KLIK COPY) -->
            <div class="bg-white rounded-[2.5rem] p-6 lg:p-8 shadow-xl border border-gray-100">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-xl font-black text-sipega-navy uppercase tracking-tight">
                                📋 Format 7 Kolom e-Kinerja BKN
                            </h4>
                            <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 rounded-full text-[10px] font-black uppercase">
                                Standar Resmi BKN
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 font-medium mt-0.5">
                            Salin satu per satu atau salin seluruh tabel sekaligus untuk ditempel ke aplikasi e-Kinerja.
                        </p>
                    </div>

                    <!-- 1-Click Multi Copy Actions -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <button 
                            type="button" 
                            onclick="copyAllTabular()" 
                            class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black px-4 py-2.5 rounded-2xl shadow-sm transition flex items-center gap-1.5"
                        >
                            <span>📑</span> Salin Tabel (Tab-Separated)
                        </button>

                        <button 
                            type="button" 
                            onclick="copyAllFormattedText()" 
                            class="bg-sipega-navy hover:bg-slate-800 text-white text-xs font-black px-4 py-2.5 rounded-2xl shadow-sm transition flex items-center gap-1.5"
                        >
                            <span>📋</span> Salin Teks Format Lengkap
                        </button>
                    </div>
                </div>

                @if($myAgendaToday && $myAgendaToday->items->count() > 0)
                    <div class="overflow-x-auto rounded-2xl border border-gray-100">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-sipega-navy uppercase text-[10px] font-black tracking-wider border-b border-gray-200">
                                    <th class="p-3 w-8 text-center">No</th>
                                    <th class="p-3 min-w-[180px]">1. RHK</th>
                                    <th class="p-3 min-w-[95px]">2. Tanggal</th>
                                    <th class="p-3 min-w-[75px]">3. Jam Mulai</th>
                                    <th class="p-3 min-w-[75px]">4. Jam Selesai</th>
                                    <th class="p-3 min-w-[200px]">5. Uraian Kegiatan</th>
                                    <th class="p-3 min-w-[180px]">6. Realisasi Kegiatan</th>
                                    <th class="p-3 min-w-[150px]">7. Link Bukti Dukung</th>
                                    <th class="p-3 w-16 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 font-medium">
                                @foreach($myAgendaToday->items as $item)
                                    @php 
                                        $driveFinalLink = $item->drive_link ?: $user->drive_folder_link;
                                        $itemScore = $itemsReadiness[$item->id] ?? 0;
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition group" data-item-id="{{ $item->id }}">
                                        <!-- No -->
                                        <td class="p-3 text-center font-bold text-gray-400">
                                            {{ $loop->iteration }}
                                        </td>

                                        <!-- Kolom 1: RHK -->
                                        <td class="p-3">
                                            <div class="flex items-start justify-between gap-1">
                                                <span class="text-gray-800 font-semibold leading-snug">
                                                    {{ $item->rhk_text ?: '— (Belum dipilih)' }}
                                                </span>
                                                @if($item->rhk_text)
                                                    <button type="button" onclick="copyToClipboard('{{ addslashes($item->rhk_text) }}', 'RHK')" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-sipega-orange p-1" title="Salin RHK">
                                                        📋
                                                    </button>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Kolom 2: Tanggal -->
                                        <td class="p-3 font-semibold text-gray-700 whitespace-nowrap">
                                            <div class="flex items-center justify-between gap-1">
                                                <span>{{ \Carbon\Carbon::parse($selectedDate)->format('Y-m-d') }}</span>
                                                <button type="button" onclick="copyToClipboard('{{ \Carbon\Carbon::parse($selectedDate)->format('Y-m-d') }}', 'Tanggal')" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-sipega-orange p-1" title="Salin Tanggal">
                                                    📋
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Kolom 3: Jam Mulai -->
                                        <td class="p-3 font-semibold text-gray-700 whitespace-nowrap">
                                            <div class="flex items-center justify-between gap-1">
                                                <span>{{ $item->start_time ?: '08:00' }}</span>
                                                <button type="button" onclick="copyToClipboard('{{ $item->start_time ?: '08:00' }}', 'Jam Mulai')" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-sipega-orange p-1" title="Salin Jam Mulai">
                                                    📋
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Kolom 4: Jam Selesai -->
                                        <td class="p-3 font-semibold text-gray-700 whitespace-nowrap">
                                            <div class="flex items-center justify-between gap-1">
                                                <span>{{ $item->end_time ?: '10:00' }}</span>
                                                <button type="button" onclick="copyToClipboard('{{ $item->end_time ?: '10:00' }}', 'Jam Selesai')" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-sipega-orange p-1" title="Salin Jam Selesai">
                                                    📋
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Kolom 5: Uraian Kegiatan -->
                                        <td class="p-3">
                                            <div class="flex items-start justify-between gap-1">
                                                <span class="text-gray-800 font-bold leading-snug">
                                                    {{ $item->plan_description }}
                                                </span>
                                                <button type="button" onclick="copyToClipboard('{{ addslashes($item->plan_description) }}', 'Uraian Kegiatan')" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-sipega-orange p-1" title="Salin Uraian">
                                                    📋
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Kolom 6: Realisasi Kegiatan -->
                                        <td class="p-3">
                                            <div class="flex items-start justify-between gap-1">
                                                <span class="text-gray-700 leading-snug">
                                                    {{ $item->realization_notes ?: ($item->status == 'completed' ? 'Tuntas dilaksanakan' : 'Dalam progres') }}
                                                </span>
                                                <button type="button" onclick="copyToClipboard('{{ addslashes($item->realization_notes ?: ($item->status == 'completed' ? 'Tuntas dilaksanakan' : 'Dalam progres')) }}', 'Realisasi')" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-sipega-orange p-1" title="Salin Realisasi">
                                                    📋
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Kolom 7: Link Bukti Dukung (Google Drive) -->
                                        <td class="p-3">
                                            @if($driveFinalLink)
                                                <div class="flex items-center justify-between gap-1">
                                                    <a href="{{ $driveFinalLink }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline truncate max-w-[130px] inline-block font-semibold" title="{{ $driveFinalLink }}">
                                                        {{ $item->proof_file_path ? '📄 Berkas Bukti' : '📁 Folder Drive' }} ↗
                                                    </a>
                                                    <button type="button" onclick="copyToClipboard('{{ addslashes($driveFinalLink) }}', 'Link Bukti Drive')" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-sipega-orange p-1" title="Salin Link Drive">
                                                        📋
                                                    </button>
                                                </div>
                                            @else
                                                <button type="button" onclick="openModal('driveModal')" class="text-amber-600 hover:underline text-[10px] font-bold">
                                                    + Pasang Link Drive
                                                </button>
                                            @endif
                                        </td>

                                        <!-- Aksi Hapus -->
                                        <td class="p-3 text-center">
                                            <form action="{{ route('agenda.item.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus item kegiatan ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-400 hover:text-rose-600 p-1 font-bold text-xs" title="Hapus Item">
                                                    🗑️
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-slate-50 border-2 border-dashed border-gray-200 rounded-3xl p-10 text-center">
                        <div class="text-3xl mb-2">📝</div>
                        <h5 class="text-sm font-bold text-gray-600">Belum ada kegiatan yang tercatat untuk tanggal ini</h5>
                        <p class="text-xs text-gray-400 mt-1 max-w-md mx-auto">
                            Gunakan kolom <strong>Catat Kegiatan</strong> di atas (bisa via teks atau suara 🎙️) untuk mulai menyusun entri 7 kolom e-Kinerja Anda.
                        </p>
                    </div>
                @endif
            </div>

            <!-- CARD 4: WORKFLOW SIKLUS SORE SIPEGA (EVALUASI & WATERMARK RESMI SIPEGA) -->
            @if($myAgendaToday && !$myAgendaToday->realization_submitted_at)
                @php 
                    $hour = (int) now('Asia/Makassar')->format('H'); 
                    $isFlexible = \App\Models\Setting::get('is_realization_open_anytime') === '1';
                @endphp
                <div class="bg-sipega-navy rounded-[2.5rem] p-6 lg:p-8 text-white shadow-xl relative overflow-hidden">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h4 class="text-xl font-black uppercase tracking-tight">Evaluasi Sore & Validasi SIPEGA</h4>
                            <p class="text-xs text-white/50 font-medium">Tetapkan status penyelesaian akhir seluruh kegiatan hari ini.</p>
                        </div>
                        @if($isFlexible)
                            <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 rounded-full text-[10px] font-black uppercase">
                                Mode Terbuka Bebas
                            </span>
                        @endif
                    </div>

                    @if($isFlexible || ($hour >= 15 && $hour < 17))
                        <form action="{{ route('agenda.realization', $myAgendaToday->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <div class="space-y-4">
                                @foreach($myAgendaToday->items as $item)
                                    <div class="bg-white/5 p-5 rounded-2xl border border-white/10 space-y-3">
                                        <div class="flex justify-between items-start gap-4">
                                            <p class="font-bold text-sm text-white">{{ $item->plan_description }}</p>
                                            <div class="flex gap-2">
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="items[{{ $item->id }}][status]" value="completed" {{ $item->status === 'completed' ? 'checked' : '' }} class="hidden peer">
                                                    <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase border border-white/20 bg-white/5 text-white/60 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:border-emerald-500 block transition">Selesai</span>
                                                </label>
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="items[{{ $item->id }}][status]" value="progress" {{ $item->status === 'progress' ? 'checked' : '' }} class="hidden peer">
                                                    <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase border border-white/20 bg-white/5 text-white/60 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500 block transition">Progres</span>
                                                </label>
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="items[{{ $item->id }}][status]" value="changed" {{ $item->status === 'changed' ? 'checked' : '' }} class="hidden peer">
                                                    <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase border border-white/20 bg-white/5 text-white/60 peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500 block transition">Berubah</span>
                                                </label>
                                            </div>
                                        </div>
                                        <input type="text" name="items[{{ $item->id }}][notes]" value="{{ $item->realization_notes }}" class="w-full bg-white/10 border-0 rounded-xl text-xs text-white placeholder-white/30 p-2.5 font-medium" placeholder="Catatan capaian / kendala...">
                                    </div>
                                @endforeach
                            </div>

                            <div class="bg-white/5 p-5 rounded-2xl border border-white/10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-white/80">Lampiran Bukti Sore (Opsional)</label>
                                    <span class="text-[10px] text-white/40">Otomatis diunggah ke Google Drive & diberi stempel watermark SIPEGA</span>
                                </div>
                                <input type="file" name="proof_file" accept="image/*,.pdf" class="text-xs text-white/60 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-sipega-orange file:text-white hover:file:bg-orange-600 cursor-pointer">
                            </div>

                            <button type="submit" class="bg-sipega-orange hover:bg-orange-600 text-white font-black py-4 rounded-2xl w-full shadow-lg transition uppercase tracking-wider text-xs">
                                Kirim Evaluasi Kinerja Sore
                            </button>
                        </form>
                    @else
                        <div class="bg-white/5 border border-dashed border-white/10 rounded-2xl p-6 text-center text-xs text-white/50">
                            Evaluasi sore terbuka otomatis pukul 15:00 - 17:00 WITA. Anda tetap bisa mencatat kegiatan melalui tombol di atas.
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>

    <!-- MODAL 1: KELOLA RHK PEGAWAI -->
    <div id="rhkModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm p-4 flex items-center justify-center">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5 animate-scale-up">
            <div class="flex justify-between items-center border-b border-gray-100 pb-4">
                <div>
                    <h5 class="font-black text-lg text-sipega-navy uppercase tracking-tight">Kelola RHK (SKP Anda)</h5>
                    <p class="text-xs text-gray-400">Daftar Rencana Hasil Kerja untuk Kolom 1 e-Kinerja</p>
                </div>
                <button type="button" onclick="closeModal('rhkModal')" class="text-gray-400 hover:text-gray-600 text-lg font-black">✕</button>
            </div>

            <!-- Form Tambah RHK -->
            <form action="{{ route('agenda.rhk.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-black text-sipega-navy uppercase mb-1">Rencana Hasil Kerja (RHK)</label>
                    <textarea name="rhk_desc" required rows="2" placeholder="Tempel uraian RHK dari SKP e-Kinerja Anda..." class="w-full bg-slate-50 border border-gray-200 focus:border-sipega-orange focus:ring-0 rounded-2xl p-3 text-xs font-semibold"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-1">Klasifikasi</label>
                        <select name="classification" class="w-full bg-slate-50 border border-gray-200 rounded-xl p-2 text-xs font-semibold">
                            <option value="Utama">Utama</option>
                            <option value="Tambahan">Tambahan</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-sipega-navy hover:bg-slate-800 text-white font-black text-xs uppercase py-2.5 rounded-xl shadow transition">
                            + Tambah RHK
                        </button>
                    </div>
                </div>
            </form>

            <!-- List Existing RHKs -->
            <div class="border-t border-gray-100 pt-4">
                <label class="block text-xs font-black text-sipega-navy uppercase mb-2">Daftar RHK Terdaftar ({{ $userRhks->count() }})</label>
                <div class="max-h-56 overflow-y-auto space-y-2 pr-1">
                    @forelse($userRhks as $rhk)
                        <div class="p-3 bg-slate-50 rounded-xl border border-gray-100 flex justify-between items-start gap-2">
                            <div>
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-[9px] font-black uppercase">{{ $rhk->classification }}</span>
                                <p class="text-xs font-bold text-gray-800 mt-1 leading-snug">{{ $rhk->rhk_desc }}</p>
                            </div>
                            <form action="{{ route('agenda.rhk.destroy', $rhk->id) }}" method="POST" onsubmit="return confirm('Hapus RHK ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-600 font-bold text-xs p-1" title="Hapus">✕</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-4">Belum ada RHK tersimpan. Tambahkan dari SKP e-Kinerja Anda di atas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: PANDUAN & ATUR LINK GOOGLE DRIVE -->
    <div id="driveModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm p-4 flex items-center justify-center">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5 animate-scale-up">
            <div class="flex justify-between items-center border-b border-gray-100 pb-4">
                <div>
                    <h5 class="font-black text-lg text-sipega-navy uppercase tracking-tight">Pengaturan Google Drive</h5>
                    <p class="text-xs text-gray-400">Sinkronisasi Bukti Dukung ke Google Drive Pegawai</p>
                </div>
                <button type="button" onclick="closeModal('driveModal')" class="text-gray-400 hover:text-gray-600 text-lg font-black">✕</button>
            </div>

            <!-- Step by step box -->
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-xs text-blue-900 space-y-2">
                <p class="font-black uppercase tracking-wider text-[10px] text-blue-700">Cara Menghubungkan Folder Google Drive Anda:</p>
                <ol class="list-decimal pl-4 space-y-1 text-xs">
                    <li>Buka Google Drive Anda, buat atau buka folder khusus (misal: <em>"Bukti e-Kinerja 2026"</em>).</li>
                    <li>Klik kanan folder > pilih <strong>Bagikan (Share)</strong>.</li>
                    <li>
                        Masukkan email robot SIPEGA berikut sebagai <strong>Editor</strong>:
                        <div class="flex items-center gap-1.5 mt-1 bg-white p-2 rounded-xl border border-blue-200">
                            <code class="text-[10px] font-mono font-bold text-gray-800 break-all select-all">sipega-drive@sipega-bpmp-kaltim.iam.gserviceaccount.com</code>
                            <button type="button" onclick="copyToClipboard('sipega-drive@sipega-bpmp-kaltim.iam.gserviceaccount.com', 'Email Service Account')" class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">
                                Salin
                            </button>
                        </div>
                    </li>
                    <li>Salin link folder Google Drive Anda dan tempel di formulir bawah ini.</li>
                </ol>
            </div>

            <!-- Opsi 1: Buat Folder Otomatis -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-4 text-xs space-y-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">⚡</span>
                    <div>
                        <p class="font-black text-sipega-navy uppercase text-[11px]">Buat Folder Otomatis (Praktis)</p>
                        <p class="text-[10px] text-gray-500">Sistem otomatis membuat folder khusus Anda di Drive Induk BPMP Kaltim.</p>
                    </div>
                </div>
                <form action="{{ route('agenda.generate_my_folder') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black text-xs uppercase py-2.5 rounded-xl shadow transition flex items-center justify-center gap-1.5">
                        <span>📁</span> Buat Folder Drive Saya Sekarang
                    </button>
                </form>
            </div>

            <div class="flex items-center gap-3">
                <div class="h-px flex-grow bg-gray-200"></div>
                <span class="text-[10px] font-bold text-gray-400 uppercase">Atau Gunakan Link Manual</span>
                <div class="h-px flex-grow bg-gray-200"></div>
            </div>

            <!-- Form Simpan Link Folder Manual -->
            <form action="{{ route('agenda.drive_folder.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-black text-sipega-navy uppercase mb-1">Tautan / URL Folder Google Drive Pegawai</label>
                    <input 
                        type="url" 
                        name="drive_folder_link" 
                        value="{{ $user->drive_folder_link }}" 
                        required 
                        placeholder="https://drive.google.com/drive/folders/..." 
                        class="w-full bg-slate-50 border border-gray-200 focus:border-sipega-orange focus:ring-0 rounded-2xl p-3 text-xs font-semibold text-gray-800"
                    >
                </div>
                <button type="submit" class="w-full bg-sipega-navy hover:bg-slate-800 text-white font-black text-xs uppercase py-3 rounded-2xl shadow transition">
                    Simpan Tautan Google Drive Manual
                </button>
            </form>
        </div>
    </div>

    <!-- TOAST NOTIFICATION CONTAINER -->
    <div id="toastNotification" class="fixed bottom-6 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-gray-900 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700">
            <span class="text-emerald-400 font-bold text-base">✓</span>
            <span id="toastMessage" class="text-xs font-bold">Tersalin ke Clipboard!</span>
        </div>
    </div>

    <!-- JAVASCRIPT: WEB SPEECH API, COPY UTILITIES, & MODAL TOGGLER -->
    <script>
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Toast notification
        function showToast(message) {
            const toast = document.getElementById('toastNotification');
            const toastMsg = document.getElementById('toastMessage');
            toastMsg.textContent = message;
            toast.classList.remove('translate-y-24', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-24', 'opacity-0');
            }, 2500);
        }

        // Copy single text to clipboard
        function copyToClipboard(text, label) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(() => {
                showToast(`${label || 'Teks'} berhasil disalin!`);
            }).catch(err => {
                console.error('Clipboard copy error: ', err);
            });
        }

        // Copy All Items in Tabular Format (Ready for direct paste into Excel / e-Kinerja Table)
        function copyAllTabular() {
            const rows = document.querySelectorAll('tbody tr[data-item-id]');
            if (!rows || rows.length === 0) {
                showToast('Tidak ada data kegiatan untuk disalin.');
                return;
            }

            let tsvText = "RHK\tTanggal\tJam Mulai\tJam Selesai\tUraian Kegiatan\tRealisasi Kegiatan\tLink Bukti Dukung\n";
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 8) {
                    const rhk = cells[1].innerText.replace(/\s+/g, ' ').replace('— (Belum dipilih)', '').trim();
                    const tanggal = cells[2].innerText.replace(/\s+/g, ' ').trim();
                    const jamMulai = cells[3].innerText.replace(/\s+/g, ' ').trim();
                    const jamSelesai = cells[4].innerText.replace(/\s+/g, ' ').trim();
                    const uraian = cells[5].innerText.replace(/\s+/g, ' ').trim();
                    const realisasi = cells[6].innerText.replace(/\s+/g, ' ').trim();
                    
                    const linkElem = cells[7].querySelector('a');
                    const link = linkElem ? linkElem.href : '';

                    tsvText += `${rhk}\t${tanggal}\t${jamMulai}\t${jamSelesai}\t${uraian}\t${realisasi}\t${link}\n`;
                }
            });

            navigator.clipboard.writeText(tsvText).then(() => {
                showToast('Seluruh tabel (Format Tab) berhasil disalin! Siap ditempel ke e-Kinerja.');
            });
        }

        // Copy All Items in Formatted Multiline Text
        function copyAllFormattedText() {
            const rows = document.querySelectorAll('tbody tr[data-item-id]');
            if (!rows || rows.length === 0) {
                showToast('Tidak ada data kegiatan untuk disalin.');
                return;
            }

            let fullText = `LAPORAN e-KINERJA HARIAN (${document.querySelector('input[type="date"]').value})\n=========================================\n\n`;
            let count = 1;
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 8) {
                    const rhk = cells[1].innerText.replace(/\s+/g, ' ').replace('— (Belum dipilih)', '').trim();
                    const tanggal = cells[2].innerText.replace(/\s+/g, ' ').trim();
                    const jamMulai = cells[3].innerText.replace(/\s+/g, ' ').trim();
                    const jamSelesai = cells[4].innerText.replace(/\s+/g, ' ').trim();
                    const uraian = cells[5].innerText.replace(/\s+/g, ' ').trim();
                    const realisasi = cells[6].innerText.replace(/\s+/g, ' ').trim();
                    const linkElem = cells[7].querySelector('a');
                    const link = linkElem ? linkElem.href : '-';

                    fullText += `${count}. ${uraian}\n`;
                    if (rhk) fullText += `   • RHK: ${rhk}\n`;
                    fullText += `   • Waktu: ${jamMulai} - ${jamSelesai} WITA\n`;
                    fullText += `   • Realisasi: ${realisasi}\n`;
                    fullText += `   • Link Bukti: ${link}\n\n`;
                    count++;
                }
            });

            navigator.clipboard.writeText(fullText).then(() => {
                showToast('Format teks lengkap berhasil disalin!');
            });
        }

        // --- SISTEM PEREKAMAN SUARA PROFESIONAL (PLAY, PAUSE, STOP & AUTO-CONTINUE) ---
        let recognition = null;
        let voiceState = 'idle'; // 'idle' | 'recording' | 'paused'
        let baseTextBeforeSession = '';
        let timerInterval = null;
        let timerSeconds = 0;
        let isManualStopping = false;

        function getSpeechRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                alert('Browser Anda belum mendukung Web Speech API. Silakan gunakan Google Chrome atau Microsoft Edge terbaru.');
                return null;
            }

            if (recognition) return recognition;

            recognition = new SpeechRecognition();
            recognition.lang = 'id-ID'; // Bahasa Indonesia
            recognition.continuous = true;
            recognition.interimResults = true;

            recognition.onstart = function() {
                console.log('Voice engine: ACTIVE');
            };

            recognition.onresult = function(event) {
                let sessionFinal = '';
                let sessionInterim = '';

                // Kumpulkan semua hasil dari sesi pengenalan suara saat ini
                for (let i = 0; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        sessionFinal += event.results[i][0].transcript + ' ';
                    } else {
                        sessionInterim += event.results[i][0].transcript;
                    }
                }

                const textarea = document.getElementById('voiceInputTextarea');
                // Gabungkan teks lama (base) + kata baru yang sudah final + kata yang sedang diucapkan (interim)
                const fullText = (baseTextBeforeSession + sessionFinal + sessionInterim).trim();
                textarea.value = fullText;

                // Smart AI/Regex parser mendeteksi jam secara otomatis
                parseSpokenTime(fullText);
            };

            recognition.onerror = function(event) {
                console.warn('Voice event notice: ', event.error);
                if (event.error === 'not-allowed') {
                    showToast('Izin mikrofon belum diberikan pada browser.');
                    voiceStop();
                }
            };

            recognition.onend = function() {
                console.log('Voice engine: onend (voiceState=' + voiceState + ')');
                // Jika browser memutus koneksi karena jeda bicara sejenak, tapi user masih ingin merekam:
                if (voiceState === 'recording' && !isManualStopping) {
                    const textarea = document.getElementById('voiceInputTextarea');
                    const currentVal = textarea.value.trim();
                    baseTextBeforeSession = currentVal ? currentVal + ' ' : '';

                    setTimeout(() => {
                        if (voiceState === 'recording' && !isManualStopping) {
                            try {
                                recognition.start();
                                console.log('Voice auto-restarted cleanly. Teks lama tetap utuh.');
                            } catch (e) {
                                console.warn('Voice restart notice: ', e);
                            }
                        }
                    }, 250);
                }
            };

            return recognition;
        }

        // 1. PLAY / MULAI / LANJUTKAN
        function voiceStart() {
            const rec = getSpeechRecognition();
            if (!rec) return;

            isManualStopping = false;
            const textarea = document.getElementById('voiceInputTextarea');

            // KUNCI: Kunci teks yang sudah ada di textarea agar tidak pernah terhapus
            const currentVal = textarea.value.trim();
            baseTextBeforeSession = currentVal ? currentVal + ' ' : '';

            if (voiceState === 'idle') {
                timerSeconds = 0;
            }

            voiceState = 'recording';

            try {
                rec.start();
            } catch (e) {
                console.log('Voice already running: ', e);
            }

            // Jalankan Timer Durasi
            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                timerSeconds++;
                const mins = String(Math.floor(timerSeconds / 60)).padStart(2, '0');
                const secs = String(timerSeconds % 60).padStart(2, '0');
                const badge = document.getElementById('voiceDurationBadge');
                if (badge) badge.textContent = `${mins}:${secs}`;
            }, 1000);

            // Update Tampilan UI Panel
            const circle = document.getElementById('voicePulseCircle');
            if (circle) circle.className = 'w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-lg border border-rose-500 shrink-0 animate-pulse';

            const title = document.getElementById('voiceStatusTitle');
            if (title) {
                title.className = 'text-xs font-black uppercase tracking-wider text-rose-400';
                title.textContent = '🔴 Sedang Merekam Suara (Mic Aktif)';
            }

            const sub = document.getElementById('voiceStatusSubtitle');
            if (sub) sub.innerHTML = '<span class="text-rose-200 font-semibold">Silakan bicara bebas. Jeda bicara tidak akan menghentikan rekaman. Teks otomatis disambung!</span>';

            const badge = document.getElementById('voiceDurationBadge');
            if (badge) badge.classList.remove('hidden');

            // Update Tombol Kontrol
            document.getElementById('btnVoicePlay').disabled = true;
            document.getElementById('btnVoicePlay').classList.add('opacity-40', 'cursor-not-allowed');

            document.getElementById('btnVoicePause').disabled = false;
            document.getElementById('btnVoicePause').classList.remove('opacity-30', 'cursor-not-allowed');

            document.getElementById('btnVoiceStop').disabled = false;
            document.getElementById('btnVoiceStop').classList.remove('opacity-30', 'cursor-not-allowed');
        }

        // 2. PAUSE / JEDA
        function voicePause() {
            if (voiceState !== 'recording') return;

            isManualStopping = true;
            voiceState = 'paused';
            clearInterval(timerInterval);

            if (recognition) {
                try { recognition.stop(); } catch(e) {}
            }

            // Snapshot teks saat ini
            const textarea = document.getElementById('voiceInputTextarea');
            const currentVal = textarea.value.trim();
            baseTextBeforeSession = currentVal ? currentVal + ' ' : '';

            // Update UI Panel
            const circle = document.getElementById('voicePulseCircle');
            if (circle) circle.className = 'w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg border border-amber-400 shrink-0';

            const title = document.getElementById('voiceStatusTitle');
            if (title) {
                title.className = 'text-xs font-black uppercase tracking-wider text-amber-400';
                title.textContent = '⏸️ Perekaman Dijeda';
            }

            const sub = document.getElementById('voiceStatusSubtitle');
            if (sub) sub.innerHTML = '<span class="text-amber-200">Perekaman dijeda. Anda dapat mengetik manual atau klik <b class="text-white underline">[Lanjutkan]</b> untuk menambah kata baru.</span>';

            // Tombol
            document.getElementById('btnVoicePlay').disabled = false;
            document.getElementById('btnVoicePlay').classList.remove('opacity-40', 'cursor-not-allowed');
            document.getElementById('txtVoicePlay').textContent = 'Lanjutkan ▶';

            document.getElementById('btnVoicePause').disabled = true;
            document.getElementById('btnVoicePause').classList.add('opacity-30', 'cursor-not-allowed');
        }

        // 3. STOP / SELESAI
        function voiceStop() {
            isManualStopping = true;
            voiceState = 'idle';
            clearInterval(timerInterval);

            if (recognition) {
                try { recognition.stop(); } catch(e) {}
            }

            // Reset UI Panel
            const circle = document.getElementById('voicePulseCircle');
            if (circle) circle.className = 'w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-lg border border-slate-700 shrink-0';

            const title = document.getElementById('voiceStatusTitle');
            if (title) {
                title.className = 'text-xs font-black uppercase tracking-wider text-slate-300';
                title.textContent = 'Panel Rekam Suara (Dikte Otomatis)';
            }

            const sub = document.getElementById('voiceStatusSubtitle');
            if (sub) sub.innerHTML = 'Klik tombol <b class="text-emerald-400">[Mulai Rekam]</b> untuk mulai bicara. Teks sebelumnya akan aman dilanjutkan.';

            const badge = document.getElementById('voiceDurationBadge');
            if (badge) badge.classList.add('hidden');

            // Reset Tombol
            document.getElementById('btnVoicePlay').disabled = false;
            document.getElementById('btnVoicePlay').classList.remove('opacity-40', 'cursor-not-allowed');
            document.getElementById('txtVoicePlay').textContent = 'Mulai Rekam';

            document.getElementById('btnVoicePause').disabled = true;
            document.getElementById('btnVoicePause').classList.add('opacity-30', 'cursor-not-allowed');

            document.getElementById('btnVoiceStop').disabled = true;
            document.getElementById('btnVoiceStop').classList.add('opacity-30', 'cursor-not-allowed');

            showToast('Perekaman suara selesai! Teks berhasil dicatat & disimpan di kotak.');
        }

        // 4. CLEAR / BERSIHKAN
        function voiceClear() {
            if (confirm('Kosongkan teks uraian kegiatan?')) {
                voiceStop();
                document.getElementById('voiceInputTextarea').value = '';
                baseTextBeforeSession = '';
                showToast('Teks uraian kegiatan berhasil dikosongkan.');
            }
        }

        // Smart parser untuk mendeteksi "jam 8 sampai jam 10", "pukul 09.00 hingga 11.30", dll.
        function parseSpokenTime(text) {
            const lower = text.toLowerCase();
            // Regex mencari pola waktu: misal "jam 8 sampai jam 10", "pukul 09:30 sampai 11:00"
            const pattern = /(?:dari\s+)?(?:jam|pukul)\s*(\d{1,2}(?:[\.:]\d{2})?)\s*(?:sampai|hingga|-)\s*(?:jam|pukul)?\s*(\d{1,2}(?:[\.:]\d{2})?)/i;
            const match = lower.match(pattern);
            if (match) {
                const formatTime = (t) => {
                    t = t.replace('.', ':');
                    if (!t.includes(':')) {
                        let num = parseInt(t);
                        return (num < 10 ? '0' + num : num) + ':00';
                    }
                    const parts = t.split(':');
                    let h = parseInt(parts[0]);
                    return (h < 10 ? '0' + h : h) + ':' + parts[1];
                };

                const start = formatTime(match[1]);
                const end = formatTime(match[2]);

                if (start && document.getElementById('startTimeInput')) {
                    document.getElementById('startTimeInput').value = start;
                }
                if (end && document.getElementById('endTimeInput')) {
                    document.getElementById('endTimeInput').value = end;
                }
                showToast(`Waktu otomatis terdeteksi: ${start} - ${end}`);
            }
        }
    </script>
</x-app-layout>
