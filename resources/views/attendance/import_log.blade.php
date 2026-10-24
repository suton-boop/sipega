<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('attendance.index') }}" class="text-xs font-black text-gray-400 hover:text-sipega-navy uppercase tracking-widest transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Daftar Hadir
                    </a>
                    <span class="text-gray-300">/</span>
                    <span class="text-xs font-black text-sipega-orange uppercase tracking-widest">Import Log</span>
                </div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight tracking-tight uppercase">
                    📥 Import Log Daftar Hadir
                </h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">
                    Sinkronisasi data <b>Laporan Log Transaksi</b> (Mesin Presensi / kehadiran.kemendikdasmen.go.id)
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('performance.recap') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 rounded-2xl text-[11px] font-black uppercase tracking-wider transition border border-emerald-200 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    Lihat Rapor Capaian
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
            @if(session('success_import'))
            <div class="bg-emerald-500 text-white p-5 rounded-3xl shadow-xl flex items-start gap-4">
                <div class="w-9 h-9 rounded-2xl bg-white/20 flex items-center justify-center shrink-0 text-xl font-black">
                    ✓
                </div>
                <div class="flex-1">
                    <h4 class="font-black text-sm uppercase tracking-wider">Sinkronisasi Berhasil!</h4>
                    <p class="text-xs text-emerald-50 mt-0.5 leading-relaxed font-medium">{{ session('success_import') }}</p>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-500 text-white p-5 rounded-3xl shadow-xl flex items-start gap-4">
                <div class="w-9 h-9 rounded-2xl bg-white/20 flex items-center justify-center shrink-0 text-xl font-black">
                    ✕
                </div>
                <div class="flex-1">
                    <h4 class="font-black text-sm uppercase tracking-wider">Gagal Memproses Data</h4>
                    <ul class="text-xs text-red-50 mt-1 list-disc list-inside space-y-0.5 font-medium">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <!-- KPI Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-black shrink-0">
                        📋
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Total Log Mesin di SIPEGA</p>
                        <p class="text-2xl font-black text-sipega-navy tracking-tight">{{ number_format($recentLogsCount, 0, ',', '.') }} <span class="text-xs text-gray-400 font-bold">Rekor</span></p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-black shrink-0">
                        📅
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Log Transaksi Terakhir</p>
                        <p class="text-base font-black text-sipega-navy tracking-tight">
                            {{ $latestLogDate ? \Carbon\Carbon::parse($latestLogDate)->translatedFormat('d F Y') : 'Belum ada data' }}
                        </p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 text-sipega-orange flex items-center justify-center text-2xl font-black shrink-0">
                        👥
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Pegawai Terdaftar (NIP)</p>
                        <p class="text-2xl font-black text-sipega-navy tracking-tight">{{ number_format($totalRegisteredEmployees, 0, ',', '.') }} <span class="text-xs text-gray-400 font-bold">Pegawai</span></p>
                    </div>
                </div>
            </div>

            <!-- Import Results Details (if session has import_result) -->
            @if(session('import_result'))
            @php $res = session('import_result'); @endphp
            <div class="bg-white rounded-[2.5rem] p-6 lg:p-8 border border-emerald-200 shadow-xl space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-100 pb-5">
                    <div>
                        <span class="text-[9px] bg-emerald-100 text-emerald-800 font-black uppercase tracking-widest px-3 py-1 rounded-full">Hasil Proses Terakhir</span>
                        <h3 class="text-xl font-black text-sipega-navy mt-1">Ringkasan Impor Log Kehadiran</h3>
                        @if($res['date_range']['min'] && $res['date_range']['max'])
                        <p class="text-xs text-gray-500 font-medium">Periode Transaksi: <b class="text-sipega-navy">{{ $res['date_range']['min'] }}</b> s.d. <b class="text-sipega-navy">{{ $res['date_range']['max'] }}</b></p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <span class="bg-slate-100 text-slate-700 px-3 py-1.5 rounded-xl">Total Pegawai di Dokumen: <b>{{ $res['total_employees'] }}</b></span>
                        <span class="bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-xl border border-emerald-100">Berhasil Disinkronkan: <b>{{ $res['matched_employees'] }}</b></span>
                        @if($res['new_employees_created'] > 0)
                        <span class="bg-blue-50 text-blue-700 px-3 py-1.5 rounded-xl border border-blue-100">Pegawai Baru Dibuat: <b>{{ $res['new_employees_created'] }}</b></span>
                        @endif
                        <span class="bg-orange-50 text-orange-700 px-3 py-1.5 rounded-xl border border-orange-100">Total Hari/Log Masuk: <b>{{ $res['total_logs_processed'] }}</b></span>
                    </div>
                </div>

                <!-- Unmapped Notice if any -->
                @if(!empty($res['unmapped_employees']) && count($res['unmapped_employees']) > 0)
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                        <h4 class="font-black text-xs uppercase tracking-wider text-amber-900">
                            {{ count($res['unmapped_employees']) }} Pegawai Belum Terdaftar di Master SIPEGA
                        </h4>
                    </div>
                    <p class="text-xs text-amber-800 mb-3 font-medium leading-relaxed">
                        Data kehadiran untuk pegawai berikut belum dapat dikaitkan karena NIP atau Nama belum tercatat di data pengguna SIPEGA. Anda dapat menambahkan pegawai tersebut melalui menu <b>Data Pegawai</b> atau mengaktifkan opsi <i>"Otomatis daftarkan pegawai baru"</i> saat impor.
                    </p>
                    <div class="max-h-40 overflow-y-auto bg-white/80 rounded-xl p-3 border border-amber-100 text-xs divide-y divide-gray-100">
                        @foreach($res['unmapped_employees'] as $un)
                        <div class="py-1.5 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-gray-800">{{ $un['nama'] }}</span>
                                <span class="text-gray-400 font-mono text-[11px] ml-2">NIP: {{ $un['nip'] }}</span>
                                @if($un['jabatan'] !== '-')
                                <span class="text-gray-400 text-[10px] italic ml-1">({{ $un['jabatan'] }})</span>
                                @endif
                            </div>
                            <span class="text-[10px] font-bold text-amber-700 bg-amber-100/70 px-2 py-0.5 rounded-full">{{ $un['logs_count'] }} transaksi</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Per-Employee Summary Table -->
                @if(!empty($res['employees_summary']) && count($res['employees_summary']) > 0)
                <div>
                    <h4 class="font-black text-xs uppercase tracking-wider text-sipega-navy mb-3">Daftar Pegawai Yang Berhasil Diperbarui:</h4>
                    <div class="overflow-x-auto border border-gray-100 rounded-2xl max-h-96 overflow-y-auto">
                        <table class="w-full text-left text-xs divide-y divide-gray-100">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-gray-500 sticky top-0">
                                <tr>
                                    <th class="py-3 px-4">No.</th>
                                    <th class="py-3 px-4">Pegawai</th>
                                    <th class="py-3 px-4">NIP</th>
                                    <th class="py-3 px-4 text-center">Hari Hadir</th>
                                    <th class="py-3 px-4 text-center">Terlambat (TL)</th>
                                    <th class="py-3 px-4 text-center">Pulang Cepat (PSW)</th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 font-medium">
                                @foreach($res['employees_summary'] as $idx => $es)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-2.5 px-4 text-gray-400 font-bold">{{ $idx + 1 }}</td>
                                    <td class="py-2.5 px-4 font-bold text-sipega-navy">
                                        {{ $es['name'] }}
                                        @if(!empty($es['position']))
                                        <p class="text-[10px] font-normal text-gray-400 leading-tight">{{ $es['position'] }}</p>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 font-mono text-gray-600">{{ $es['nip'] }}</td>
                                    <td class="py-2.5 px-4 text-center font-bold text-blue-600">{{ $es['total_days'] }} Hari</td>
                                    <td class="py-2.5 px-4 text-center">
                                        @if($es['total_tl_minutes'] > 0)
                                        <span class="bg-red-50 text-red-600 px-2 py-0.5 rounded-full font-bold text-[11px]">{{ $es['total_tl_minutes'] }} mnt</span>
                                        @else
                                        <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-center">
                                        @if($es['total_psw_minutes'] > 0)
                                        <span class="bg-orange-50 text-orange-600 px-2 py-0.5 rounded-full font-bold text-[11px]">{{ $es['total_psw_minutes'] }} mnt</span>
                                        @else
                                        <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-4 text-center">
                                        <a href="{{ route('attendance.recap', $es['user_id']) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-sipega-navy bg-slate-100 hover:bg-sipega-navy hover:text-white px-3 py-1 rounded-lg transition">
                                            <span>Rekap</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Main Import Form Card -->
            <div class="bg-white rounded-[2.5rem] p-6 lg:p-10 border border-slate-200/80 shadow-sm" x-data="{ mode: 'file', fileName: '', isDragging: false }">
                <form action="{{ route('attendance.import.process') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    <!-- Header & Mode Tabs -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-100 pb-6">
                        <div>
                            <h3 class="text-xl font-black text-sipega-navy uppercase tracking-tight">Formulir Impor Log</h3>
                            <p class="text-xs text-gray-500 font-medium">Pilih metode impor: unggah file laporan atau tempelkan langsung data teks/HTML</p>
                        </div>

                        <!-- Mode Switcher Tabs -->
                        <div class="flex bg-slate-100 p-1.5 rounded-2xl gap-1">
                            <button type="button" @click="mode = 'file'" :class="mode === 'file' ? 'bg-white text-sipega-navy shadow-sm font-black' : 'text-gray-500 hover:text-sipega-navy font-bold'" class="px-4 py-2 rounded-xl text-xs uppercase tracking-wider transition">
                                📁 Unggah File
                            </button>
                            <button type="button" @click="mode = 'paste'" :class="mode === 'paste' ? 'bg-white text-sipega-navy shadow-sm font-black' : 'text-gray-500 hover:text-sipega-navy font-bold'" class="px-4 py-2 rounded-xl text-xs uppercase tracking-wider transition">
                                📋 Tempel Teks / HTML
                            </button>
                        </div>
                    </div>

                    <!-- Method 1: File Upload -->
                    <div x-show="mode === 'file'" x-transition class="space-y-3">
                        <label class="block text-xs font-black uppercase tracking-wider text-sipega-navy">
                            Pilih File Laporan Transaksi
                        </label>
                        <label 
                            for="log_file_input"
                            @dragover.prevent="isDragging = true" 
                            @dragleave.prevent="isDragging = false" 
                            @drop.prevent="isDragging = false; if ($event.dataTransfer.files.length) { document.getElementById('log_file_input').files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0].name; document.getElementById('fileNameDisplay').innerText = 'File Terpilih: ' + fileName; document.getElementById('fileSelectedBadge').style.display = 'inline-flex'; }"
                            :class="isDragging ? 'border-sipega-orange bg-orange-50/40' : 'border-gray-200 hover:border-sipega-orange hover:bg-slate-50/50'"
                            class="block border-2 border-dashed rounded-3xl p-8 lg:p-12 text-center transition cursor-pointer relative">
                            
                            <input 
                                type="file" 
                                id="log_file_input"
                                name="log_file" 
                                accept=".html,.htm,.xls,.xlsx,.csv,.txt" 
                                class="sr-only" 
                                onchange="var name = this.files[0] ? this.files[0].name : ''; document.getElementById('fileNameDisplay').innerText = name ? 'File Terpilih: ' + name : 'Klik di sini atau seret file ke area ini'; document.getElementById('fileSelectedBadge').style.display = name ? 'inline-flex' : 'none';"
                                @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                            
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <div class="w-16 h-16 rounded-2xl bg-orange-50 text-sipega-orange flex items-center justify-center text-3xl font-black shadow-inner">
                                    📥
                                </div>
                                <div class="space-y-1">
                                    <p id="fileNameDisplay" class="text-sm font-black text-sipega-navy" x-text="fileName ? 'File Terpilih: ' + fileName : 'Klik di sini atau seret file ke area ini'">Klik di sini atau seret file ke area ini</p>
                                    <p class="text-xs text-gray-400 font-medium">Mendukung format: <b class="text-gray-600">.HTML, .HTM, .XLS, .XLSX, .CSV, .TXT</b> (Maksimal 20MB)</p>
                                </div>

                                <div id="fileSelectedBadge" style="display: none;" class="items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                                    ✓ File Siap Diimpor
                                </div>

                                <div class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-wider text-white bg-sipega-navy hover:bg-black px-6 py-3 rounded-2xl shadow-md transition pointer-events-none">
                                    <svg class="w-4 h-4 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Pilih File Dari Komputer</span>
                                </div>
                            </div>
                        </label>
                        <p class="text-[11px] text-gray-400 font-medium italic">
                            *Tips: Jika Anda mengunduh laporan dari portal presensi Kemendikdasmen berupa halaman web atau file Excel (.xls), Anda dapat langsung mengunggah file tersebut di sini.
                        </p>
                    </div>

                    <!-- Method 2: Paste Raw HTML / Text -->
                    <div x-show="mode === 'paste'" x-transition class="space-y-3" style="display: none;">
                        <div class="flex justify-between items-center">
                            <label class="block text-xs font-black uppercase tracking-wider text-sipega-navy">
                                Tempelkan (Paste) Kode HTML atau Teks Laporan
                            </label>
                            <span class="text-[10px] font-bold text-gray-400">Ctrl+V di dalam kotak di bawah</span>
                        </div>
                        <textarea 
                            name="raw_content" 
                            rows="10" 
                            placeholder="Salin (Ctrl+C) seluruh isi tabel atau kode HTML dari halaman kehadiran.kemendikdasmen.go.id, lalu tempel (Ctrl+V) di sini..."
                            class="w-full text-xs font-mono p-4 rounded-2xl border-2 border-gray-200 focus:border-sipega-orange focus:ring-sipega-orange transition leading-relaxed"></textarea>
                        <p class="text-[11px] text-gray-400 font-medium italic">
                            *Tips: Anda dapat membuka tampilan cetak di browser, tekan <b>Ctrl+U</b> (View Source) atau salin teks tabelnya, lalu tempelkan langsung di kotak ini.
                        </p>
                    </div>

                    <!-- Parameter & Pengaturan Jam Kerja -->
                    <div class="bg-slate-50 p-6 rounded-3xl border border-slate-200/70 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">⚙️</span>
                            <h4 class="font-black text-xs uppercase tracking-wider text-sipega-navy">Parameter Perhitungan Jam Kerja</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-gray-500 tracking-wider">Jam Masuk Standar</label>
                                <input type="time" name="office_start_time" value="07:30" class="w-full text-xs font-bold p-3 rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy bg-white">
                                <span class="text-[9px] text-gray-400 italic">Lewat dari jam ini = TL (Terlambat)</span>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-gray-500 tracking-wider">Pulang (Senin - Kamis)</label>
                                <input type="time" name="office_end_weekday" value="16:00" class="w-full text-xs font-bold p-3 rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy bg-white">
                                <span class="text-[9px] text-gray-400 italic">Pulang sebelum jam ini = PSW</span>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-gray-500 tracking-wider">Pulang Hari Jumat</label>
                                <input type="time" name="office_end_friday" value="16:30" class="w-full text-xs font-bold p-3 rounded-xl border-gray-200 focus:border-sipega-navy focus:ring-sipega-navy bg-white">
                                <span class="text-[9px] text-gray-400 italic">Standar ASN: 16:30 WITA</span>
                            </div>
                        </div>

                        <!-- Checkbox Auto-Create User -->
                        <div class="pt-2 border-t border-slate-200/60">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input type="checkbox" name="auto_create_user" value="1" checked class="mt-0.5 rounded text-sipega-orange focus:ring-sipega-orange border-gray-300">
                                <div class="text-xs">
                                    <span class="font-bold text-gray-800 group-hover:text-sipega-navy">Otomatis daftarkan akun pegawai baru jika NIP belum ada di SIPEGA</span>
                                    <p class="text-[10px] text-gray-400 font-medium">Jika dicentang, pegawai yang belum tercatat di sistem akan dibuatkan akun otomatis dengan NIP sebagai password default sehingga log kehadirannya langsung tersimpan.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
                        <a href="{{ route('attendance.index') }}" class="text-xs font-bold text-gray-400 hover:text-gray-600 transition">
                            ← Kembali ke Daftar Hadir
                        </a>

                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 bg-sipega-navy hover:bg-black text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-xl transition-all transform hover:-translate-y-0.5 cursor-pointer">
                            <svg class="w-4 h-4 text-sipega-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                            <span>Mulai Proses Sinkronisasi Log</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Petunjuk Penggunaan -->
            <div class="bg-white rounded-[2.5rem] p-6 lg:p-8 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center gap-2">
                    <span class="text-base">💡</span>
                    <h4 class="font-black text-xs uppercase tracking-wider text-sipega-navy">Panduan Mengambil Data dari Portal Kehadiran Kemendikdasmen:</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs font-medium text-gray-600">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="w-6 h-6 rounded-full bg-sipega-navy text-white text-[10px] font-black flex items-center justify-center mb-2">1</span>
                        <p class="font-bold text-sipega-navy">Buka Portal Presensi</p>
                        <p class="text-[11px] text-gray-500">Login ke situs <code class="text-sipega-navy font-mono text-[10px]">kehadiran.kemendikdasmen.go.id</code> dengan akun instansi.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="w-6 h-6 rounded-full bg-sipega-navy text-white text-[10px] font-black flex items-center justify-center mb-2">2</span>
                        <p class="font-bold text-sipega-navy">Pilih Menu Log</p>
                        <p class="text-[11px] text-gray-500">Buka menu <b>Laporan &gt; Laporan Log Transaksi</b>, tentukan rentang tanggal yang ingin diimpor.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="w-6 h-6 rounded-full bg-sipega-navy text-white text-[10px] font-black flex items-center justify-center mb-2">3</span>
                        <p class="font-bold text-sipega-navy">Simpan / Salin Data</p>
                        <p class="text-[11px] text-gray-500">Klik cetak/tampilkan, lalu simpan file (<kbd class="text-[10px] bg-white border px-1 rounded">Ctrl+S</kbd>) atau salin kode/teks tabelnya.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="w-6 h-6 rounded-full bg-sipega-orange text-white text-[10px] font-black flex items-center justify-center mb-2">4</span>
                        <p class="font-bold text-sipega-navy">Impor ke SIPEGA</p>
                        <p class="text-[11px] text-gray-500">Unggah file atau tempelkan di form di atas. Sistem otomatis memetakan NIP, menghitung keterlambatan, dan menyinkronkan Surat Tugas.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
