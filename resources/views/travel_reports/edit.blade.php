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
            <div class="flex gap-2">
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
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Nama Kegiatan *</label>
                            <input type="text" name="activity_name" value="{{ old('activity_name', $report->activity_name) }}" required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tanggal Mulai</label>
                                <input type="date" name="start_date" value="{{ old('start_date', $report->start_date ? $report->start_date->format('Y-m-d') : '') }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tanggal Selesai</label>
                                <input type="date" name="end_date" value="{{ old('end_date', $report->end_date ? $report->end_date->format('Y-m-d') : '') }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Teks Tanggal Kustom</label>
                                <input type="text" name="date_range_text" value="{{ old('date_range_text', $report->date_range_text) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tempat Kegiatan *</label>
                            <textarea name="location" rows="2" required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">{{ old('location', $report->location) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Tujuan Kegiatan</label>
                            <textarea name="purpose" rows="4" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">{{ old('purpose', $report->purpose) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Sasaran Kegiatan</label>
                            <input type="text" name="target_audience" value="{{ old('target_audience', $report->target_audience) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Output Kegiatan</label>
                            <textarea name="output" rows="4" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-3">{{ old('output', $report->output) }}</textarea>
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
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Deskripsi / Hasil Pelaksanaan Lengkap *</label>
                            <textarea name="description" rows="14" required class="w-full text-xs font-medium rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-4 leading-relaxed">{{ old('description', $report->description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-gray-500 mb-1.5">Penutup / Kesimpulan & Rekomendasi</label>
                            <textarea name="conclusion" rows="5" class="w-full text-xs font-medium rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange p-4 leading-relaxed">{{ old('conclusion', $report->conclusion) }}</textarea>
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

    <script>
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
