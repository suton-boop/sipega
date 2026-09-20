<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('letters.index') }}" class="text-xs font-bold text-gray-400 hover:text-sipega-navy flex items-center gap-1 mb-1">
                    ← Kembali ke Daftar Surat Tugas
                </a>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight uppercase tracking-wider">
                    📝 Buat Surat Tugas Baru
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="suratTugasApp()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if ($errors->any())
            <div class="p-5 bg-red-50 border-l-4 border-red-500 rounded-2xl text-red-800 shadow-sm">
                <div class="font-black text-sm mb-2 flex items-center gap-2">
                    <span>⚠️</span> Terdapat kesalahan pengisian:
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 font-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('letters.store') }}" method="POST" id="stForm">
                @csrf

                <!-- 1. PILIH MODEL SURAT TUGAS (5 MODEL) -->
                <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] border border-gray-100 shadow-xl space-y-6">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-sipega-orange bg-orange-50 px-3 py-1 rounded-full border border-orange-100">LANGKAH 1</span>
                        <h3 class="text-xl font-black text-sipega-navy mt-2">Pilih Model Surat Tugas</h3>
                        <p class="text-xs text-gray-400 font-bold mt-0.5">Sesuaikan dengan format fisik dokumen resmi BPMP Kalimantan Timur</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        <!-- Model 1 -->
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="selectedModel === 'model_1' ? 'border-sipega-orange bg-orange-50/40 shadow-md ring-2 ring-orange-200' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="st_model" value="model_1" x-model="selectedModel" class="sr-only" @change="handleModelChange('model_1')">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black" :class="selectedModel === 'model_1' ? 'text-sipega-orange' : 'text-gray-700'">MODEL 1</span>
                                <span class="text-lg">👤</span>
                            </div>
                            <span class="text-xs font-black text-sipega-navy mt-2">Surat Tugas 1 Orang</span>
                            <span class="text-[10px] text-gray-500 mt-1 font-medium leading-tight">Perorangan / Narasumber tunggal. Format 1 halaman pas.</span>
                        </label>

                        <!-- Model 2 -->
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="selectedModel === 'model_2' ? 'border-sipega-orange bg-orange-50/40 shadow-md ring-2 ring-orange-200' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="st_model" value="model_2" x-model="selectedModel" class="sr-only" @change="handleModelChange('model_2')">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black" :class="selectedModel === 'model_2' ? 'text-sipega-orange' : 'text-gray-700'">MODEL 2</span>
                                <span class="text-lg">👥</span>
                            </div>
                            <span class="text-xs font-black text-sipega-navy mt-2">Kolektif (>1 Orang)</span>
                            <span class="text-[10px] text-gray-500 mt-1 font-medium leading-tight">Banyak pegawai. Tabel 3 kolom (No, Nama/NIP/Pkt, Jabatan).</span>
                        </label>

                        <!-- Model 3 -->
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="selectedModel === 'model_3' ? 'border-sipega-orange bg-orange-50/40 shadow-md ring-2 ring-orange-200' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="st_model" value="model_3" x-model="selectedModel" class="sr-only" @change="handleModelChange('model_3')">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black" :class="selectedModel === 'model_3' ? 'text-sipega-orange' : 'text-gray-700'">MODEL 3</span>
                                <span class="text-lg">💻</span>
                            </div>
                            <span class="text-xs font-black text-sipega-navy mt-2">Kegiatan Daring</span>
                            <span class="text-[10px] text-gray-500 mt-1 font-medium leading-tight">Pelatihan Teknis / Bimtek online. Tempat otomatis Daring.</span>
                        </label>

                        <!-- Model 4 -->
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="selectedModel === 'model_4' ? 'border-sipega-orange bg-orange-50/40 shadow-md ring-2 ring-orange-200' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="st_model" value="model_4" x-model="selectedModel" class="sr-only" @change="handleModelChange('model_4')">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black" :class="selectedModel === 'model_4' ? 'text-sipega-orange' : 'text-gray-700'">MODEL 4</span>
                                <span class="text-lg">📑</span>
                            </div>
                            <span class="text-xs font-black text-sipega-navy mt-2">Lampiran Matriks</span>
                            <span class="text-[10px] text-gray-500 mt-1 font-medium leading-tight">Pengantar di Hal 1, Lampiran matriks penugasan per Kab/Kota.</span>
                        </label>

                        <!-- Model 5 -->
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="selectedModel === 'model_5' ? 'border-sipega-orange bg-orange-50/40 shadow-md ring-2 ring-orange-200' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="st_model" value="model_5" x-model="selectedModel" class="sr-only" @change="handleModelChange('model_5')">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black" :class="selectedModel === 'model_5' ? 'text-sipega-orange' : 'text-gray-700'">MODEL 5</span>
                                <span class="text-lg">🏷️</span>
                            </div>
                            <span class="text-xs font-black text-sipega-navy mt-2">Kolom Keterangan</span>
                            <span class="text-[10px] text-gray-500 mt-1 font-medium leading-tight">Tabel 4 kolom dengan kolom Keterangan (Pokja/Bidang/Tugas).</span>
                        </label>
                    </div>
                </div>

                <!-- 2. KATEGORI SURAT TUGAS (3 KATEGORI) -->
                <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] border border-gray-100 shadow-xl space-y-4 mt-6">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">LANGKAH 2</span>
                        <h3 class="text-xl font-black text-sipega-navy mt-2">Pilih Kategori Surat Tugas</h3>
                        <p class="text-xs text-gray-400 font-bold mt-0.5">Digunakan untuk akumulasi otomatis pada Rekapitulasi Tugas (Travel Recap)</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="category === 'DLK' ? 'border-blue-600 bg-blue-50/40 ring-2 ring-blue-100' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="category" value="DLK" x-model="category" class="text-blue-600 focus:ring-blue-500">
                            <div>
                                <span class="text-xs font-black text-blue-950 block">🏢 Kategori 1: DLK</span>
                                <span class="text-[11px] font-bold text-gray-600">Surat Tugas Kegiatan Kantor</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="category === 'DLP' ? 'border-purple-600 bg-purple-50/40 ring-2 ring-purple-100' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="category" value="DLP" x-model="category" class="text-purple-600 focus:ring-purple-500">
                            <div>
                                <span class="text-xs font-black text-purple-950 block">🏛️ Kategori 2: DLP</span>
                                <span class="text-[11px] font-bold text-gray-600">Surat Tugas Kegiatan Pusat</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                               :class="category === 'DLN' ? 'border-emerald-600 bg-emerald-50/40 ring-2 ring-emerald-100' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="category" value="DLN" x-model="category" class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-black text-emerald-950 block">🤝 Kategori 3: DLN</span>
                                <span class="text-[11px] font-bold text-gray-600">Surat Tugas Kegiatan Kemitraan</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 3. DATA DASAR UNDANGAN & DETAIL SURAT -->
                <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] border border-gray-100 shadow-xl space-y-6 mt-6">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-sipega-navy bg-slate-100 px-3 py-1 rounded-full border border-gray-200">LANGKAH 3</span>
                        <h3 class="text-xl font-black text-sipega-navy mt-2">Dasar Undangan & Detail Kegiatan</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Nomor Surat Tugas -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Nomor Surat Tugas <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="number" value="{{ old('number', $nextNumber) }}" required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                            <span class="text-[10px] text-gray-400 mt-1 block">Format baku: 0701/C6.24/DM.00.02/2026 atau KP.10.00</span>
                        </div>

                        <!-- Judul/Perihal Tugas -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Nama Kegiatan / Perihal Penugasan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: Penguatan Kapasitas Calon Fasilitator Nasional BOSP..." required class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>

                        <!-- Pengundang / Dasar Surat -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Instansi Pengundang / Pejabat yang Mengundang (Dasar Surat)
                            </label>
                            <input type="text" name="invitation_from" value="{{ old('invitation_from') }}" placeholder="Contoh: Direktur Pendidikan Anak Usia Dini, Ditjen PAUD Dikdasmen, Kemendikdasmen" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>

                        <!-- Nomor Surat Undangan & Tanggal Undangan -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Nomor Surat Undangan
                            </label>
                            <input type="text" name="invitation_number" value="{{ old('invitation_number') }}" placeholder="Contoh: 1667/C2/DM.00.01/2026" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Tanggal Surat Undangan
                            </label>
                            <input type="date" name="invitation_date" value="{{ old('invitation_date') }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>

                        <!-- Hal/Perihal Undangan -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Perihal pada Surat Undangan (Hal)
                            </label>
                            <input type="text" name="invitation_subject" value="{{ old('invitation_subject') }}" placeholder="Contoh: Penguatan Kapasitas Calon Fasilitator Nasional Implementasi BOSP Kinerja Terbaik Region 3 Tahun 2026" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>

                        <!-- Tanggal Pelaksanaan -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Tanggal Mulai Pelaksanaan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="date_start" 
                                   x-model="dateStart" 
                                   @change="fetchConflicts()"
                                   required 
                                   class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Tanggal Selesai Pelaksanaan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="date_end" 
                                   x-model="dateEnd" 
                                   @change="fetchConflicts()"
                                   required 
                                   class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>

                        <!-- Tempat Kegiatan -->
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Tempat Kegiatan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="location" x-model="locationText" required placeholder="Contoh: Harris Hotel & Conventions Gubeng Surabaya / Daring" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>

                        <!-- DIPA / Pembebanan Anggaran (Checkbox Opsional) -->
                        <div class="md:col-span-2 pt-2 border-t border-gray-100">
                            <label class="inline-flex items-center gap-3 p-3.5 bg-blue-50/70 rounded-2xl border border-blue-200 cursor-pointer w-full">
                                <input type="checkbox" x-model="useDipa" class="rounded text-sipega-orange focus:ring-sipega-orange w-4 h-4">
                                <div>
                                    <span class="text-xs font-black text-blue-950 block">💰 Pembebanan Anggaran / Sumber Dana (DIPA) — Opsional</span>
                                    <span class="text-[11px] font-medium text-blue-800">Centang jika kegiatan ini memiliki pembebanan anggaran. Jika tidak dicentang, klausul pembebanan anggaran ditiadakan pada surat tugas.</span>
                                </div>
                            </label>

                            <!-- Input DIPA yang muncul saat checkbox dicentang -->
                            <div x-show="useDipa" x-transition class="mt-3 p-4 bg-gray-50 rounded-2xl border border-gray-200">
                                <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                    Sumber Dana / DIPA
                                </label>
                                <input type="text" 
                                       name="dipa_source" 
                                       x-model="dipaSource" 
                                       :disabled="!useDipa"
                                       placeholder="Contoh: DIPA Direktorat Pendidikan Anak Usia Dini / DIPA BPMP Provinsi Kalimantan Timur" 
                                       class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                            </div>
                        </div>

                        <!-- OPSI TAMPILKAN KOLOM KETERANGAN -->
                        <div class="md:col-span-2 pt-2 border-t border-gray-100">
                            <label class="inline-flex items-center gap-3 p-3 bg-amber-50/60 rounded-2xl border border-amber-200 cursor-pointer">
                                <input type="checkbox" name="show_keterangan" value="1" x-model="showKeterangan" class="rounded text-sipega-orange focus:ring-sipega-orange w-4 h-4">
                                <div>
                                    <span class="text-xs font-black text-amber-950 block">🏷️ Tampilkan Kolom Keterangan pada Surat Tugas</span>
                                    <span class="text-[11px] font-medium text-amber-800">Centang opsi ini untuk menampilkan kolom Keterangan (seperti SPMI, LITNUM, DIGITALISASI) di tabel personil dan PDF.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 4. TABEL PERSONIL DITUGASKAN (DENGAN AUTO-FILL) -->
                <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] border border-gray-100 shadow-xl space-y-6 mt-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">LANGKAH 4</span>
                            <h3 class="text-xl font-black text-sipega-navy mt-2">Daftar Pegawai Ditugaskan</h3>
                            <p class="text-xs text-gray-400 font-bold mt-0.5">Pilih nama pegawai, data NIP, Pangkat/Golongan, dan Jabatan akan <b>terisi otomatis</b></p>
                        </div>
                        <template x-if="selectedModel !== 'model_1'">
                            <button type="button" @click="addRow()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider rounded-2xl transition flex items-center gap-2 shadow-sm">
                                <span>➕</span> Tambah Pegawai
                            </button>
                        </template>
                    </div>

                    <!-- Indikator Pengecekan Bentrok Jadwal -->
                    <div x-show="isLoadingConflicts" class="p-3.5 bg-blue-50 border border-blue-200 rounded-2xl text-xs text-blue-700 flex items-center gap-2.5">
                        <span class="animate-spin text-base">🔄</span>
                        <span class="font-bold">Memeriksa ketersediaan dan jadwal dinas pegawai untuk tanggal yang dipilih...</span>
                    </div>

                    <div x-show="!isLoadingConflicts && Object.keys(busyUsers).length > 0" class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">🛡️</span>
                            <div>
                                <span class="font-black block">Sistem Ketat Penunjukan Pegawai Aktif</span>
                                <span class="text-[11px] text-amber-700">Terdapat <b class="text-red-600" x-text="Object.keys(busyUsers).length"></b> pegawai yang sedang bertugas pada rentang tanggal ini. Pegawai tersebut otomatis dikunci dan tidak dapat dipilih.</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-amber-200/80 text-amber-900 rounded-lg text-[10px] font-black uppercase tracking-wider">Terkunci Otomatis</span>
                    </div>

                    <div x-show="hasSelectedConflict()" class="p-3.5 bg-red-50 border border-red-300 rounded-2xl text-xs text-red-900 flex items-center gap-3">
                        <span class="text-xl">⚠️</span>
                        <div>
                            <span class="font-black block">Peringatan Bentrok Jadwal Pegawai!</span>
                            <span class="text-[11px] text-red-700">Terdapat pegawai terpilih yang memiliki jadwal tugas dinas lain pada tanggal ini. Mohon ganti pegawai pada baris yang ditandai merah sebelum menyimpan surat tugas.</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-[10px] font-black uppercase tracking-wider text-gray-500 border-b border-gray-200">
                                    <th class="py-3 px-4 w-12 text-center">No</th>
                                    <th class="py-3 px-4 min-w-[220px]">Pilih Pegawai</th>
                                    <th class="py-3 px-4 min-w-[180px]">NIP & Pangkat / Golongan</th>
                                    <th class="py-3 px-4 min-w-[180px]">Jabatan</th>
                                    
                                    <!-- Kolom Khusus Model 4 (Lampiran Matriks) -->
                                    <template x-if="selectedModel === 'model_4'">
                                        <th class="py-3 px-4 min-w-[140px]">Kab / Kota</th>
                                    </template>
                                    <template x-if="selectedModel === 'model_4'">
                                        <th class="py-3 px-4 min-w-[120px]">Peran (Narsum/Panitia)</th>
                                    </template>
                                    <template x-if="selectedModel === 'model_4'">
                                        <th class="py-3 px-4 min-w-[160px]">Tempat Spesifik</th>
                                    </template>
                                    <template x-if="selectedModel === 'model_4'">
                                        <th class="py-3 px-4 min-w-[140px]">Tanggal</th>
                                    </template>
                                    <template x-if="selectedModel === 'model_4'">
                                        <th class="py-3 px-4 min-w-[160px]">Penanggung Jawab</th>
                                    </template>

                                    <!-- Kolom Keterangan (Model 5 atau saat checkbox dicentang) -->
                                    <template x-if="showKeterangan || selectedModel === 'model_5'">
                                        <th class="py-3 px-4 min-w-[140px]">Keterangan</th>
                                    </template>

                                    <th class="py-3 px-4 w-16 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                <template x-for="(row, index) in participants" :key="index">
                                    <tr :class="busyUsers[row.user_id] ? 'bg-red-50/70 border-2 border-red-300' : 'hover:bg-gray-50/50'">
                                        <td class="py-3 px-4 text-center font-bold text-gray-400" x-text="index + 1"></td>
                                        
                                        <!-- Pilih Pegawai -->
                                        <td class="py-3 px-4">
                                            <select :name="`participants[${index}][user_id]`" 
                                                    x-model="row.user_id" 
                                                    @change="onUserSelect(index)" 
                                                    required 
                                                    :class="busyUsers[row.user_id] ? 'border-red-500 bg-red-50 text-red-700' : 'border-gray-200'"
                                                    class="w-full text-xs font-bold rounded-xl focus:border-sipega-orange focus:ring-sipega-orange py-2">
                                                <option value="">-- Pilih Pegawai --</option>
                                                @foreach($users as $u)
                                                <option value="{{ $u->id }}"
                                                        :disabled="busyUsers['{{ $u->id }}'] !== undefined"
                                                        :class="busyUsers['{{ $u->id }}'] ? 'text-red-500 bg-red-50 font-bold' : ''">
                                                    {{ $u->name }}
                                                </option>
                                                @endforeach
                                            </select>

                                            <!-- Peringatan di Bawah Dropdown jika bentrok -->
                                            <template x-if="busyUsers[row.user_id]">
                                                <div class="mt-1.5 p-2 bg-red-100/70 border border-red-200 rounded-xl text-[10px] text-red-800 flex items-start gap-1">
                                                    <span class="text-xs">🚫</span>
                                                    <div>
                                                        <b>Sedang Dinas:</b> <span x-text="busyUsers[row.user_id].title"></span>
                                                        (<span x-text="busyUsers[row.user_id].date_range"></span>)
                                                    </div>
                                                </div>
                                            </template>
                                        </td>

                                        <!-- NIP & Golongan (Bisa diedit/diisi manual) -->
                                        <td class="py-3 px-4">
                                            <div class="space-y-1.5">
                                                <div>
                                                    <label class="text-[9px] font-black uppercase tracking-wider text-gray-400 block mb-0.5">NIP (Bisa diedit/diisi)</label>
                                                    <input type="text" 
                                                           :name="`participants[${index}][nip]`" 
                                                           x-model="row.nip" 
                                                           placeholder="Contoh: 198001012005011001" 
                                                           class="w-full text-xs font-mono font-bold rounded-xl border-gray-200 py-1.5 px-2.5 focus:border-sipega-orange focus:ring-sipega-orange">
                                                </div>
                                                <div>
                                                    <label class="text-[9px] font-black uppercase tracking-wider text-gray-400 block mb-0.5">Pangkat / Golongan</label>
                                                    <input type="text" 
                                                           :name="`participants[${index}][golongan]`" 
                                                           x-model="row.golongan" 
                                                           placeholder="Contoh: Pembina, IV/a" 
                                                           class="w-full text-[11px] font-medium rounded-xl border-gray-200 py-1 px-2.5 focus:border-sipega-orange focus:ring-sipega-orange">
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Jabatan (Bisa diedit/diisi manual) -->
                                        <td class="py-3 px-4">
                                            <label class="text-[9px] font-black uppercase tracking-wider text-gray-400 block mb-0.5">Jabatan Pegawai</label>
                                            <input type="text" 
                                                   :name="`participants[${index}][position]`" 
                                                   x-model="row.position" 
                                                   placeholder="Contoh: Widyaprada Ahli Madya" 
                                                   class="w-full text-xs font-semibold rounded-xl border-gray-200 py-1.5 px-2.5 focus:border-sipega-orange focus:ring-sipega-orange">
                                        </td>

                                        <!-- Khusus Model 4 -->
                                        <template x-if="selectedModel === 'model_4'">
                                            <td class="py-3 px-4">
                                                <input type="text" :name="`participants[${index}][city_destination]`" x-model="row.city_destination" placeholder="Bontang / Paser..." class="w-full text-xs rounded-xl border-gray-200 py-2">
                                            </td>
                                        </template>
                                        <template x-if="selectedModel === 'model_4'">
                                            <td class="py-3 px-4">
                                                <select :name="`participants[${index}][custom_role]`" x-model="row.custom_role" class="w-full text-xs rounded-xl border-gray-200 py-2">
                                                    <option value="Narasumber">Narasumber</option>
                                                    <option value="Panitia">Panitia</option>
                                                    <option value="Fasilitator">Fasilitator</option>
                                                    <option value="Pendamping">Pendamping</option>
                                                </select>
                                            </td>
                                        </template>
                                        <template x-if="selectedModel === 'model_4'">
                                            <td class="py-3 px-4">
                                                <input type="text" :name="`participants[${index}][venue]`" x-model="row.venue" placeholder="SMPN 2 Tanah Grogot..." class="w-full text-xs rounded-xl border-gray-200 py-2">
                                            </td>
                                        </template>
                                        <template x-if="selectedModel === 'model_4'">
                                            <td class="py-3 px-4">
                                                <input type="text" :name="`participants[${index}][execution_dates]`" x-model="row.execution_dates" placeholder="21 s.d 23 Juli 2026" class="w-full text-xs rounded-xl border-gray-200 py-2">
                                            </td>
                                        </template>
                                        <template x-if="selectedModel === 'model_4'">
                                            <td class="py-3 px-4">
                                                <input type="text" :name="`participants[${index}][person_in_charge]`" x-model="row.person_in_charge" placeholder="Dr. Jarwoko, M.Pd." class="w-full text-xs rounded-xl border-gray-200 py-2">
                                            </td>
                                        </template>

                                        <!-- Kolom Keterangan (Model 5 atau saat checkbox aktif) -->
                                        <template x-if="showKeterangan || selectedModel === 'model_5'">
                                            <td class="py-3 px-4">
                                                <input type="text" :name="`participants[${index}][keterangan]`" x-model="row.keterangan" placeholder="SPMI / LITNUM / DIGITALISASI..." class="w-full text-xs font-bold rounded-xl border-gray-200 py-2">
                                            </td>
                                        </template>

                                        <!-- Hapus Baris -->
                                        <td class="py-3 px-4 text-center">
                                            <template x-if="participants.length > 1 && selectedModel !== 'model_1'">
                                                <button type="button" @click="removeRow(index)" class="text-red-500 hover:text-red-700 font-bold p-1 rounded-lg hover:bg-red-50" title="Hapus Baris">
                                                    ✕
                                                </button>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 5. PENANDATANGAN & TOMBOL AKSI -->
                <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] border border-gray-100 shadow-xl space-y-6 mt-6">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-purple-600 bg-purple-50 px-3 py-1 rounded-full border border-purple-100">LANGKAH 5</span>
                        <h3 class="text-xl font-black text-sipega-navy mt-2">Penandatangan & Eksekusi Surat</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">Nama Pejabat Penandatangan</label>
                            <input type="text" name="signatory_name" value="{{ old('signatory_name', $defaultSignatory['name']) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">NIP Pejabat</label>
                            <input type="text" name="signatory_nip" value="{{ old('signatory_nip', $defaultSignatory['nip']) }}" class="w-full text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>
                    </div>

                    <!-- Opsi Tanggal Tanda Tangan (Auto Sesuai Tanggal Buat & Manual di Input) -->
                    <div class="pt-4 border-t border-gray-100">
                        <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                            Tanggal Tanda Tangan Surat
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <label :class="signatureDateType === 'auto' ? 'border-sipega-orange bg-orange-50/40 text-orange-950' : 'border-gray-200 bg-white text-gray-700'" class="flex items-start gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition">
                                <input type="radio" name="signature_date_type" value="auto" x-model="signatureDateType" class="mt-0.5 text-sipega-orange focus:ring-sipega-orange">
                                <div>
                                    <span class="text-xs font-black block">📅 Otomatis (Sesuai Tanggal Buat)</span>
                                    <span class="text-[11px] text-gray-500 font-medium">Otomatis mengikuti tanggal dibuatnya surat: <b class="text-sipega-orange">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</b></span>
                                </div>
                            </label>

                            <label :class="signatureDateType === 'manual' ? 'border-sipega-orange bg-orange-50/40 text-orange-950' : 'border-gray-200 bg-white text-gray-700'" class="flex items-start gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition">
                                <input type="radio" name="signature_date_type" value="manual" x-model="signatureDateType" class="mt-0.5 text-sipega-orange focus:ring-sipega-orange">
                                <div>
                                    <span class="text-xs font-black block">✍️ Manual (Input Tanggal Sendiri)</span>
                                    <span class="text-[11px] text-gray-500 font-medium">Pilih tanggal tanda tangan secara bebas</span>
                                </div>
                            </label>
                        </div>

                        <!-- Input Tanggal Manual saat mode 'manual' dipilih -->
                        <div x-show="signatureDateType === 'manual'" x-transition class="p-4 bg-gray-50 rounded-2xl border border-gray-200">
                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                Masukkan Tanggal Tanda Tangan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="signed_at" 
                                   x-model="signedAt"
                                   :required="signatureDateType === 'manual'"
                                   class="w-full sm:w-1/2 text-xs font-bold rounded-2xl border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange px-4 py-3">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row justify-end items-center gap-4">
                        <!-- Hidden action_type -->
                        <input type="hidden" name="action_type" id="actionType" value="save">

                        <a href="{{ route('letters.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-black text-xs uppercase tracking-widest rounded-2xl transition">
                            Batal
                        </a>

                        <!-- Tombol Simpan Draft -->
                        <button type="submit" onclick="document.getElementById('actionType').value='save'" class="px-6 py-3 bg-sipega-navy hover:bg-black text-white font-black text-xs uppercase tracking-widest rounded-2xl transition shadow-lg flex items-center gap-2">
                            <span>💾</span> Simpan Sebagai Draft
                        </button>

                        <!-- Tombol Simpan & Approve -->
                        <button type="submit" onclick="document.getElementById('actionType').value='approve'" class="px-8 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xs uppercase tracking-widest rounded-2xl transition shadow-xl shadow-emerald-600/20 flex items-center gap-2">
                            <span>✅</span> Simpan & Setujui (Approve)
                        </button>
                    </div>
                </div>

                <!-- Modal Peringatan Bentrok Tugas -->
                <div x-show="conflictModal.open" 
                     style="display: none;"
                     class="fixed inset-0 z-50 overflow-y-auto"
                     aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Backdrop -->
                        <div x-show="conflictModal.open" 
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
                             @click="conflictModal.open = false"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <!-- Modal Content Box -->
                        <div x-show="conflictModal.open" 
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-red-100">
                            
                            <div class="bg-gradient-to-r from-red-600 to-rose-600 px-6 py-4 flex items-center justify-between text-white">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">🚫</span>
                                    <div>
                                        <h4 class="text-sm font-black uppercase tracking-wider">Pegawai Sedang Bertugas!</h4>
                                        <p class="text-[11px] text-red-100 font-medium">Sistem Penunjukan Ketat Bebas Bentrok</p>
                                    </div>
                                </div>
                                <button type="button" @click="conflictModal.open = false" class="text-white/80 hover:text-white font-black text-lg">✕</button>
                            </div>

                            <div class="p-6 space-y-4">
                                <template x-if="conflictModal.data">
                                    <div class="space-y-4">
                                        <!-- Profil Pegawai -->
                                        <div class="p-4 bg-red-50 rounded-2xl border border-red-100 flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-black text-lg border-2 border-red-200">
                                                👤
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-black text-gray-900" x-text="conflictModal.data.user_name"></h5>
                                                <p class="text-xs text-gray-500 font-mono">NIP: <span x-text="conflictModal.data.user_nip"></span></p>
                                                <span class="inline-block mt-1 px-2.5 py-0.5 bg-red-600 text-white font-black text-[9px] uppercase tracking-wider rounded-full">
                                                    Sedang Dinas / Bertugas Aktif
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Detail Surat Tugas yang Bertabrakan -->
                                        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-200 space-y-2 text-xs">
                                            <div class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-1">Detail Surat Tugas Aktif:</div>
                                            <div class="flex justify-between py-1 border-b border-gray-100">
                                                <span class="text-gray-500 font-medium">No. Surat Tugas</span>
                                                <span class="font-bold text-gray-800" x-text="conflictModal.data.letter_number"></span>
                                            </div>
                                            <div class="flex justify-between py-1 border-b border-gray-100">
                                                <span class="text-gray-500 font-medium">Kegiatan</span>
                                                <span class="font-bold text-gray-800 text-right ml-4" x-text="conflictModal.data.title"></span>
                                            </div>
                                            <div class="flex justify-between py-1 border-b border-gray-100">
                                                <span class="text-gray-500 font-medium">Rentang Tanggal</span>
                                                <span class="font-bold text-red-600" x-text="conflictModal.data.date_range"></span>
                                            </div>
                                            <div class="flex justify-between py-1">
                                                <span class="text-gray-500 font-medium">Lokasi</span>
                                                <span class="font-bold text-gray-800 text-right ml-4" x-text="conflictModal.data.location"></span>
                                            </div>
                                        </div>

                                        <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 flex items-start gap-2">
                                            <span class="text-sm">ℹ️</span>
                                            <p class="leading-relaxed">
                                                Pegawai ini <b>tidak dapat dipilih</b> karena surat tugas terkait telah <b>Disetujui (Approved)</b> dan tanggal tugas beririsan dengan tanggal kegiatan yang Anda tentukan.
                                            </p>
                                        </div>
                                    </div>
                                </template>

                                <div class="pt-2 flex justify-end">
                                    <button type="button" @click="conflictModal.open = false" class="px-6 py-2.5 bg-gray-900 hover:bg-black text-white text-xs font-black uppercase tracking-wider rounded-xl transition shadow-md">
                                        Mengerti & Batalkan Pilihan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>

    <!-- SCRIPT AUTO-FILL & INTERAKSI MODEL -->
    <script>
        // Master Data Pegawai dari Server
        const allUsersData = @json($users);

        function suratTugasApp() {
            return {
                selectedModel: '{{ old('st_model', 'model_1') }}',
                category: '{{ old('category', 'DLK') }}',
                dateStart: '{{ old('date_start', date('Y-m-d')) }}',
                dateEnd: '{{ old('date_end', date('Y-m-d')) }}',
                locationText: '{{ old('location', '') }}',
                showKeterangan: {{ old('show_keterangan') ? 'true' : 'false' }},
                useDipa: {{ old('dipa_source') ? 'true' : 'false' }},
                dipaSource: '{{ old('dipa_source', '') }}',
                signatureDateType: '{{ old('signature_date_type', 'auto') }}',
                signedAt: '{{ old('signed_at', date('Y-m-d')) }}',
                busyUsers: {},
                isLoadingConflicts: false,
                conflictModal: {
                    open: false,
                    data: null
                },
                participants: [
                    {
                        user_id: '',
                        nip: '',
                        golongan: '',
                        position: '',
                        custom_role: 'Narasumber',
                        keterangan: '',
                        city_destination: '',
                        venue: '',
                        execution_dates: '',
                        person_in_charge: 'Dr. Jarwoko, M.Pd.'
                    }
                ],

                init() {
                    if (this.selectedModel === 'model_5') {
                        this.showKeterangan = true;
                    }
                    this.fetchConflicts();
                },

                async fetchConflicts() {
                    if (!this.dateStart) return;
                    this.isLoadingConflicts = true;
                    try {
                        const params = new URLSearchParams({
                            date_start: this.dateStart,
                            date_end: this.dateEnd || this.dateStart,
                        });
                        const res = await fetch(`{{ route('letters.check_conflicts') }}?${params.toString()}`);
                        const data = await res.json();
                        if (data.success) {
                            this.busyUsers = data.conflicts || {};
                        }
                    } catch (e) {
                        console.error('Error fetching conflicts:', e);
                    } finally {
                        this.isLoadingConflicts = false;
                    }
                },

                hasSelectedConflict() {
                    return this.participants.some(p => p.user_id && this.busyUsers[p.user_id]);
                },

                handleModelChange(model) {
                    if (model === 'model_1') {
                        // Batasi 1 orang
                        if (this.participants.length > 1) {
                            this.participants = [this.participants[0]];
                        }
                    } else if (model === 'model_3') {
                        // Otomatis tempat Daring jika kosong
                        if (!this.locationText || this.locationText === '') {
                            this.locationText = 'Daring';
                        }
                    } else if (model === 'model_5') {
                        this.showKeterangan = true;
                    }
                },

                addRow() {
                    this.participants.push({
                        user_id: '',
                        nip: '',
                        golongan: '',
                        position: '',
                        custom_role: 'Narasumber',
                        keterangan: '',
                        city_destination: '',
                        venue: '',
                        execution_dates: '',
                        person_in_charge: 'Dr. Jarwoko, M.Pd.'
                    });
                },

                removeRow(idx) {
                    this.participants.splice(idx, 1);
                },

                onUserSelect(idx) {
                    const selectedId = this.participants[idx].user_id;
                    if (!selectedId) {
                        this.participants[idx].nip = '';
                        this.participants[idx].golongan = '';
                        this.participants[idx].position = '';
                        return;
                    }

                    // Deteksi jika pegawai sedang bertugas di rentang tanggal ini
                    if (this.busyUsers[selectedId]) {
                        const conflict = this.busyUsers[selectedId];
                        this.conflictModal = {
                            open: true,
                            data: conflict
                        };
                        // Reset pilihan pada baris ini agar tidak dapat dipilih
                        this.participants[idx].user_id = '';
                        this.participants[idx].nip = '';
                        this.participants[idx].golongan = '';
                        this.participants[idx].position = '';
                        return;
                    }

                    const found = allUsersData.find(u => u.id == selectedId);
                    if (found) {
                        this.participants[idx].nip = (found.nip && found.nip !== '-') ? found.nip : '';
                        this.participants[idx].golongan = found.golongan || 'Pembina, IV/a';
                        this.participants[idx].position = found.position || 'Widyaprada Ahli Madya';
                    } else {
                        this.participants[idx].nip = '';
                        this.participants[idx].golongan = '';
                        this.participants[idx].position = '';
                    }
                }
            }
        }
    </script>
</x-app-layout>
