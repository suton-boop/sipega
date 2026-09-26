<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 whitespace-nowrap" x-data="{ open: false }">
            <div>
                <h2 class="font-extrabold text-2xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Kontrol Hak Akses
                </h2>
                <p class="text-[10px] font-bold text-sipega-orange uppercase tracking-[0.3em] mt-1">SIPEGA RBAC &bull; DEVICE BINDING &bull; MANAJEMEN PEGAWAI</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <!-- Tombol Toggle Panel Import & Template -->
                <button @click="$dispatch('toggle-import')" class="bg-gradient-to-r from-sipega-orange to-amber-500 hover:brightness-110 text-white font-black py-2.5 px-5 rounded-2xl transition shadow-lg shadow-orange-500/20 text-[10px] uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span>Import & Template</span>
                </button>

                <!-- Tombol Tambah Pegawai Manual -->
                <button @click="open = true" class="bg-sipega-navy hover:bg-black text-white font-black py-2.5 px-5 rounded-2xl transition shadow text-[10px] uppercase tracking-widest flex items-center gap-2">
                    <span class="text-lg leading-none">+</span> Tambah Pegawai
                </button>

                <a href="{{ route('dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-400 hover:text-gray-800 font-black py-2.5 px-5 rounded-2xl transition shadow text-[10px] uppercase tracking-widest border border-gray-200">
                    Kembali ke Dasbor
                </a>
            </div>

            <!-- Modal Form Tambah User Manual -->
            <div x-show="open" 
                 class="fixed inset-0 z-50 overflow-y-auto" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-sipega-navy/80 backdrop-blur-sm" @click="open = false"></div>

                    <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[3rem] shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="px-8 pt-10 pb-12 bg-white">
                            <h3 class="text-3xl font-black text-sipega-navy mb-2 tracking-tighter uppercase">Pendaftaran Pegawai</h3>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-8">Input Data Pegawai Secara Manual</p>

                            <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                                @csrf
                                <div class="flex flex-col items-center mb-6">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Foto Pegawai</label>
                                    <input type="file" name="photo" class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-gray-100 file:text-sipega-navy hover:file:bg-gray-200 cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Nama Lengkap</label>
                                    <input type="text" name="name" required class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-sipega-navy" placeholder="Nama Lengkap dan Gelar">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Alamat Email</label>
                                        <input type="email" name="email" required class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-sipega-navy" placeholder="nama@bpmpkaltim.id">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Nomor Induk Pegawai (NIP)</label>
                                        <input type="text" name="nip" required class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-sipega-navy" placeholder="18 Digit NIP">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Hak Akses (Role)</label>
                                    <select name="role" required class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-sipega-navy uppercase tracking-widest">
                                        <option value="Pegawai">Pegawai</option>
                                        <option value="Operator">Operator</option>
                                        <option value="Kasubag">Kasubag</option>
                                        <option value="Pimpinan">Pimpinan</option>
                                        <option value="Admin">Admin</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Jabatan</label>
                                        <input type="text" name="position" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold focus:ring-2 focus:ring-sipega-navy" placeholder="Widyaprada Ahli Madya">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Gugus Mutu</label>
                                        <select name="gugus_mutu" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-xs font-bold focus:ring-2 focus:ring-sipega-navy">
                                            <option value="">-- Pilih Gugus Mutu --</option>
                                            <option value="GM 1 - PAUD & Kesetaraan">GM 1 - PAUD & Kesetaraan</option>
                                            <option value="GM 2 - Sekolah Dasar (SD)">GM 2 - Sekolah Dasar (SD)</option>
                                            <option value="GM 3 - SMP">GM 3 - SMP</option>
                                            <option value="GM 4 - SMA, SMK & SLB">GM 4 - SMA, SMK & SLB</option>
                                            <option value="GM 5 - Tata Usaha & Kemitraan">GM 5 - Tata Usaha & Kemitraan</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Password Default</label>
                                    <input type="password" name="password" required value="12345678" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-sipega-navy">
                                    <p class="text-[9px] text-gray-400 mt-2 font-bold italic">*Password awal yang digunakan pegawai untuk masuk pertama kali</p>
                                </div>

                                <div class="pt-4 flex gap-3">
                                    <button type="button" @click="open = false" class="flex-1 bg-gray-100 text-gray-400 font-extrabold py-5 rounded-3xl uppercase tracking-widest text-xs hover:bg-gray-200 transition">Batal</button>
                                    <button type="submit" class="flex-2 bg-sipega-orange text-white font-extrabold py-5 px-10 rounded-3xl uppercase tracking-widest text-xs shadow-xl shadow-orange-500/20 hover:bg-orange-600 transition">Daftarkan 🚀</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen" 
         x-data="{ showImport: {{ session('error') || request('open_import') ? 'true' : 'false' }} }" 
         @toggle-import.window="showImport = !showImport">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-50 text-green-700 p-6 rounded-3xl mb-8 font-bold border border-green-100 flex items-center gap-3 shadow-sm">
                    <span class="bg-green-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black italic">✓</span> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 text-red-700 p-6 rounded-3xl mb-8 font-bold border border-red-100 flex items-center gap-3 shadow-sm">
                    <span class="bg-red-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black italic">!</span> {{ session('error') }}
                </div>
            @endif

            <!-- 1. TOGGLE BAR: IMPORT & TEMPLATE (Bisa di-hidden & dimunculkan) -->
            <div class="flex items-center justify-between mb-6 bg-white py-4 px-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full" :class="showImport ? 'bg-sipega-orange animate-pulse' : 'bg-gray-300'"></span>
                    <span class="text-xs font-black text-sipega-navy uppercase tracking-wider">
                        Fitur Import Pegawai & Template Excel
                    </span>
                </div>
                <button @click="showImport = !showImport" 
                    class="text-[10px] font-black uppercase tracking-widest py-2.5 px-5 rounded-2xl transition-all flex items-center gap-2 shadow-sm"
                    :class="showImport ? 'bg-gray-100 hover:bg-gray-200 text-gray-600' : 'bg-sipega-navy hover:bg-black text-white'">
                    <svg class="w-3.5 h-3.5 transition-transform duration-300" :class="showImport ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    <span x-text="showImport ? 'Sembunyikan Panel Import' : 'Buka Panel Import & Template'"></span>
                </button>
            </div>

            <!-- PANEL IMPORT & TEMPLATE EXCEL (COLLAPSIBLE) -->
            <div x-show="showImport" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4"
                 class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
                
                <!-- Card 1: Form Import Excel -->
                <div class="lg:col-span-7 bg-white p-8 rounded-[2.5rem] shadow-xl border border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-black text-sipega-navy flex items-center gap-3">
                                📥 Import Data Pegawai
                            </h3>
                            <span class="px-3 py-1 bg-blue-50 text-sipega-navy text-[9px] font-black rounded-full uppercase tracking-widest border border-blue-100">
                                Excel / CSV
                            </span>
                        </div>
                        
                        <!-- Alert Box: Ketentuan Login Pegawai Baru -->
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 text-amber-900 text-xs">
                            <div class="flex items-start gap-3">
                                <span class="text-lg leading-none">🔑</span>
                                <div>
                                    <strong class="font-extrabold block uppercase tracking-wider text-[11px] mb-0.5">Ketentuan Akun Pegawai Hasil Import:</strong>
                                    <p class="text-[11px] text-amber-800 leading-relaxed">
                                        Pegawai otomatis dapat masuk ke aplikasi menggunakan:
                                        <br>• <strong>Username:</strong> <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono font-bold">NIP Pegawai</code>
                                        <br>• <strong>Password Default:</strong> <code class="bg-amber-100 px-1.5 py-0.5 rounded font-mono font-bold">NIP Pegawai</code>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div class="border-2 border-dashed border-gray-200 rounded-2xl p-6 text-center hover:border-sipega-orange transition-colors bg-gray-50/50">
                                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-sipega-navy file:text-white hover:file:bg-black cursor-pointer" required>
                                <p class="text-[10px] text-gray-400 mt-2 font-bold uppercase tracking-wider">Format yang didukung: .xlsx, .xls, .csv</p>
                            </div>
                            
                            <button type="submit" class="w-full bg-sipega-navy text-white text-[11px] font-black py-4 px-8 rounded-2xl shadow-lg hover:bg-black transition-all hover:-translate-y-0.5 uppercase tracking-widest flex items-center justify-center gap-2">
                                <span>Mulai Upload & Import Data</span> 🚀
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Card 2: Unduh Template Excel & Panduan Kolom -->
                <div class="lg:col-span-5 bg-gradient-to-br from-[#003366] to-[#001a33] p-8 rounded-[2.5rem] shadow-xl text-white flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-black flex items-center gap-3">📋 Template Data</h3>
                            <span class="px-3 py-1 bg-white/10 text-white text-[9px] font-black rounded-full uppercase tracking-widest border border-white/20">Panduan</span>
                        </div>
                        
                        <p class="text-xs text-white/80 mb-5 leading-relaxed">
                            Unduh template resmi agar susunan kolom sesuai dengan sistem. NIP diformat khusus agar tidak terpotong oleh Excel.
                        </p>

                        <!-- Tabel Ringkasan Kolom Template -->
                        <div class="bg-white/10 rounded-2xl p-4 mb-6 border border-white/10 text-[11px]">
                            <div class="font-extrabold uppercase tracking-widest text-[9px] text-sipega-orange mb-2">Struktur Kolom Template:</div>
                            <div class="grid grid-cols-2 gap-y-1.5 text-white/90 text-[10px]">
                                <div>1. <strong>Nama</strong> (Lengkap + Gelar)</div>
                                <div>2. <strong>NIP</strong> (Angka 18 digit)</div>
                                <div>3. <strong>Jabatan</strong> (Struktural/JFT)</div>
                                <div>4. <strong>Golongan</strong> (Cth: III/a)</div>
                                <div>5. <strong>KJ</strong> (Kelas Jabatan, Cth: 8)</div>
                                <div>6. <strong>Role</strong> (Pegawai/Operator)</div>
                                <div class="col-span-2">7. <strong>Email</strong> (Opsional, otomatis diisi jika kosong)</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('users.template') }}" class="flex-1 text-center bg-sipega-orange text-white text-[10px] font-black py-4 px-4 rounded-2xl shadow-lg hover:bg-orange-600 transition-all hover:-translate-y-0.5 uppercase tracking-widest flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Unduh Excel (.xlsx)</span>
                        </a>
                        <a href="{{ route('users.template', ['format' => 'csv']) }}" class="text-center bg-white/10 hover:bg-white/20 text-white text-[10px] font-black py-4 px-4 rounded-2xl border border-white/20 transition-all uppercase tracking-widest">
                            CSV
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. FILTER & PENCARIAN PEGAWAI -->
            <div class="bg-white p-6 rounded-[2.5rem] shadow-xl border border-gray-100 mb-8">
                <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <!-- Input Pencarian -->
                    <div class="md:col-span-5 relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama, NIP, Email, atau Jabatan..." class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-xs font-bold text-sipega-navy focus:ring-2 focus:ring-sipega-navy">
                    </div>

                    <!-- Filter Peran (Role) -->
                    <div class="md:col-span-2">
                        <select name="role" onchange="this.form.submit()" class="w-full py-3 px-4 bg-gray-50 border-none rounded-2xl text-xs font-bold text-sipega-navy focus:ring-2 focus:ring-sipega-navy uppercase tracking-wider">
                            <option value="all">Semua Peran</option>
                            <option value="Pegawai" {{ request('role') == 'Pegawai' ? 'selected' : '' }}>Pegawai</option>
                            <option value="Operator" {{ request('role') == 'Operator' ? 'selected' : '' }}>Operator</option>
                            <option value="Kasubag" {{ request('role') == 'Kasubag' ? 'selected' : '' }}>Kasubag</option>
                            <option value="Pimpinan" {{ request('role') == 'Pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                            <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div class="md:col-span-2">
                        <select name="status" onchange="this.form.submit()" class="w-full py-3 px-4 bg-gray-50 border-none rounded-2xl text-xs font-bold text-sipega-navy focus:ring-2 focus:ring-sipega-navy uppercase tracking-wider">
                            <option value="all">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>🟢 Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>⚪ Nonaktif</option>
                        </select>
                    </div>

                    <!-- Filter Device Binding -->
                    <div class="md:col-span-2">
                        <select name="device" onchange="this.form.submit()" class="w-full py-3 px-4 bg-gray-50 border-none rounded-2xl text-xs font-bold text-sipega-navy focus:ring-2 focus:ring-sipega-navy uppercase tracking-wider">
                            <option value="all">Semua Perangkat</option>
                            <option value="locked" {{ request('device') == 'locked' ? 'selected' : '' }}>🔒 Locked (HP)</option>
                            <option value="unbound" {{ request('device') == 'unbound' ? 'selected' : '' }}>🔓 Unbound</option>
                        </select>
                    </div>

                    <!-- Tombol Aksi Filter & Reset -->
                    <div class="md:col-span-1 flex gap-2">
                        <button type="submit" class="w-full bg-sipega-navy hover:bg-black text-white p-3 rounded-2xl text-xs font-black transition flex items-center justify-center shadow" title="Terapkan Filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        </button>
                        @if(request()->hasAny(['search', 'role', 'status', 'device']))
                        <a href="{{ route('users.index') }}" class="bg-red-50 hover:bg-red-100 text-red-600 p-3 rounded-2xl text-xs font-black transition flex items-center justify-center border border-red-100" title="Reset Filter">
                            ✕
                        </a>
                        @endif
                    </div>
                </form>
                
                <!-- Ringkasan Filter & Jumlah Data -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mt-4 pt-4 border-t border-gray-100 text-[11px] text-gray-500 font-bold">
                    <div>
                        Menampilkan <strong class="text-sipega-navy">{{ count($users) }}</strong> data pegawai
                        @if(request()->hasAny(['search', 'role', 'status', 'device']))
                            <span class="text-sipega-orange font-black ml-1">(Hasil Filter Aktif)</span>
                        @endif
                    </div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-widest">
                        Total Terdaftar: <strong class="text-sipega-navy">{{ $totalUsers ?? count($users) }}</strong> Pegawai
                    </div>
                </div>
            </div>

            <!-- 3. TABEL DATA PEGAWAI & KENDALI OPERASIONAL -->
            <div class="bg-white overflow-hidden shadow-2xl rounded-[40px] border border-gray-100 p-2 md:p-6 lg:p-10">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="py-6 px-6 font-black text-[10px] text-gray-400 uppercase tracking-widest rounded-tl-3xl">Pegawai Terdaftar</th>
                                <th class="py-6 px-6 font-black text-[10px] text-gray-400 uppercase tracking-widest">Identitas NIP & Jabatan</th>
                                <th class="py-6 px-6 font-black text-[10px] text-gray-400 uppercase tracking-widest text-center">Status</th>
                                <th class="py-6 px-6 font-black text-[10px] text-gray-400 uppercase tracking-widest text-center rounded-tr-3xl">Kendali Operasional</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($users as $u)
                                <tr class="hover:bg-gray-50 transition duration-150 group">
                                    <!-- Kolom Identitas Pegawai -->
                                    <td class="py-8 px-6 align-top">
                                        <div class="flex items-center gap-4">
                                            @if($u->photo)
                                                <img src="{{ asset('storage/' . $u->photo) }}" class="w-12 h-12 object-cover rounded-2xl border-2 border-sipega-navy shadow-lg">
                                            @else
                                                <div class="w-12 h-12 bg-sipega-navy flex items-center justify-center rounded-2xl text-white font-black text-lg shadow-lg">
                                                    {{ substr($u->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-black text-sipega-navy text-lg leading-none mb-1">{{ $u->name }}</div>
                                                <div class="text-xs text-gray-400 font-bold tracking-tight lowercase">{{ $u->email }}</div>
                                                <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-yellow-400/10 text-yellow-700 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                    {{ $u->role }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kolom NIP, Jabatan & Golongan -->
                                    <td class="py-8 px-6 align-top">
                                        <div class="text-sm font-black text-gray-700 tracking-tighter mb-1">
                                            {{ $u->nip ?? '-' }}
                                        </div>
                                        @if($u->position)
                                            <div class="text-xs font-bold text-gray-600 leading-tight mb-1">
                                                {{ $u->position }}
                                            </div>
                                        @endif
                                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                            @if($u->gugus_mutu)
                                                <span class="px-2.5 py-0.5 bg-blue-50 text-blue-800 border border-blue-200 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                                    🏛️ {{ $u->gugus_mutu }}
                                                </span>
                                            @endif
                                            @if($u->golongan)
                                                <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-600 text-[10px] font-black">Gol: {{ $u->golongan }}</span>
                                            @endif
                                            @if($u->grade)
                                                <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-600 text-[10px] font-black">KJ: {{ $u->grade }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Kolom Status Keamanan & Keaktifan -->
                                    <td class="py-8 px-6 text-center align-top">
                                        <div class="flex flex-col items-center gap-3">
                                            @if($u->device_id)
                                                <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-red-100 text-red-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-red-200" title="Terkunci di HP">
                                                    🔒 Locked
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-green-100 text-green-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-green-200">
                                                    🔓 Unbound
                                                </span>
                                            @endif
                                            
                                            <div class="flex items-center gap-2">
                                                <div class="h-2.5 w-2.5 {{ $u->is_active ? 'bg-green-500 animate-pulse' : 'bg-gray-300' }} rounded-full"></div>
                                                <span class="text-[10px] font-black uppercase tracking-widest {{ $u->is_active ? 'text-green-600' : 'text-gray-400' }}">
                                                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kolom Kendali Operasional & Hapus Pegawai -->
                                    <td class="py-8 px-6 align-top">
                                        <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-xl group-hover:border-gray-200 transition-all flex flex-col gap-4">
                                            <!-- Form Update Data & Role -->
                                            <form action="{{ route('users.update', $u->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                                                @csrf
                                                @method('PUT')
                                                
                                                <div>
                                                    <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1 px-1">Update Foto</label>
                                                    <input type="file" name="photo" class="text-[9px] text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-full file:border-0 file:bg-gray-100 file:text-sipega-navy cursor-pointer">
                                                </div>

                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1 px-1">Jabatan</label>
                                                        <input type="text" name="position" value="{{ $u->position }}" placeholder="Jabatan..." class="w-full text-xs font-bold p-2.5 bg-gray-50 rounded-xl border-none focus:ring-sipega-navy focus:bg-white">
                                                    </div>
                                                    <div>
                                                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1 px-1">Golongan</label>
                                                        <input type="text" name="golongan" value="{{ $u->golongan }}" placeholder="Contoh: IV/a" class="w-full text-xs font-bold p-2.5 bg-gray-50 rounded-xl border-none focus:ring-sipega-navy focus:bg-white">
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1 px-1">Gugus Mutu</label>
                                                    <select name="gugus_mutu" class="w-full text-xs font-bold p-2.5 bg-gray-50 rounded-xl border-none focus:ring-sipega-navy focus:bg-white">
                                                        <option value="">- Tanpa Gugus Mutu -</option>
                                                        <option value="GM 1 - PAUD & Kesetaraan" {{ $u->gugus_mutu == 'GM 1 - PAUD & Kesetaraan' ? 'selected' : '' }}>GM 1 - PAUD & Kesetaraan</option>
                                                        <option value="GM 2 - Sekolah Dasar (SD)" {{ $u->gugus_mutu == 'GM 2 - Sekolah Dasar (SD)' ? 'selected' : '' }}>GM 2 - Sekolah Dasar (SD)</option>
                                                        <option value="GM 3 - SMP" {{ $u->gugus_mutu == 'GM 3 - SMP' ? 'selected' : '' }}>GM 3 - SMP</option>
                                                        <option value="GM 4 - SMA, SMK & SLB" {{ $u->gugus_mutu == 'GM 4 - SMA, SMK & SLB' ? 'selected' : '' }}>GM 4 - SMA, SMK & SLB</option>
                                                        <option value="GM 5 - Tata Usaha & Kemitraan" {{ $u->gugus_mutu == 'GM 5 - Tata Usaha & Kemitraan' ? 'selected' : '' }}>GM 5 - Tata Usaha & Kemitraan</option>
                                                    </select>
                                                </div>

                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1 px-1">Tukar Peran</label>
                                                        <select name="role" class="w-full text-xs font-black p-3 bg-gray-50 rounded-xl border-none focus:ring-sipega-navy focus:bg-white transition-all uppercase tracking-widest">
                                                            <option value="Admin" {{ $u->role == 'Admin' ? 'selected' : '' }}>Admin</option>
                                                            <option value="Pimpinan" {{ $u->role == 'Pimpinan' ? 'selected' : '' }}>Pimpinan</option>
                                                            <option value="Kasubag" {{ $u->role == 'Kasubag' ? 'selected' : '' }}>Kasubag</option>
                                                            <option value="Operator" {{ $u->role == 'Operator' ? 'selected' : '' }}>Operator</option>
                                                            <option value="Pegawai" {{ $u->role == 'Pegawai' ? 'selected' : '' }}>Pegawai</option>
                                                        </select>
                                                    </div>
                                                    <div class="flex items-center justify-center">
                                                         <label class="flex items-center gap-3 cursor-pointer group/toggle">
                                                            <input type="checkbox" name="is_active" value="1" {{ $u->is_active ? 'checked' : '' }} class="rounded-lg text-green-600 focus:ring-green-500 w-6 h-6 border-gray-100 bg-gray-50">
                                                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 group-hover/toggle:text-green-600 transition-colors">Aktif</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer bg-red-50 p-2.5 rounded-xl border border-red-50 hover:bg-red-100 transition-colors">
                                                        <input type="checkbox" name="reset_device" value="1" class="rounded text-red-600 focus:ring-red-500 w-5 h-5 border-none">
                                                        <span class="text-[9px] font-black text-red-800 uppercase tracking-tighter leading-none">Reset HP</span>
                                                    </label>
                                                    <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer bg-orange-50 p-2.5 rounded-xl border border-orange-50 hover:bg-orange-100 transition-colors">
                                                        <input type="checkbox" name="reset_password" value="1" class="rounded text-orange-600 focus:ring-orange-500 w-5 h-5 border-none">
                                                        <span class="text-[9px] font-black text-orange-800 uppercase tracking-tighter leading-none">Reset Pass</span>
                                                    </label>
                                                </div>

                                                <button type="submit" class="w-full bg-sipega-navy hover:bg-black text-white text-[10px] font-black py-3.5 rounded-xl mt-1 shadow-lg transition-all uppercase tracking-widest hover:-translate-y-0.5">
                                                    Simpan Kendali
                                                </button>
                                            </form>

                                            <!-- MENU DELETE PEGAWAI: KHUSUS LEVEL ADMIN -->
                                            @if(auth()->user()->role === 'Admin')
                                            <div class="pt-3 border-t border-gray-100">
                                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('⚠️ KONFIRMASI PENGHAPUSAN:\n\nApakah Anda yakin ingin MENGHAPUS pegawai ini secara permanen dari sistem?\n\n• Nama: {{ addslashes($u->name) }}\n• NIP: {{ $u->nip ?? '-' }}\n• Email: {{ $u->email }}\n\nTindakan ini tidak dapat dibatalkan.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full bg-red-50 hover:bg-red-600 text-red-600 hover:text-white text-[9px] font-black py-2.5 px-4 rounded-xl transition-all uppercase tracking-widest border border-red-100 hover:border-red-600 hover:shadow-md flex items-center justify-center gap-2 group/del">
                                                        <svg class="w-3.5 h-3.5 text-red-500 group-hover/del:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        <span>Hapus Pegawai</span>
                                                    </button>
                                                </form>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-16 text-center text-gray-400">
                                        <div class="text-4xl mb-3">🔍</div>
                                        <p class="text-sm font-extrabold text-sipega-navy uppercase tracking-wider">Tidak ada data pegawai yang sesuai dengan filter.</p>
                                        <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci pencarian lain atau klik tombol reset di bawah.</p>
                                        <a href="{{ route('users.index') }}" class="inline-block mt-4 px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[10px] font-black uppercase tracking-widest rounded-xl transition">
                                            Reset Filter
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
