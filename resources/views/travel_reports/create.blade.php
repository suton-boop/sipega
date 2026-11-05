<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Buat Laporan Perjalanan Dinas 📝
                </h2>
                <p class="text-[10px] font-black text-sipega-orange uppercase tracking-[0.3em] mt-1">
                    FORMULIR INPUT LAPORAN DINAS LUAR &bull; EKSPOR DOC & PDF
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="focusVoiceToolbar()" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black py-3 px-5 rounded-2xl shadow-xl transition-all hover:-translate-y-0.5 flex items-center gap-2 uppercase text-[10px] tracking-widest">
                    <span>🎙️</span> Input Suara
                </button>
                <button type="button" onclick="fillSampleTemplate()" class="bg-purple-600 hover:bg-purple-700 text-white font-black py-3 px-6 rounded-2xl shadow-xl transition-all hover:-translate-y-0.5 flex items-center gap-2 uppercase text-[10px] tracking-widest">
                    <span>📋</span> Contoh Template
                </button>
                <a href="{{ route('travel-reports.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-black py-3 px-6 rounded-2xl transition-all flex items-center gap-2 uppercase text-[10px] tracking-widest">
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

            <form action="{{ route('travel-reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

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
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs">1</span>
                            <div>
                                <h3 class="text-base font-extrabold text-sipega-navy uppercase">Hubungkan Dengan Surat Tugas (Opsional)</h3>
                                <p class="text-xs text-gray-400">Pilih Surat Tugas yang telah disetujui untuk mengisi data otomatis.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase text-gray-500 mb-2">Pilih Surat Tugas Disetujui (Approved)</label>
                        <select name="letter_id" id="letter_select" onchange="autoFillFromLetter(this.value)" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                            <option value="">-- Buat Laporan Mandiri / Tanpa Tautan Surat Tugas --</option>
                            @foreach($letters as $let)
                                <option value="{{ $let->id }}" 
                                    data-number="{{ $let->number }}"
                                    data-date="{{ $let->signed_at ? $let->signed_at->format('Y-m-d') : ($let->created_at ? $let->created_at->format('Y-m-d') : '') }}"
                                    data-title="{{ $let->title }}"
                                    data-location="{{ $let->location }}"
                                    data-start="{{ $let->date_start ? $let->date_start->format('Y-m-d') : '' }}"
                                    data-end="{{ $let->date_end ? $let->date_end->format('Y-m-d') : '' }}"
                                    data-purpose="{{ $let->purpose ?? '' }}"
                                    {{ (old('letter_id', request('letter_id')) == $let->id) ? 'selected' : '' }}>
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
                            <h3 class="text-base font-extrabold text-sipega-navy uppercase">Identitas Yang Melakukan Perjalanan Dinas</h3>
                            <p class="text-xs text-gray-400">Informasi pegawai pelaksana perjalanan dinas.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Nama Lengkap & Gelar *</label>
                            <input type="text" name="employee_name" id="employee_name" value="{{ old('employee_name', $user->name) }}" required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3" placeholder="Contoh: Sutono,S.Kom">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">NIP</label>
                            <input type="text" name="employee_nip" id="employee_nip" value="{{ old('employee_nip', $user->nip) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3" placeholder="Contoh: 198109282002121005">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Jabatan</label>
                            <input type="text" name="employee_position" id="employee_position" value="{{ old('employee_position', $user->position) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3" placeholder="Contoh: Widyaprada Ahli Pertama">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Nomor Surat Tugas *</label>
                            <input type="text" name="st_number" id="st_number" value="{{ old('st_number') }}" required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3" placeholder="Contoh: 1188/C6.24/DM.00.02/2026 Tanggal 24 September 2026">
                        </div>
                    </div>
                </div>

                <!-- KARTU 3: KEGIATAN -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs">3</span>
                        <div>
                            <h3 class="text-base font-extrabold text-sipega-navy uppercase">Rincian Kegiatan</h3>
                            <p class="text-xs text-gray-400">Deskripsi umum nama kegiatan, waktu, lokasi, dan tujuan.</p>
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
                            <input type="text" name="activity_name" id="activity_name" value="{{ old('activity_name') }}" required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3 transition-all" placeholder="Contoh: Verifikasi dan Validasi Data Pokok Pendidikan Tahun 2026">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tanggal Selesai</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Teks Tanggal Kustom</label>
                                <input type="text" name="date_range_text" id="date_range_text" value="{{ old('date_range_text') }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3" placeholder="Contoh: 29 September s.d 1 Oktober 2026">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Tempat Kegiatan *</label>
                                <button type="button" onclick="startVoiceForField('location')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1 bg-slate-100 hover:bg-emerald-50 px-2.5 py-0.5 rounded-lg transition border border-slate-200" data-field="location" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte</span>
                                </button>
                            </div>
                            <textarea name="location" id="location" rows="2" required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3 transition-all" placeholder="Contoh: SMAS Muhammadiyah Tanah Grogot&#10;SMA negeri 2 Unggulan Tanah Grogot">{{ old('location') }}</textarea>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Tujuan Kegiatan</label>
                                <button type="button" onclick="startVoiceForField('purpose')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1 bg-slate-100 hover:bg-emerald-50 px-2.5 py-0.5 rounded-lg transition border border-slate-200" data-field="purpose" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte</span>
                                </button>
                            </div>
                            <textarea name="purpose" id="purpose" rows="4" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3 transition-all" placeholder="Tuliskan tujuan kegiatan...">{{ old('purpose') }}</textarea>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Sasaran Kegiatan</label>
                                <button type="button" onclick="startVoiceForField('target_audience')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1 bg-slate-100 hover:bg-emerald-50 px-2.5 py-0.5 rounded-lg transition border border-slate-200" data-field="target_audience" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte</span>
                                </button>
                            </div>
                            <input type="text" name="target_audience" id="target_audience" value="{{ old('target_audience') }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3 transition-all" placeholder="Contoh: Responden kegiatan ini adalah Kepala Sekolah dan Operator Dapodik pada Satuan Pendidikan">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Output Kegiatan</label>
                                <button type="button" onclick="startVoiceForField('output')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1 bg-slate-100 hover:bg-emerald-50 px-2.5 py-0.5 rounded-lg transition border border-slate-200" data-field="output" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte</span>
                                </button>
                            </div>
                            <textarea name="output" id="output" rows="4" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3 transition-all" placeholder="Tuliskan output kegiatan...">{{ old('output') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- KARTU 4: DESKRIPSI & HASIL KEGIATAN -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs">4</span>
                        <div>
                            <h3 class="text-base font-extrabold text-sipega-navy uppercase">Deskripsi Kegiatan & Hasil Pelaksanaan</h3>
                            <p class="text-xs text-gray-400">Uraian narasi pelaksanaan, temuan uji petik/kegiatan, data lapangan, dan pembahasan.</p>
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
                            <textarea name="description" id="description" rows="14" required class="w-full text-xs font-medium rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-4 leading-relaxed transition-all" placeholder="Tuliskan narasi kegiatan dan hasil uji petik/kunjungan secara rinci...">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black uppercase text-gray-500">Penutup / Kesimpulan & Rekomendasi</label>
                                <button type="button" onclick="startVoiceForField('conclusion')" class="field-mic-btn text-[10px] font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1.5 bg-slate-100 hover:bg-emerald-50 px-3 py-1 rounded-lg transition border border-slate-200" data-field="conclusion" title="Dikte dengan suara ke kolom ini">
                                    <span>🎙️</span> <span>Dikte Kesimpulan</span>
                                </button>
                            </div>
                            <textarea name="conclusion" id="conclusion" rows="5" class="w-full text-xs font-medium rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-4 leading-relaxed transition-all" placeholder="Tuliskan kesimpulan dan rekomendasi tindak lanjut...">{{ old('conclusion') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- KARTU 5: PENANDATANGANAN -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                    <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs">5</span>
                        <div>
                            <h3 class="text-base font-extrabold text-sipega-navy uppercase">Tempat, Tanggal & Tanda Tangan</h3>
                            <p class="text-xs text-gray-400">Pengesahan laporan perjalanan dinas.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tempat TTD</label>
                            <input type="text" name="signed_place" id="signed_place" value="{{ old('signed_place', 'Samarinda') }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tanggal TTD</label>
                            <input type="date" name="signed_date" id="signed_date" value="{{ old('signed_date', now()->format('Y-m-d')) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Unggah Gambar Tanda Tangan (PNG / JPG / Transparan)</label>
                            <input type="file" name="signature_image" id="signature_image" accept="image/*" class="w-full text-xs rounded-2xl border border-gray-200 p-2.5 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sipega-navy file:text-white hover:file:bg-black">
                            <input type="hidden" name="existing_signature_image" id="existing_signature_image" value="">
                            <div id="signature_preview_box" class="mt-3 hidden">
                                <span class="text-[10px] text-gray-400 font-bold block mb-1">Preview Tanda Tangan:</span>
                                <img id="signature_preview_img" src="" class="h-16 border border-gray-200 rounded-lg p-1 bg-white inline-block">
                            </div>
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
                                <p class="text-xs text-gray-400">Lampirkan foto-foto kegiatan lapangan beserta keterangannya.</p>
                            </div>
                        </div>
                        <button type="button" onclick="addPhotoRow()" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition border border-blue-200 flex items-center gap-1.5">
                            <span>📷+</span> Tambah Foto
                        </button>
                    </div>

                    <input type="hidden" name="preset_photos_json" id="preset_photos_json" value="">

                    <div id="photos_container" class="space-y-4">
                        <!-- Baris Foto 1 -->
                        <div class="photo-item grid grid-cols-1 md:grid-cols-12 gap-3 items-center p-4 bg-gray-50/70 rounded-2xl border border-gray-100">
                            <div class="md:col-span-5">
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">File Foto 1</label>
                                <input type="file" name="photos[]" accept="image/*" class="w-full text-xs rounded-xl border border-gray-200 p-2 bg-white file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-gray-100 file:text-gray-700">
                            </div>
                            <div class="md:col-span-6">
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Keterangan / Nama Sekolah</label>
                                <input type="text" name="photo_captions[]" placeholder="Contoh: SMAS MUHAMMADIYAH TANAH GROGOT" class="w-full text-xs font-bold rounded-xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-2.5">
                            </div>
                            <div class="md:col-span-1 text-center pt-4">
                                <button type="button" onclick="removePhotoRow(this)" class="p-2 text-gray-400 hover:text-red-600 transition" title="Hapus Foto">
                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Baris Foto 2 -->
                        <div class="photo-item grid grid-cols-1 md:grid-cols-12 gap-3 items-center p-4 bg-gray-50/70 rounded-2xl border border-gray-100">
                            <div class="md:col-span-5">
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">File Foto 2</label>
                                <input type="file" name="photos[]" accept="image/*" class="w-full text-xs rounded-xl border border-gray-200 p-2 bg-white file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-gray-100 file:text-gray-700">
                            </div>
                            <div class="md:col-span-6">
                                <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">Keterangan / Nama Sekolah</label>
                                <input type="text" name="photo_captions[]" placeholder="Contoh: SMAN 2 UNGGULAN TANAH GROGOT" class="w-full text-xs font-bold rounded-xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-2.5">
                            </div>
                            <div class="md:col-span-1 text-center pt-4">
                                <button type="button" onclick="removePhotoRow(this)" class="p-2 text-gray-400 hover:text-red-600 transition" title="Hapus Foto">
                                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="preset_photos_preview" class="hidden p-4 bg-purple-50 rounded-2xl border border-purple-100">
                        <span class="text-xs font-bold text-purple-900 block mb-2">📸 Foto Sampel Dari Template Akan Digunakan:</span>
                        <div class="flex gap-4">
                            <div class="text-center">
                                <img src="{{ asset('images/sample_report/image2.jpeg') }}" class="h-20 w-32 object-cover rounded-lg border border-purple-200 shadow-sm">
                                <span class="text-[9px] font-bold text-purple-700 block mt-1">SMAS MUHAMMADIYAH</span>
                            </div>
                            <div class="text-center">
                                <img src="{{ asset('images/sample_report/image3.jpeg') }}" class="h-20 w-32 object-cover rounded-lg border border-purple-200 shadow-sm">
                                <span class="text-[9px] font-bold text-purple-700 block mt-1">SMAN 2 UNGGULAN</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOMBOL SIMPAN -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <a href="{{ route('travel-reports.index') }}" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-2xl text-xs transition">
                        Batal
                    </a>
                    <button type="submit" class="px-8 py-3.5 bg-sipega-navy hover:bg-black text-white font-black rounded-2xl text-xs uppercase tracking-wider shadow-xl transition-all hover:-translate-y-0.5 flex items-center gap-2">
                        <span>💾</span> Simpan & Terbitkan Laporan
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

    <!-- JAVASCRIPT LOGIC AUTO-FILL & PRESET TEMPLATE & VOICE INPUT -->
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
            'description': '4. Deskripsi / Hasil Lengkap',
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

            // Jalankan Timer
            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                timerSeconds++;
                const mins = String(Math.floor(timerSeconds / 60)).padStart(2, '0');
                const secs = String(timerSeconds % 60).padStart(2, '0');
                const badge = document.getElementById('voiceDurationBadge');
                if (badge) badge.textContent = `🔴 ${mins}:${secs}`;
            }, 1000);

            // UI Updates
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

            // Floating Pill Updates
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

            // UI Updates
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

            // Floating Pill Updates
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

            // Reset UI
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

            // Floating Pill Updates
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
        function autoFillFromLetter(letterId) {
            if (!letterId) return;

            const select = document.getElementById('letter_select');
            const selectedOpt = select.options[select.selectedIndex];
            if (!selectedOpt) return;

            const number = selectedOpt.getAttribute('data-number');
            const date = selectedOpt.getAttribute('data-date');
            const title = selectedOpt.getAttribute('data-title');
            const location = selectedOpt.getAttribute('data-location');
            const start = selectedOpt.getAttribute('data-start');
            const end = selectedOpt.getAttribute('data-end');
            const purpose = selectedOpt.getAttribute('data-purpose');

            if (number) {
                document.getElementById('st_number').value = number + (date ? ' Tanggal ' + formatDateIndo(date) : '');
            }
            if (title) {
                document.getElementById('activity_name').value = title;
            }
            if (location) {
                document.getElementById('location').value = location;
            }
            if (start) {
                document.getElementById('start_date').value = start;
            }
            if (end) {
                document.getElementById('end_date').value = end;
            }
            if (purpose) {
                document.getElementById('purpose').value = purpose;
            }
        }

        function formatDateIndo(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        }

        function addPhotoRow() {
            const container = document.getElementById('photos_container');
            const count = container.querySelectorAll('.photo-item').length + 1;

            const row = document.createElement('div');
            row.className = 'photo-item grid grid-cols-1 md:grid-cols-12 gap-3 items-center p-4 bg-gray-50/70 rounded-2xl border border-gray-100';
            row.innerHTML = `
                <div class="md:col-span-5">
                    <label class="block text-[10px] font-black uppercase text-gray-400 mb-1">File Foto ${count}</label>
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

        // Preview Signature input
        document.getElementById('signature_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    document.getElementById('signature_preview_img').src = evt.target.result;
                    document.getElementById('signature_preview_box').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        // 📋 FILL SAMPLE TEMPLATE (Verifikasi dan Validasi Data Pokok Pendidikan)
        function fillSampleTemplate() {
            // Gunakan data identitas pegawai yang sedang login (bukan hardcoded)
            document.getElementById('employee_name').value = @json($user->name ?? '');
            document.getElementById('employee_nip').value = @json($user->nip ?? '');
            document.getElementById('employee_position').value = @json($user->position ?? '');
            document.getElementById('st_number').value = "1188/C6.24/DM.00.02/2026 Tanggal 24 September 2026";

            document.getElementById('activity_name').value = "Verifikasi dan Validasi Data Pokok Pendidikan Tahun 2026";
            document.getElementById('start_date').value = "2026-09-29";
            document.getElementById('end_date').value = "2026-10-01";
            document.getElementById('date_range_text').value = "29 September s.d 1 Oktober 2026";
            document.getElementById('location').value = "SMAS Muhammadiyah Tanah Grogot\nSMA negeri 2 Unggulan Tanah Grogot";

            document.getElementById('purpose').value = `Tujuan dari verifikasi dan validasi adalah untuk mendapatkan gambaran yang representatif terhadap kondisi kualitas data Dapodik satuan pendidikan. Pengecekan kualitas data dapodik berdasarkan pada 3 indikator utama yaitu:
- Kelengkapan : Ukuran tingkat keterisian dan kelengkapan setiap entitas data pokok pendidikan
- Validitas : Ukuran tingkat kesesuaian data dengan standar atau aturan yang telah ditetapkan.
- Mutakhir : Ukuran kemutakhiran/kebaruan data pokok pendidikan untuk memastikan data yang dikumpulkan melalui data pokok pendidikan adalah data terkini.`;

            document.getElementById('target_audience').value = "Responden kegiatan ini adalah Kepala Sekolah dan Operator Dapodik pada Satuan Pendidikan";

            document.getElementById('output').value = `Terjaringnya data kondisi real di lapangan terkait 5 entitas Data Pokok Pendidikan yang meliputi :
- Satuan Pendidikan
- Sarana dan Prasarana
- Peserta Didik
- Pendidik dan Tenaga Kependidikan
- Rombongan Belajar
Teridentifikasinya kendala yang dialami oleh satuan Pendidikan dalam mengelola Dapodik;
Dapat menyampaikan analisis penyebab perbedaan data kondisi lapang dengan dapodik;
Menyampaikan rekomendasi atas hasil verifikasi dan validasi data yang dilakukan.`;

            document.getElementById('description').value = `Berdasarkan Permendikbud RI No. 31 Tahun 2022 tentang Satu Data Pendidikan, Kebudayaan, Riset, dan Teknologi, Data Pendidikan, Kebudayaan, Riset dan, Teknologi yang selanjutnya disebut Data adalah data di bidang pendidikan, kebudayaan, ilmu pengetahuan, dan teknologi yang dikelola oleh Kementerian. Satu Data Indonesia adalah kebijakan tata kelola data pemerintah untuk menghasilkan data yang akurat, mutakhir, terpadu, dan dapat dipertanggungjawabkan, serta mudah diakses dan dibagipakaikan antar instansi pusat dan instansi daerah melalui pemenuhan Standar Data, Metadata, Interoperabilitas Data, dan menggunakan Kode Referensi dan Data Induk.

Dapodik merupakan sumber data utama pendidikan di Indonesia dan berfungsi sebagai sistem penghubung layanan pemerintah, baik di lingkungan internal Kemendikbudristek maupun lintas Kementerian/Lembaga. Namun, saat ini terdapat isu terkait kualitas Dapodik: ditemukannya anomali pada data satuan pendidikan, data yang diinputkan pada Dapodik tidak sesuai dengan kondisi lapangan. Hal tersebut tentu berdampak pada kurang akuratnya pemanfaatan Dapodik bagi para pemangku kepentingan dalam mengambil keputusan/kebijakan. Oleh karena itu, Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur bermaksud mengawal kualitas Dapodik.

Dalam mengawal kualitas Dapodik, dilakukan penetapan indikator kualitas Dapodik tahun 2026. Adapun indikator kualitas Dapodik tersebut terdiri dari Kelengkapan/Completeness, Validitas/Validity, dan Mutakhir/Up to Date. Dengan adanya penetapan indikator tersebut, diperlukan kolaborasi dengan berbagai pihak untuk mengawal kualitas Dapodik melalui penyusunan Instrumen Uji Petik ke satuan pendidikan.

=== Hasil Pelaksanaan Uji Petik Pada Satuan Pendidikan ===

1. Sekolah SMAS Muhammadiyah Tanah Grogot
- Gambaran Pemahaman terhadap Kebijakan Pendataan:
Berdasarkan hasil uji petik dan telaah terhadap data profil satuan pendidikan, SMAS Muhammadiyah Tanah Grogot telah memiliki dan melakukan pemutakhiran data satuan pendidikan pada Dapodik. Hal ini terlihat dari tersedianya data identitas sekolah, peserta didik, PTK, rombongan belajar, serta sarana dan prasarana pada profil satuan pendidikan.
Hasil pengukuran Indikator Kualitas Data (IKD) menunjukkan bahwa skor IKD sekolah mencapai 96,67%, rombongan belajar 97,14%, peserta didik 78,22%, PTK 89,86%, bangunan 37,57%, ruang 83,67%, dan tanah 76,12%. Fokus penguatan perlu diarahkan pada pemahaman bahwa pendataan Dapodik bukan hanya pemenuhan administrasi, tetapi dasar perencanaan dan pengambilan kebijakan.

- Entitas Satuan Pendidikan:
NPSN 30400178, SMA Swasta di bawah Yayasan, Akreditasi A. Beralamat di Jalan Pangeran Menteri Tanah Grogot No. 96, Kabupaten Paser, Kalimantan Timur. Daya listrik PLN 10.600 VA, internet fiber optic broadband, dan berpartisipasi dalam BOSP. Kualitas: Kelengkapan 100%, Validitas 91,67%, Kemutakhiran 100%, IKD 96,67%.

- Entitas Sarana dan Prasarana:
Data tanah kelengkapan 100%, validitas 66,67%, kemutakhiran 64,84%, IKD 76,12%. Diperlukan verifikasi dokumen kepemilikan. Data prasarana: 8 ruang kelas, 1 perpustakaan, ruang kepsek, ruang guru, ruang TU, ruang UKS, toilet siswa & guru, lab komputer, dan lab IPA. Entitas ruang IKD 83,67%, sedangkan entitas bangunan memperoleh IKD 37,57% (kelengkapan 33,33%, validitas 50%, kemutakhiran 25,24%) sehingga menjadi prioritas pemutakhiran.

- Entitas Peserta Didik:
Semester ganjil 2026/2027 tercatat 230 peserta didik (144 L, 86 P), turun dari semester sebelumnya 235 peserta didik. Skor IKD peserta didik 78,22% (kelengkapan 80,41%, validitas 82,44%, kemutakhiran 70,39%). Tindak lanjut: pemutakhiran berkala atas mutasi/perubahan status siswa.

- Entitas PTK:
Jumlah PTK sebanyak 19 orang (12 guru, 7 tendik), stabil dari semester lalu. IKD PTK 89,86% (kelengkapan 93,57%, validitas 93,98%, kemutakhiran 80,65%).

- Entitas Rombongan Belajar:
Tercatat 7 rombel, skor IKD mencapai 97,14% (kelengkapan 100%, validitas 92,86%, kemutakhiran 100%).

- Kondisi Data Penerima DAK Fisik:
Tidak tercantum status khusus penerima DAK Fisik pada profil Dapodik yang ditelaah sehingga perlu verifikasi sumber data pendukung lebih lanjut.

2. Sekolah SMAN 2 Unggulan Tanah Grogot
- Gambaran Pemahaman terhadap Kebijakan Pendataan:
SMA Negeri 2 Unggulan Tanah Grogot memiliki data yang lengkap berstatus negeri di bawah Pemerintah Daerah dengan Akreditasi A. Hasil pengukuran IKD: sekolah 96,67%, rombongan belajar 100%, peserta didik 75,35%, PTK 88,23%, bangunan 47,73%, ruang 80,09%, dan tanah 79,65%.

- Entitas Satuan Pendidikan:
NPSN 69899868, Akreditasi A, SK Pendirian 420/KEP-362/2015. Daya listrik PLN 33.000 VA, internet fiber optic shared PT Telkom 100 Mbps, berpartisipasi dalam BOSP. Kualitas: Kelengkapan 100%, Validitas 91,67%, Kemutakhiran 100%, IKD 96,67%.

- Entitas Sarana dan Prasarana:
Data tanah IKD 79,65% (kelengkapan 100%, validitas 66,67%, kemutakhiran 76,60%). Prasarana memiliki 22 ruang kelas, perpustakaan, kepsek, guru, 2 TU, UKS, 8 toilet siswa, lab komputer, lab Biologi, Fisika, Kimia, Multi, Bahasa, dan kantin. Entitas ruang IKD 80,09%, entitas bangunan IKD 47,73% (kelengkapan 21,43%, validitas 82,14%, kemutakhiran 28,16%).

- Entitas Peserta Didik:
Semester ganjil 2026/2027 tercatat 585 peserta didik (262 L, 323 P), naik dari 580 peserta didik pada semester genap sebelumnya. Skor IKD peserta didik 75,35% (kelengkapan 82,79%, validitas 84,40%, kemutakhiran 55,86%).

- Entitas PTK:
Tercatat 45 orang (34 guru, 11 tendik), sama dengan semester lalu. Skor IKD PTK 88,23% (kelengkapan 92,32%, validitas 93,56%, kemutakhiran 77,04%).

- Entitas Rombongan Belajar:
Tercatat 18 rombel. Entitas rombel memperoleh nilai sempurna IKD 100% (kelengkapan 100%, validitas 100%, kemutakhiran 100%).

- Kondisi Data Penerima DAK Fisik:
Diperlukan verifikasi terhadap dokumen atau sumber data pendukung yang secara khusus memuat informasi terkait penerima DAK Fisik.`;

            document.getElementById('conclusion').value = `Berdasarkan hasil uji petik dan telaah data Dapodik pada SMAS Muhammadiyah Tanah Grogot dan SMA Negeri 2 Unggulan Tanah Grogot, secara umum kedua satuan pendidikan telah memiliki data pokok pendidikan yang tersedia pada entitas sekolah, peserta didik, PTK, rombongan belajar, serta sarana dan prasarana. Kualitas data pada entitas sekolah dan rombongan belajar menunjukkan kondisi yang relatif baik pada kedua satuan pendidikan.

Namun demikian, hasil uji petik menunjukkan masih terdapat beberapa entitas yang perlu mendapat perhatian dalam hal kelengkapan, validitas, dan terutama kemutakhiran data. Pada SMAS Muhammadiyah Tanah Grogot, perhatian utama terdapat pada data bangunan, tanah, peserta didik, dan ruang. Sementara pada SMA Negeri 2 Unggulan Tanah Grogot, aspek yang perlu diprioritaskan meliputi bangunan, peserta didik, ruang, dan tanah.

Hasil uji petik juga menunjukkan bahwa data bangunan menjadi salah satu aspek yang paling perlu mendapatkan perhatian pada kedua sekolah. Oleh karena itu, diperlukan verifikasi antara data yang tercatat dalam Dapodik dengan kondisi fisik dan dokumen administrasi satuan pendidikan. Selain itu, pemutakhiran data peserta didik, ruang, tanah, dan PTK perlu dilakukan secara berkala agar data yang tersedia selalu menggambarkan kondisi faktual satuan pendidikan.

Dengan demikian, tindak lanjut hasil uji petik diarahkan pada penguatan pemahaman satuan pendidikan mengenai pentingnya kualitas data, pelaksanaan verifikasi dan validasi secara berkala, pemutakhiran data sesuai kondisi riil, serta penguatan dokumentasi pendukung. Untuk aspek penerima DAK Fisik, diperlukan verifikasi lebih lanjut melalui sumber atau dokumen pendukung karena informasi tersebut tidak tercantum secara khusus dalam profil Dapodik yang ditelaah.`;

            document.getElementById('signed_place').value = "Samarinda";
            document.getElementById('signed_date').value = "2026-10-03";

            // Tanda tangan dikosongkan sesuai instruksi
            document.getElementById('existing_signature_image').value = "";
            document.getElementById('signature_image').value = "";
            document.getElementById('signature_preview_img').src = "";
            document.getElementById('signature_preview_box').classList.add('hidden');

            // Pasang foto dokumentasi preset
            const presetPhotos = [
                { path: "images/sample_report/image2.jpeg", caption: "SMAS MUHAMMADIYAH TANAH GROGOT" },
                { path: "images/sample_report/image3.jpeg", caption: "SMAN 2 UNGGULAN TANAH GROGOT" }
            ];
            document.getElementById('preset_photos_json').value = JSON.stringify(presetPhotos);
            document.getElementById('preset_photos_preview').classList.remove('hidden');

            showToast('Contoh template berhasil dimuat dengan data identitas Anda.', '📋');
            window.scrollTo({ top: 300, behavior: 'smooth' });
        }
    </script>
</x-app-layout>
