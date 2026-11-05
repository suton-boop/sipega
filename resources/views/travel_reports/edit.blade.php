<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Edit Laporan Perjalanan Dinas ✏️
                </h2>
                <p class="text-[10px] font-black text-sipega-orange uppercase tracking-[0.3em] mt-1">
                    {{ $report->employee_name }} &bull; {{ $report->st_number }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="focusVoiceToolbar()" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black py-3 px-5 rounded-2xl shadow-xl transition-all hover:-translate-y-0.5 flex items-center gap-2 uppercase text-[10px] tracking-widest">
                    <span>🎙️</span> Input Suara
                </button>
                <a href="{{ route('travel-reports.show', $report->id) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-black py-3 px-6 rounded-2xl transition-all flex items-center gap-2 uppercase text-[10px] tracking-widest">
                    <span>⬅️</span> Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
            <div class="mb-6 p-4 bg-red-500 text-white rounded-2xl shadow-lg">
                <div class="font-bold text-sm mb-1">Perhatian! Terdapat kesalahan input:</div>
                <ul class="list-disc list-inside text-xs">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('travel-reports.update', $report->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- PANEL MENU INPUT DENGAN SUARA (VOICE TO TEXT) -->
                <div id="voiceToolbarPanel" class="bg-slate-900 text-white p-5 md:p-6 rounded-3xl shadow-xl border border-slate-800 space-y-4 transition-all">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        <!-- Left info & visualizer -->
                        <div class="flex items-center gap-3.5">
                            <div id="globalVoicePulse" class="w-12 h-12 rounded-2xl bg-slate-800 flex items-center justify-center text-2xl border border-slate-700 shrink-0 transition-all">
                                🎙️
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span id="voiceStatusTitle" class="text-sm font-black uppercase tracking-wider text-slate-200">
                                        Input Laporan Dengan Suara (Voice to Text)
                                    </span>
                                    <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 text-[10px] font-black uppercase tracking-wider rounded-md border border-emerald-500/30">
                                        id-ID (Bahasa Indonesia)
                                    </span>
                                    <span id="voiceDurationBadge" class="hidden font-mono text-[11px] font-bold bg-rose-950 text-rose-300 border border-rose-800 px-2.5 py-0.5 rounded-full animate-pulse">
                                        🔴 00:00
                                    </span>
                                </div>
                                <p id="voiceStatusSubtitle" class="text-xs text-slate-400 mt-1">
                                    Pilih kolom tujuan lalu klik <b class="text-emerald-400 font-bold">[Mulai Dikte]</b>, atau klik tombol <b class="text-amber-300">🎙️ Dikte</b> pada masing-masing isian form di bawah.
                                </p>
                            </div>
                        </div>

                        <!-- Right Controls -->
                        <div class="flex items-center gap-2 flex-wrap w-full lg:w-auto justify-start lg:justify-end">
                            <!-- Selector Target Field -->
                            <div class="flex items-center gap-2 bg-slate-800 px-3 py-2 rounded-xl border border-slate-700 text-xs font-bold">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider shrink-0">Kolom Target:</span>
                                <select id="voiceTargetSelect" onchange="changeVoiceTarget(this.value)" class="bg-slate-900 text-white text-xs font-bold rounded-lg border border-slate-700 px-2.5 py-1 focus:ring-sipega-orange focus:border-sipega-orange cursor-pointer">
                                    <option value="description">4. Deskripsi / Hasil Lengkap</option>
                                    <option value="purpose">3. Tujuan Kegiatan</option>
                                    <option value="output">3. Output Kegiatan</option>
                                    <option value="target_audience">3. Sasaran Kegiatan</option>
                                    <option value="conclusion">4. Penutup / Rekomendasi</option>
                                    <option value="activity_name">3. Nama Kegiatan</option>
                                    <option value="location">3. Tempat Kegiatan</option>
                                </select>
                            </div>

                            <!-- Start / Record -->
                            <button type="button" id="btnVoicePlay" onclick="voiceStart()" class="px-4 py-2.5 rounded-xl text-xs font-black bg-emerald-500 hover:bg-emerald-600 text-white flex items-center gap-1.5 transition shadow cursor-pointer">
                                <span>▶</span>
                                <span id="txtVoicePlay">Mulai Dikte</span>
                            </button>

                            <!-- Pause -->
                            <button type="button" id="btnVoicePause" onclick="voicePause()" disabled class="px-3.5 py-2.5 rounded-xl text-xs font-black bg-amber-500 hover:bg-amber-600 text-white disabled:opacity-30 disabled:cursor-not-allowed flex items-center gap-1.5 transition shadow">
                                <span>⏸</span>
                                <span>Jeda</span>
                            </button>

                            <!-- Stop -->
                            <button type="button" id="btnVoiceStop" onclick="voiceStop()" disabled class="px-3.5 py-2.5 rounded-xl text-xs font-black bg-rose-600 hover:bg-rose-700 text-white disabled:opacity-30 disabled:cursor-not-allowed flex items-center gap-1.5 transition shadow">
                                <span>⏹</span>
                                <span>Selesai</span>
                            </button>

                            <!-- Clear -->
                            <button type="button" onclick="voiceClearCurrentTarget()" title="Kosongkan isi teks kolom yang sedang dipilih" class="px-3 py-2.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition cursor-pointer">
                                🧹 Bersihkan
                            </button>
                        </div>
                    </div>

                    <!-- Live Transcript & Tips Box -->
                    <div id="voiceLiveBox" class="hidden p-3.5 bg-slate-800/90 rounded-2xl border border-slate-700/80 text-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between text-[11px] text-slate-400 mb-1.5 gap-1">
                            <span class="flex items-center gap-1.5 text-emerald-400 font-bold">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                Transkrip Langsung (Mengisi: <span id="voiceCurrentTargetLabel" class="text-white font-mono underline">4. Deskripsi / Hasil Lengkap</span>)
                            </span>
                            <span class="text-[10px] text-slate-400">
                                💡 Katakan <b class="text-slate-300">"titik"</b>, <b class="text-slate-300">"koma"</b>, <b class="text-slate-300">"baris baru"</b> untuk format teks otomatis
                            </span>
                        </div>
                        <p id="voiceLiveTranscript" class="text-slate-200 font-mono italic text-xs leading-relaxed bg-slate-900/60 p-2.5 rounded-xl border border-slate-800">
                            Menunggu suara Anda...
                        </p>
                    </div>
                </div>

                <!-- KARTU 1: HUBUNGKAN DENGAN SURAT TUGAS -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs">1</span>
                        <div>
                            <h3 class="text-base font-extrabold text-sipega-navy uppercase">Hubungkan Dengan Surat Tugas</h3>
                            <p class="text-xs text-gray-400">Pilih Surat Tugas terdaftar.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase text-gray-500 mb-2">Pilih Surat Tugas Disetujui (Approved)</label>
                        <select name="letter_id" id="letter_select" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                            <option value="">-- Tanpa Tautan Surat Tugas --</option>
                            @foreach($letters as $let)
                                <option value="{{ $let->id }}" {{ old('letter_id', $report->letter_id) == $let->id ? 'selected' : '' }}>
                                    [{{ $let->number ?? 'Tanpa Nomor' }}] {{ $let->title }} ({{ $let->date_start ? $let->date_start->format('d/m/Y') : '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- KARTU 2: IDENTITAS PELAKSANA -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs">2</span>
                        <div>
                            <h3 class="text-base font-extrabold text-sipega-navy uppercase">Identitas Pelaksana</h3>
                            <p class="text-xs text-gray-400">Informasi pegawai pelaksana perjalanan dinas.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Nama Lengkap & Gelar *</label>
                            <input type="text" name="employee_name" value="{{ old('employee_name', $report->employee_name) }}" required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">NIP</label>
                            <input type="text" name="employee_nip" value="{{ old('employee_nip', $report->employee_nip) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Jabatan</label>
                            <input type="text" name="employee_position" value="{{ old('employee_position', $report->employee_position) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Nomor Surat Tugas *</label>
                            <input type="text" name="st_number" value="{{ old('st_number', $report->st_number) }}" required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                        </div>
                    </div>
                </div>

                <!-- KARTU 3: KEGIATAN -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs">3</span>
                        <div>
                            <h3 class="text-base font-extrabold text-sipega-navy uppercase">Rincian Kegiatan</h3>
                            <p class="text-xs text-gray-400">Deskripsi kegiatan, waktu, tempat, dan sasaran.</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Nama Kegiatan *</label>
                                <button type="button" onclick="startVoiceForField('activity_name')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1 bg-slate-100 hover:bg-emerald-50 px-2.5 py-0.5 rounded-lg transition border border-slate-200" data-field="activity_name" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte</span>
                                </button>
                            </div>
                            <input type="text" name="activity_name" id="activity_name" value="{{ old('activity_name', $report->activity_name) }}" required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3 transition-all">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $report->start_date ? $report->start_date->format('Y-m-d') : '') }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tanggal Selesai</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $report->end_date ? $report->end_date->format('Y-m-d') : '') }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Teks Tanggal Kustom</label>
                                <input type="text" name="date_range_text" id="date_range_text" value="{{ old('date_range_text', $report->date_range_text) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Tempat Kegiatan *</label>
                                <button type="button" onclick="startVoiceForField('location')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1 bg-slate-100 hover:bg-emerald-50 px-2.5 py-0.5 rounded-lg transition border border-slate-200" data-field="location" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte</span>
                                </button>
                            </div>
                            <textarea name="location" id="location" rows="2" required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3 transition-all">{{ old('location', $report->location) }}</textarea>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Tujuan Kegiatan</label>
                                <button type="button" onclick="startVoiceForField('purpose')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1 bg-slate-100 hover:bg-emerald-50 px-2.5 py-0.5 rounded-lg transition border border-slate-200" data-field="purpose" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte</span>
                                </button>
                            </div>
                            <textarea name="purpose" id="purpose" rows="4" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3 transition-all">{{ old('purpose', $report->purpose) }}</textarea>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Sasaran Kegiatan</label>
                                <button type="button" onclick="startVoiceForField('target_audience')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1 bg-slate-100 hover:bg-emerald-50 px-2.5 py-0.5 rounded-lg transition border border-slate-200" data-field="target_audience" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte</span>
                                </button>
                            </div>
                            <input type="text" name="target_audience" id="target_audience" value="{{ old('target_audience', $report->target_audience) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3 transition-all">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Output Kegiatan</label>
                                <button type="button" onclick="startVoiceForField('output')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1 bg-slate-100 hover:bg-emerald-50 px-2.5 py-0.5 rounded-lg transition border border-slate-200" data-field="output" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte</span>
                                </button>
                            </div>
                            <textarea name="output" id="output" rows="4" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3 transition-all">{{ old('output', $report->output) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- KARTU 4: DESKRIPSI & HASIL KEGIATAN -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs">4</span>
                        <div>
                            <h3 class="text-base font-extrabold text-sipega-navy uppercase">Deskripsi & Pembahasan Hasil</h3>
                            <p class="text-xs text-gray-400">Narasi lengkap pelaksanaan dan temuan lapangan.</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Deskripsi / Hasil Pelaksanaan Lengkap *</label>
                                <button type="button" onclick="startVoiceForField('description')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1.5 bg-slate-100 hover:bg-emerald-50 px-3 py-1 rounded-lg transition border border-slate-200" data-field="description" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte Narasi</span>
                                </button>
                            </div>
                            <textarea name="description" id="description" rows="14" required class="w-full text-xs font-medium rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-4 leading-relaxed transition-all">{{ old('description', $report->description) }}</textarea>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Penutup / Kesimpulan & Rekomendasi</label>
                                <button type="button" onclick="startVoiceForField('conclusion')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1.5 bg-slate-100 hover:bg-emerald-50 px-3 py-1 rounded-lg transition border border-slate-200" data-field="conclusion" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte Kesimpulan</span>
                                </button>
                            </div>
                            <textarea name="conclusion" id="conclusion" rows="5" class="w-full text-xs font-medium rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-4 leading-relaxed transition-all">{{ old('conclusion', $report->conclusion) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- KARTU 5: PENANDATANGANAN -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs">5</span>
                        <div>
                            <h3 class="text-base font-extrabold text-sipega-navy uppercase">Tempat, Tanggal & Tanda Tangan</h3>
                            <p class="text-xs text-gray-400">Pengesahan laporan.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tempat TTD</label>
                            <input type="text" name="signed_place" value="{{ old('signed_place', $report->signed_place) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tanggal TTD</label>
                            <input type="date" name="signed_date" value="{{ old('signed_date', $report->signed_date ? $report->signed_date->format('Y-m-d') : '') }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Ganti Gambar Tanda Tangan</label>
                            @php
                                $sigUrl = null;
                                if ($report->signature_image) {
                                    if (file_exists(public_path('storage/' . $report->signature_image))) {
                                        $sigUrl = asset('storage/' . $report->signature_image);
                                    } elseif (file_exists(public_path($report->signature_image))) {
                                        $sigUrl = asset($report->signature_image);
                                    }
                                }
                            @endphp
                            @if($sigUrl)
                            <div class="flex items-center gap-4 mb-3 p-3 bg-gray-50 rounded-2xl border border-gray-200">
                                <img src="{{ $sigUrl }}" class="h-14 border border-gray-300 rounded p-1 bg-white">
                                <label class="flex items-center gap-2 text-xs text-red-600 font-bold cursor-pointer">
                                    <input type="checkbox" name="remove_signature" value="1" class="rounded text-red-600 focus:ring-red-500">
                                    Hapus Tanda Tangan Ini
                                </label>
                            </div>
                            @endif
                            <input type="file" name="signature_image" accept="image/*" class="w-full text-xs rounded-2xl border border-gray-200 p-2.5 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sipega-navy file:text-white hover:file:bg-black">
                        </div>
                    </div>
                </div>

                <!-- KARTU 6: DOKUMENTASI FOTO KEGIATAN -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs">6</span>
                            <div>
                                <h3 class="text-base font-extrabold text-sipega-navy uppercase">Dokumentasi Foto Kegiatan</h3>
                                <p class="text-xs text-gray-400">Kelola foto yang sudah ada atau tambahkan foto baru.</p>
                            </div>
                        </div>
                        <button type="button" onclick="addPhotoRow()" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition border border-blue-200 flex items-center gap-1.5">
                            <span>📷+</span> Tambah Foto Baru
                        </button>
                    </div>

                    <!-- FOTO EKSISTING -->
                    @if(!empty($report->photos) && is_array($report->photos))
                    <div class="space-y-3">
                        <label class="block text-xs font-black uppercase text-gray-600">Foto Tersimpan (Centang untuk mempertahankan):</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($report->photos as $idx => $ph)
                                @php
                                    $phPath = $ph['path'] ?? '';
                                    $phCap = $ph['caption'] ?? '';
                                    $phUrl = null;
                                    if (file_exists(public_path('storage/' . $phPath))) {
                                        $phUrl = asset('storage/' . $phPath);
                                    } elseif (file_exists(public_path($phPath))) {
                                        $phUrl = asset($phPath);
                                    }
                                @endphp
                                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-200 flex gap-4 items-start">
                                    <div class="shrink-0 text-center">
                                        @if($phUrl)
                                            <img src="{{ $phUrl }}" class="w-24 h-20 object-cover rounded-xl border border-gray-300">
                                        @endif
                                        <label class="flex items-center justify-center gap-1.5 text-[10px] font-bold text-gray-600 mt-2 cursor-pointer">
                                            <input type="checkbox" name="keep_photos[]" value="{{ $idx }}" checked class="rounded text-sipega-navy">
                                            Simpan
                                        </label>
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Keterangan Foto</label>
                                        <input type="text" name="existing_photo_captions[{{ $idx }}]" value="{{ $phCap }}" class="w-full text-xs font-bold rounded-xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-2">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- CONTAINER FOTO BARU -->
                    <div id="photos_container" class="space-y-4 pt-2"></div>
                </div>

                <!-- TOMBOL SIMPAN -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <a href="{{ route('travel-reports.show', $report->id) }}" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-2xl text-xs transition">
                        Batal
                    </a>
                    <button type="submit" class="px-8 py-3.5 bg-sipega-navy hover:bg-black text-white font-black rounded-2xl text-xs uppercase tracking-wider shadow-xl transition-all hover:-translate-y-0.5 flex items-center gap-2">
                        <span>💾</span> Perbarui Laporan
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- FLOATING VOICE BUTTON (Bisa diklik kapan saja saat scroll ke bawah) -->
    <div id="floatingVoicePill" class="fixed bottom-6 right-6 z-40 flex items-center gap-2 bg-slate-900/95 backdrop-blur text-white p-2 pl-4 rounded-full shadow-2xl border border-slate-700 transition-all hover:scale-105">
        <div id="floatingVoiceDot" class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div>
        <span id="floatingVoiceLabel" class="text-xs font-black uppercase tracking-wider text-slate-200">
            Dikte: <span id="floatingTargetName" class="text-emerald-400 font-bold">Deskripsi</span>
        </span>
        <button type="button" id="floatingToggleBtn" onclick="toggleVoiceGlobal()" class="ml-1 w-9 h-9 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow transition cursor-pointer" title="Mulai / Berhenti Rekam Suara">
            🎙️
        </button>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div id="toastNotification" class="fixed bottom-20 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl border border-slate-700 flex items-center gap-3">
            <span class="text-lg" id="toastIcon">ℹ️</span>
            <span id="toastMessage" class="text-xs font-bold text-slate-200">Notifikasi</span>
        </div>
    </div>

    <script>
        // --- SISTEM INPUT DENGAN SUARA (VOICE TO TEXT) UNTUK LAPORAN PERJALANAN DINAS ---
        let recognition = null;
        let voiceState = 'idle'; // 'idle' | 'recording' | 'paused'
        let currentTargetField = 'description'; // default
        let baseTextBeforeSession = '';
        let timerInterval = null;
        let timerSeconds = 0;
        let isManualStopping = false;

        const targetFieldNames = {
            'description': '4. Deskripsi / Pembahasan Hasil',
            'purpose': '3. Tujuan Kegiatan',
            'output': '3. Output Kegiatan',
            'target_audience': '3. Sasaran Kegiatan',
            'conclusion': '4. Penutup / Rekomendasi',
            'activity_name': '3. Nama Kegiatan',
            'location': '3. Tempat Kegiatan'
        };

        function getSpeechRecognition() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                alert('Browser Anda belum mendukung Web Speech API. Silakan gunakan Google Chrome atau Microsoft Edge terbaru untuk fitur Input Suara.');
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

                for (let i = 0; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        sessionFinal += event.results[i][0].transcript + ' ';
                    } else {
                        sessionInterim += event.results[i][0].transcript;
                    }
                }

                const targetEl = document.getElementById(currentTargetField);
                if (targetEl) {
                    let formattedFinal = formatDictatedText(sessionFinal);
                    let formattedInterim = formatDictatedText(sessionInterim);

                    const fullText = (baseTextBeforeSession + formattedFinal + formattedInterim).trim();
                    targetEl.value = fullText;
                }

                const liveTranscript = document.getElementById('voiceLiveTranscript');
                if (liveTranscript) {
                    const spokenNow = (sessionFinal + sessionInterim).trim();
                    liveTranscript.textContent = spokenNow ? `"${spokenNow}"` : 'Mendengarkan ucapan Anda...';
                }
            };

            recognition.onerror = function(event) {
                console.warn('Voice event notice: ', event.error);
                if (event.error === 'not-allowed') {
                    showToast('Izin mikrofon belum diberikan pada browser.', '⚠️');
                    voiceStop();
                }
            };

            recognition.onend = function() {
                console.log('Voice engine: onend (voiceState=' + voiceState + ')');
                if (voiceState === 'recording' && !isManualStopping) {
                    const targetEl = document.getElementById(currentTargetField);
                    if (targetEl) {
                        const currentVal = targetEl.value.trim();
                        baseTextBeforeSession = currentVal ? currentVal + ' ' : '';
                    }

                    setTimeout(() => {
                        if (voiceState === 'recording' && !isManualStopping) {
                            try {
                                recognition.start();
                                console.log('Voice auto-restarted cleanly.');
                            } catch (e) {
                                console.warn('Voice restart notice: ', e);
                            }
                        }
                    }, 250);
                }
            };

            return recognition;
        }

        function formatDictatedText(text) {
            if (!text) return '';
            return text
                .replace(/\b(titik)\b/gi, '.')
                .replace(/\b(koma)\b/gi, ',')
                .replace(/\b(tanda tanya)\b/gi, '?')
                .replace(/\b(tanda seru)\b/gi, '!')
                .replace(/\b(titik dua)\b/gi, ':')
                .replace(/\b(baris baru|ganti baris|enter)\b/gi, '\n')
                .replace(/\b(paragraf baru)\b/gi, '\n\n')
                .replace(/\s+([.,?!:])/g, '$1');
        }

        function changeVoiceTarget(newField) {
            if (!newField) return;
            const wasRecording = (voiceState === 'recording');
            if (wasRecording) {
                isManualStopping = true;
                if (recognition) try { recognition.stop(); } catch(e) {}
            }

            currentTargetField = newField;
            const selectEl = document.getElementById('voiceTargetSelect');
            if (selectEl) selectEl.value = newField;

            const targetLabel = targetFieldNames[newField] || newField;
            const labelEl = document.getElementById('voiceCurrentTargetLabel');
            if (labelEl) labelEl.textContent = targetLabel;
            const floatNameEl = document.getElementById('floatingTargetName');
            if (floatNameEl) {
                const shortNames = {
                    'description': 'Deskripsi',
                    'purpose': 'Tujuan',
                    'output': 'Output',
                    'target_audience': 'Sasaran',
                    'conclusion': 'Penutup',
                    'activity_name': 'Kegiatan',
                    'location': 'Tempat'
                };
                floatNameEl.textContent = shortNames[newField] || newField;
            }

            highlightActiveField(newField);

            const targetEl = document.getElementById(newField);
            if (targetEl) {
                const currentVal = targetEl.value.trim();
                baseTextBeforeSession = currentVal ? currentVal + ' ' : '';
            } else {
                baseTextBeforeSession = '';
            }

            if (wasRecording) {
                isManualStopping = false;
                setTimeout(() => {
                    const rec = getSpeechRecognition();
                    if (rec) {
                        try { rec.start(); } catch(e) {}
                    }
                }, 200);
            }

            showToast('Target dikte suara diubah ke: ' + targetLabel, '🎯');
        }

        function highlightActiveField(fieldId) {
            ['activity_name', 'location', 'purpose', 'target_audience', 'output', 'description', 'conclusion'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.classList.remove('ring-4', 'ring-rose-400', 'border-rose-500', 'bg-rose-50/20');
                }
            });

            if (voiceState === 'recording') {
                const activeEl = document.getElementById(fieldId);
                if (activeEl) {
                    activeEl.classList.add('ring-4', 'ring-rose-400', 'border-rose-500', 'bg-rose-50/20');
                }
            }

            updateFieldMicButtons();
        }

        function updateFieldMicButtons() {
            document.querySelectorAll('.field-mic-btn').forEach(btn => {
                const field = btn.getAttribute('data-field');
                if (voiceState === 'recording' && field === currentTargetField) {
                    btn.className = 'field-mic-btn text-[10px] font-bold text-white flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 px-3 py-1 rounded-lg transition border border-rose-500 animate-pulse shadow-md';
                    btn.innerHTML = '<span>🔴</span> <span>Merekam (Klik Selesai)</span>';
                    btn.title = 'Sedang merekam suara... Klik untuk selesai';
                } else {
                    btn.className = 'field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1 bg-slate-100 hover:bg-emerald-50 px-2.5 py-0.5 rounded-lg transition border border-slate-200';
                    btn.innerHTML = '<span>🎙️</span> <span>Dikte</span>';
                    btn.title = 'Dikte dengan suara ke kolom ini';
                }
            });
        }

        function startVoiceForField(fieldId) {
            if (voiceState === 'recording' && currentTargetField === fieldId) {
                voicePause();
                return;
            }

            changeVoiceTarget(fieldId);

            const targetEl = document.getElementById(fieldId);
            if (targetEl) {
                targetEl.focus();
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            voiceStart();
        }

        function toggleVoiceGlobal() {
            if (voiceState === 'recording') {
                voicePause();
            } else {
                voiceStart();
            }
        }

        function voiceStart() {
            const rec = getSpeechRecognition();
            if (!rec) return;

            isManualStopping = false;
            const targetEl = document.getElementById(currentTargetField);
            if (targetEl) {
                const currentVal = targetEl.value.trim();
                baseTextBeforeSession = currentVal ? currentVal + ' ' : '';
            }

            if (voiceState === 'idle') {
                timerSeconds = 0;
            }

            voiceState = 'recording';

            try {
                rec.start();
            } catch (e) {
                console.log('Voice already running: ', e);
            }

            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                timerSeconds++;
                const mins = String(Math.floor(timerSeconds / 60)).padStart(2, '0');
                const secs = String(timerSeconds % 60).padStart(2, '0');
                const badge = document.getElementById('voiceDurationBadge');
                if (badge) badge.textContent = `🔴 ${mins}:${secs}`;
            }, 1000);

            const pulse = document.getElementById('globalVoicePulse');
            if (pulse) pulse.className = 'w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-2xl border border-rose-500 shrink-0 transition-all animate-pulse shadow-lg';

            const title = document.getElementById('voiceStatusTitle');
            if (title) {
                title.className = 'text-sm font-black uppercase tracking-wider text-rose-400';
                title.textContent = '🔴 Sedang Merekam Suara (Mic Aktif)';
            }

            const sub = document.getElementById('voiceStatusSubtitle');
            if (sub) {
                const targetLabel = targetFieldNames[currentTargetField] || currentTargetField;
                sub.innerHTML = `<span class="text-rose-200 font-semibold">Silakan bicara bebas dalam Bahasa Indonesia. Ucapan otomatis mengisi kolom <b class="text-white underline">${targetLabel}</b>.</span>`;
            }

            const badge = document.getElementById('voiceDurationBadge');
            if (badge) badge.classList.remove('hidden');

            const liveBox = document.getElementById('voiceLiveBox');
            if (liveBox) liveBox.classList.remove('hidden');

            document.getElementById('btnVoicePlay').disabled = true;
            document.getElementById('btnVoicePlay').classList.add('opacity-40', 'cursor-not-allowed');

            document.getElementById('btnVoicePause').disabled = false;
            document.getElementById('btnVoicePause').classList.remove('opacity-30', 'cursor-not-allowed');

            document.getElementById('btnVoiceStop').disabled = false;
            document.getElementById('btnVoiceStop').classList.remove('opacity-30', 'cursor-not-allowed');

            const floatToggleBtn = document.getElementById('floatingToggleBtn');
            if (floatToggleBtn) {
                floatToggleBtn.className = 'ml-1 w-9 h-9 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center font-bold text-sm shadow transition cursor-pointer animate-pulse';
                floatToggleBtn.innerHTML = '⏹';
                floatToggleBtn.title = 'Klik untuk selesai / jeda rekaman';
            }
            const floatDot = document.getElementById('floatingVoiceDot');
            if (floatDot) floatDot.className = 'w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping';

            highlightActiveField(currentTargetField);
            showToast('Mikrofon aktif! Mulai berbicara...', '🎙️');
        }

        function voicePause() {
            if (voiceState !== 'recording') return;

            isManualStopping = true;
            voiceState = 'paused';
            clearInterval(timerInterval);

            if (recognition) {
                try { recognition.stop(); } catch(e) {}
            }

            const targetEl = document.getElementById(currentTargetField);
            if (targetEl) {
                const currentVal = targetEl.value.trim();
                baseTextBeforeSession = currentVal ? currentVal + ' ' : '';
            }

            const pulse = document.getElementById('globalVoicePulse');
            if (pulse) pulse.className = 'w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl border border-amber-400 shrink-0 transition-all shadow-md';

            const title = document.getElementById('voiceStatusTitle');
            if (title) {
                title.className = 'text-sm font-black uppercase tracking-wider text-amber-300';
                title.textContent = '⏸ Rekaman Suara Dijeda (Paused)';
            }

            const sub = document.getElementById('voiceStatusSubtitle');
            if (sub) sub.innerHTML = '<span class="text-amber-200">Dikte suara sedang dijeda. Klik <b>[Lanjutkan Dikte]</b> untuk meneruskan rekaman.</span>';

            document.getElementById('btnVoicePlay').disabled = false;
            document.getElementById('btnVoicePlay').classList.remove('opacity-40', 'cursor-not-allowed');
            document.getElementById('txtVoicePlay').textContent = 'Lanjutkan Dikte';

            document.getElementById('btnVoicePause').disabled = true;
            document.getElementById('btnVoicePause').classList.add('opacity-30', 'cursor-not-allowed');

            const floatToggleBtn = document.getElementById('floatingToggleBtn');
            if (floatToggleBtn) {
                floatToggleBtn.className = 'ml-1 w-9 h-9 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow transition cursor-pointer';
                floatToggleBtn.innerHTML = '▶';
                floatToggleBtn.title = 'Klik untuk lanjutkan rekaman';
            }
            const floatDot = document.getElementById('floatingVoiceDot');
            if (floatDot) floatDot.className = 'w-2.5 h-2.5 rounded-full bg-amber-400';

            highlightActiveField(currentTargetField);
            showToast('Dikte suara dijeda.', '⏸');
        }

        function voiceStop() {
            isManualStopping = true;
            voiceState = 'idle';
            clearInterval(timerInterval);

            if (recognition) {
                try { recognition.stop(); } catch(e) {}
            }

            const targetEl = document.getElementById(currentTargetField);
            if (targetEl) {
                const currentVal = targetEl.value.trim();
                baseTextBeforeSession = currentVal ? currentVal + ' ' : '';
            }

            const pulse = document.getElementById('globalVoicePulse');
            if (pulse) pulse.className = 'w-12 h-12 rounded-2xl bg-slate-800 flex items-center justify-center text-2xl border border-slate-700 shrink-0 transition-all';

            const title = document.getElementById('voiceStatusTitle');
            if (title) {
                title.className = 'text-sm font-black uppercase tracking-wider text-slate-200';
                title.textContent = 'Input Laporan Dengan Suara (Voice to Text)';
            }

            const sub = document.getElementById('voiceStatusSubtitle');
            if (sub) {
                sub.textContent = 'Pilih kolom tujuan lalu klik [Mulai Dikte], atau klik tombol 🎙️ Dikte pada masing-masing isian form di bawah.';
            }

            const badge = document.getElementById('voiceDurationBadge');
            if (badge) badge.classList.add('hidden');

            const liveBox = document.getElementById('voiceLiveBox');
            if (liveBox) liveBox.classList.add('hidden');

            document.getElementById('btnVoicePlay').disabled = false;
            document.getElementById('btnVoicePlay').classList.remove('opacity-40', 'cursor-not-allowed');
            document.getElementById('txtVoicePlay').textContent = 'Mulai Dikte';

            document.getElementById('btnVoicePause').disabled = true;
            document.getElementById('btnVoicePause').classList.add('opacity-30', 'cursor-not-allowed');

            document.getElementById('btnVoiceStop').disabled = true;
            document.getElementById('btnVoiceStop').classList.add('opacity-30', 'cursor-not-allowed');

            const floatToggleBtn = document.getElementById('floatingToggleBtn');
            if (floatToggleBtn) {
                floatToggleBtn.className = 'ml-1 w-9 h-9 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow transition cursor-pointer';
                floatToggleBtn.innerHTML = '🎙️';
                floatToggleBtn.title = 'Mulai Dikte Suara';
            }
            const floatDot = document.getElementById('floatingVoiceDot');
            if (floatDot) floatDot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-400';

            highlightActiveField(currentTargetField);
            showToast('Dikte suara selesai tersimpan.', '✅');
        }

        function voiceClearCurrentTarget() {
            const targetEl = document.getElementById(currentTargetField);
            const targetLabel = targetFieldNames[currentTargetField] || currentTargetField;
            if (!targetEl) return;

            if (confirm(`Kosongkan teks pada ${targetLabel}?`)) {
                voiceStop();
                targetEl.value = '';
                baseTextBeforeSession = '';
                showToast(`Teks ${targetLabel} berhasil dikosongkan.`, '🧹');
            }
        }

        function focusVoiceToolbar() {
            const panel = document.getElementById('voiceToolbarPanel');
            if (panel) {
                panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                panel.classList.add('ring-4', 'ring-emerald-400');
                setTimeout(() => {
                    panel.classList.remove('ring-4', 'ring-emerald-400');
                }, 1500);
            }
        }

        function showToast(message, icon = 'ℹ️') {
            const toast = document.getElementById('toastNotification');
            const toastMsg = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');
            if (!toast || !toastMsg) return;

            toastMsg.textContent = message;
            if (toastIcon) toastIcon.textContent = icon;
            toast.classList.remove('translate-y-24', 'opacity-0');
            clearTimeout(window.toastTimer);
            window.toastTimer = setTimeout(() => {
                toast.classList.add('translate-y-24', 'opacity-0');
            }, 3000);
        }
        function addPhotoRow() {
            const container = document.getElementById('photos_container');
            const count = container.querySelectorAll('.photo-item').length + 1;

            const row = document.createElement('div');
            row.className = 'photo-item grid grid-cols-1 md:grid-cols-12 gap-3 items-center p-4 bg-gray-50/70 rounded-2xl border border-gray-100';
            row.innerHTML = `
                <div class="md:col-span-5">
                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">File Foto Baru ${count}</label>
                    <input type="file" name="photos[]" accept="image/*" class="w-full text-xs rounded-xl border border-gray-200 p-2 bg-white file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-gray-100 file:text-gray-700">
                </div>
                <div class="md:col-span-6">
                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Keterangan / Nama Sekolah</label>
                    <input type="text" name="photo_captions[]" placeholder="Keterangan foto..." class="w-full text-xs font-bold rounded-xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-2.5">
                </div>
                <div class="md:col-span-1 text-center pt-4">
                    <button type="button" onclick="removePhotoRow(this)" class="p-2 text-gray-400 hover:text-red-600 transition" title="Hapus Foto">
                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            `;
            container.appendChild(row);
        }

        function removePhotoRow(btn) {
            const row = btn.closest('.photo-item');
            if (row) row.remove();
        }
    </script>
</x-app-layout>
