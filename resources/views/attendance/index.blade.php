<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
            <h2 class="font-semibold text-xl text-sipega-navy leading-tight font-sans tracking-wide">
                {{ __('Daftar Hadir Kegiatan') }} 📅
            </h2>
            <div id="mobileScannerBadge" class="hidden md:hidden">
                <span class="bg-sipega-orange text-white text-[10px] font-black px-3 py-1 rounded-full uppercase animate-pulse">Auto-Scanner Ready</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Check-in Section -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl border-t-[10px] border-blue-600 p-8">
                <h3 class="text-2xl font-extrabold mb-6 text-sipega-navy">📍 Scan Presensi Rapat</h3>
                <div class="bg-blue-50 border border-blue-100 rounded-3xl p-6 text-center">
                    <p class="text-gray-600 mb-6 text-sm font-medium">Buka menu scan di aplikasi SIPEGA Android atau gunakan kamera untuk verifikasi lokasi GPS dan scan QR Rapat yang tampil di layar utama.</p>
                    <div onclick="startScanner()" class="p-8 border-4 border-dashed border-blue-200 rounded-3xl flex flex-col items-center justify-center bg-white hover:border-sipega-orange hover:bg-orange-50 transition-all cursor-pointer group shadow-inner">
                        <span class="text-6xl mb-4 group-hover:scale-110 transition-transform">📸</span>
                        <p class="text-sipega-navy font-black text-xs uppercase tracking-widest group-hover:text-sipega-orange">Aktifkan Kamera Scanner</p>
                    </div>
                </div>
            </div>

            <!-- Admin/Operator: Create New Meeting -->
            @if(in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator']))
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl border-t-[10px] border-sipega-orange p-8">
                <h3 class="text-xl font-black mb-6 text-sipega-navy italic uppercase tracking-tighter flex justify-between items-center">
                    🚀 Daftarkan Kegiatan/Rapat Baru
                    <button type="button" onclick="openLocationModal()" class="text-[10px] bg-sipega-navy text-white px-4 py-2 rounded-full font-black uppercase shadow-lg hover:bg-black transition cursor-pointer">+ Master Lokasi</button>
                </h3>
                <form action="{{ route('admin.meetings.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase text-gray-400">Judul Kegiatan</label>
                            <input type="text" name="title" placeholder="E.g. Rapat Koordinasi Bulanan" required class="border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold w-full focus:border-sipega-orange transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase text-gray-400">Tanggal</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold w-full focus:border-sipega-orange transition-all">
                        </div>
                    </div>

                    <!-- Target Peserta Pilihan -->
                    <div class="bg-gray-50/50 p-6 rounded-[2rem] border border-gray-100">
                        <label class="text-[10px] font-black uppercase text-sipega-navy mb-4 block tracking-widest">🎯 Target Undangan Peserta:</label>
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <label class="relative flex items-center justify-center p-4 border-2 rounded-2xl cursor-pointer transition-all hover:bg-white group" id="label_all">
                                <input type="radio" name="target_type" value="All" checked class="hidden" onchange="toggleUserSelection('All')">
                                <span class="text-xs font-black uppercase text-gray-400 group-hover:text-sipega-navy">Seluruh Pegawai</span>
                            </label>
                            <label class="relative flex items-center justify-center p-4 border-2 rounded-2xl cursor-pointer transition-all hover:bg-white group" id="label_specific">
                                <input type="radio" name="target_type" value="Specific" class="hidden" onchange="toggleUserSelection('Specific')">
                                <span class="text-xs font-black uppercase text-gray-400 group-hover:text-sipega-navy">Undangan Tertentu</span>
                            </label>
                        </div>

                        <!-- User Selection (Hidden by Default) -->
                        <div id="user_selection_area" class="hidden space-y-3 bg-white p-6 rounded-2xl border border-gray-100 max-h-60 overflow-y-auto shadow-inner">
                            <p class="text-[9px] font-black text-sipega-orange uppercase mb-2">Pilih Pegawai Yang Diundang:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($allUsers as $u)
                                <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-orange-50 cursor-pointer transition-colors border border-transparent hover:border-orange-200">
                                    <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" class="rounded text-sipega-orange focus:ring-sipega-orange">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-black text-sipega-navy uppercase leading-none">{{ $u->name }}</span>
                                        <span class="text-[9px] font-bold text-gray-400">{{ $u->position ?? 'Pegawai SIPEGA' }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase text-gray-400">Jam Mulai</label>
                            <input type="time" name="start_time" required class="border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold w-full">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase text-sipega-orange italic">Open Absen</label>
                            <input type="time" name="open_time" class="border-2 border-orange-50 rounded-2xl p-4 text-sm font-bold w-full bg-orange-50/30">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase text-red-500 italic">Close Absen</label>
                            <input type="time" name="close_time" class="border-2 border-red-50 rounded-2xl p-4 text-sm font-bold w-full bg-red-50/30">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase text-gray-400">Tempat Rapat</label>
                            <select name="location_name" id="location_select" required class="border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold w-full bg-white" onchange="syncCoordinates(this, 'lat_input', 'lng_input')">
                                <option value="">-- Pilih Tempat Kegiatan --</option>
                                @foreach($locations as $loc)
                                <option value="{{ $loc->name }}" data-lat="{{ $loc->latitude }}" data-lng="{{ $loc->longitude }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black uppercase text-gray-400">Uraian / Agenda Rapat</label>
                            <input type="text" name="agenda" placeholder="Agenda Rapat..." required class="border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold w-full">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="lat" id="lat_input" placeholder="Lat" class="border-2 border-gray-100 rounded-2xl p-4 text-[10px] font-bold w-full bg-gray-50" readonly title="Koordinat otomatis dari Lokasi">
                        <input type="text" name="lng" id="lng_input" placeholder="Lng" class="border-2 border-gray-100 rounded-2xl p-4 text-[10px] font-bold w-full bg-gray-50" readonly title="Koordinat otomatis dari Lokasi">
                        <div class="relative">
                            <input type="number" name="geofence_radius" value="100" placeholder="Radius (Meter)" class="border-2 border-orange-100 rounded-2xl p-4 text-[10px] font-bold w-full bg-orange-50/20 focus:border-sipega-orange" title="Jarak maksimal pegawai dari titik lokasi">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[8px] font-black text-sipega-orange uppercase">Meter</span>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-sipega-navy text-white font-black py-5 rounded-[2rem] shadow-xl hover:bg-black transition-all hover:scale-[1.01] uppercase tracking-[0.2em] text-[11px]">
                        Publish Kegiatan SIPEGA 💾
                    </button>
                </form>
            </div>
            @endif

            <!-- History & Minutes Section -->
            <div class="bg-white overflow-hidden shadow-2xl sm:rounded-3xl p-8">
                <h3 class="text-2xl font-extrabold mb-6 text-sipega-navy">📜 Riwayat & Dokumentasi Kegiatan</h3>
                
                <div class="space-y-6">
                    @forelse($availableMeetings as $meeting)
                    <div class="p-6 bg-gray-50 border border-gray-100 rounded-3xl group hover:border-sipega-orange transition-all relative">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                            <div>
                                <h4 class="font-black text-gray-800 text-lg uppercase italic tracking-tighter">{{ $meeting->title }}</h4>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <span class="text-[9px] bg-sipega-navy/5 text-sipega-navy px-2 py-0.5 rounded-md font-bold uppercase tracking-wider">📅 {{ \Carbon\Carbon::parse($meeting->date)->format('d/m/Y') }}</span>
                                    <span class="text-[9px] bg-sipega-navy/5 text-sipega-navy px-2 py-0.5 rounded-md font-bold uppercase">🕒 {{ $meeting->start_time }} WITA</span>
                                    <span class="text-[9px] bg-orange-100 text-sipega-orange px-2 py-0.5 rounded-md font-black uppercase">📍 {{ $meeting->location_name ?? 'Lokasi Belum Diatur' }}</span>
                                    @if($meeting->open_time)
                                        <span class="text-[9px] bg-green-100 text-green-700 px-2 py-0.5 rounded-md font-black uppercase">OPEN: {{ $meeting->open_time }}</span>
                                    @endif
                                    @if($meeting->close_time)
                                        <span class="text-[9px] bg-red-100 text-red-700 px-2 py-0.5 rounded-md font-black uppercase">CLOSE: {{ $meeting->close_time }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5 justify-end">
                                <!-- Admin Actions -->
                                @if(in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator']))
                                    <a href="{{ route('admin.meetings.qr', $meeting->id) }}" target="_blank" class="w-10 h-10 flex items-center justify-center bg-sipega-navy text-white rounded-xl shadow-lg hover:bg-black transition cursor-pointer" title="QR Screen">
                                        <span class="text-lg">📱</span>
                                    </a>
                                    <a href="{{ route('admin.meetings.print-qr', $meeting->id) }}" target="_blank" class="w-10 h-10 flex items-center justify-center bg-white border-2 border-sipega-navy text-sipega-navy rounded-xl shadow-lg hover:bg-sipega-navy hover:text-white transition cursor-pointer" title="Cetak Barcode">
                                        <span class="text-lg">🖨️</span>
                                    </a>
                                    <button type="button" onclick="openEditModal({{ $meeting->id }})" class="w-10 h-10 flex items-center justify-center bg-orange-50 text-sipega-orange rounded-xl border border-orange-200 hover:bg-sipega-orange hover:text-white transition shadow-sm cursor-pointer" title="Edit">
                                        <span class="text-lg">✏️</span>
                                    </button>
                                    @if(auth()->user()->role === 'Admin')
                                    <form action="{{ route('admin.meetings.destroy', $meeting->id) }}" method="POST" class="inline" onsubmit="return confirm('⚠️ SIPEGA: Hapus kegiatan ini secara permanen?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-10 h-10 flex items-center justify-center bg-red-50 text-red-600 rounded-xl border border-red-200 hover:bg-red-600 hover:text-white transition shadow-sm cursor-pointer" title="Hapus">
                                            <span class="text-lg">🗑️</span>
                                        </button>
                                    </form>
                                    @endif
                                @endif
                                
                                <a href="{{ route('admin.meetings.download-attendance', $meeting->id) }}" class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white rounded-xl shadow-lg hover:bg-blue-800 transition cursor-pointer" title="Absensi PDF">
                                    <span class="text-lg">📄</span>
                                </a>
                                @if($meeting->minutes_text)
                                    <a href="{{ route('admin.meetings.download-minutes', $meeting->id) }}" class="w-10 h-10 flex items-center justify-center bg-green-600 text-white rounded-xl shadow-lg hover:bg-green-800 transition cursor-pointer" title="Notulensi PDF">
                                        <span class="text-lg">📝</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white border border-gray-100 rounded-2xl p-4 mb-4">
                            <p class="text-[10px] font-black text-sipega-orange uppercase tracking-widest mb-1">Agenda & Keputusan:</p>
                            <p class="text-sm text-gray-600 font-bold leading-relaxed">{{ $meeting->agenda }}</p>
                        </div>

                        <!-- Operator: Write Minutes -->
                        @if(in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag', 'Operator']))
                        <div class="mt-4 border-t pt-4">
                            <form action="{{ route('admin.meetings.minutes', $meeting->id) }}" method="POST">
                                @csrf
                                <label class="text-[10px] font-black text-gray-400 uppercase mb-2 block">Tulis/Perbarui Notulensi Rapat:</label>
                                <textarea name="minutes" rows="3" class="w-full border-2 border-gray-50 rounded-2xl p-4 text-sm font-medium focus:border-sipega-orange focus:ring-0" placeholder="Hasil keputusan rapat...">{{ $meeting->minutes_text }}</textarea>
                                <button type="submit" class="mt-2 bg-gray-100 text-gray-500 font-black py-2 px-6 rounded-xl text-[10px] uppercase tracking-widest hover:bg-sipega-orange hover:text-white transition">SIMPAN NOTULENSI ✔</button>
                            </form>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-12 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-100">
                        <span class="text-4xl mb-2 block">📭</span>
                        <p class="text-gray-400 italic text-sm font-medium uppercase tracking-widest text-[10px]">Belum ada kegiatan/rapat terdaftar.</p>
                    </div>
                    @endforelse
              <!-- MODAL: ADD LOCATION -->
    <div id="locationModal" class="fixed inset-0 bg-sipega-navy/90 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-[3rem] w-full max-w-lg shadow-2xl overflow-hidden relative border-t-[12px] border-sipega-orange">
            <!-- Close Button -->
            <button onclick="closeLocationModal()" class="absolute top-6 right-6 w-10 h-10 flex items-center justify-center bg-gray-50 text-gray-400 rounded-full hover:bg-red-50 hover:text-red-500 transition-all z-10 cursor-pointer">
                <span class="text-xl font-bold">✕</span>
            </button>

            <div class="p-10">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-3xl">📍</div>
                    <div>
                        <h3 class="text-2xl font-black text-sipega-navy leading-none italic uppercase">Master Lokasi</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Tambahkan Titik Koordinat Presensi</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="group">
                        <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest ml-1">Nama Tempat / Gedung</label>
                        <input type="text" id="loc_name" placeholder="E.g. Aula Utama Lantai 3" class="w-full border-2 border-gray-100 rounded-2xl p-5 text-sm font-bold focus:border-sipega-orange focus:ring-0 transition-all bg-gray-50/50">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="group">
                            <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest ml-1">Latitude</label>
                            <input type="text" id="loc_lat" placeholder="-0.123456" class="w-full border-2 border-gray-100 rounded-2xl p-5 text-xs font-black bg-orange-50/20 focus:border-sipega-orange focus:ring-0 transition-all">
                        </div>
                        <div class="group">
                            <label class="text-[10px] font-black uppercase text-gray-400 mb-2 block tracking-widest ml-1">Longitude</label>
                            <input type="text" id="loc_lng" placeholder="117.123456" class="w-full border-2 border-gray-100 rounded-2xl p-5 text-xs font-black bg-orange-50/20 focus:border-sipega-orange focus:ring-0 transition-all">
                        </div>
                    </div>

                    <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 flex gap-3 items-start">
                        <span class="text-blue-500 mt-0.5 italic font-black text-xs">ℹ️</span>
                        <p class="text-[9px] text-blue-700 font-bold leading-relaxed">* Titik ini akan menjadi pusat lingkaran (geofence) SIPEGA. Pastikan angka koordinat akurat agar pegawai tidak gagal presensi.</p>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="closeLocationModal()" class="flex-1 bg-gray-100 text-gray-500 font-black py-5 rounded-2xl hover:bg-gray-200 transition-all uppercase tracking-widest text-[10px] cursor-pointer">Batal</button>
                        <button type="button" onclick="saveNewLocation()" class="flex-[2] bg-sipega-orange text-white font-black py-5 rounded-2xl shadow-xl shadow-orange-200 hover:bg-orange-600 hover:-translate-y-1 transition-all uppercase tracking-widest text-[11px] cursor-pointer">Simpan Master 💾</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    <!-- MODAL: QR SCANNER -->
    <div id="scannerModal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-[110] hidden flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-[3rem] overflow-hidden shadow-2xl relative">
            <div class="bg-sipega-navy p-6 text-white text-center">
                <h3 class="text-lg font-black uppercase tracking-tighter italic">SIPEGA Mobile Scanner</h3>
                <p class="text-[9px] opacity-70 font-bold uppercase tracking-widest">Arahkan Kamera ke QR Code Rapat</p>
            </div>
            
            <div id="reader" class="w-full aspect-square bg-black"></div>
            
            <div class="p-8 text-center bg-gray-50 border-t border-gray-100">
                <div id="scannerStatus" class="mb-4 text-xs font-bold text-gray-500 italic">Mencari Kamera...</div>
                <button onclick="stopScanner()" class="w-full bg-red-600 text-white font-black py-4 rounded-2xl shadow-xl hover:bg-black transition uppercase tracking-widest text-[10px]">
                    BATAL / TUTUP CAMERA ✕
                </button>
            </div>
        </div>
        <p class="mt-8 text-white/40 text-[9px] font-bold uppercase tracking-[0.4em]">Integrated SIPEGA Geo-Fencing System</p>
    </div>

    <!-- MODAL: EDIT MEETING -->
    <div id="editMeetingModal" class="fixed inset-0 bg-sipega-navy/80 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-2xl shadow-2xl p-10 border-t-[10px] border-sipega-navy">
            <h3 class="text-2xl font-black text-sipega-navy mb-6 italic uppercase tracking-tighter">✏️ Edit Kegiatan SIPEGA</h3>
            <form id="editMeetingForm" method="POST" class="space-y-6">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-gray-400">Judul Kegiatan</label>
                        <input type="text" name="title" id="edit_title" required class="border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold w-full">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-gray-400">Tanggal</label>
                        <input type="date" name="date" id="edit_date" required class="border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold w-full">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-gray-400">Jam Mulai</label>
                        <input type="time" name="start_time" id="edit_start_time" required class="border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold w-full">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-sipega-orange italic">Open Absen</label>
                        <input type="time" name="open_time" id="edit_open_time" class="border-2 border-orange-50 rounded-2xl p-4 text-sm font-bold w-full bg-orange-50/30">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-red-500 italic">Close Absen</label>
                        <input type="time" name="close_time" id="edit_close_time" class="border-2 border-red-50 rounded-2xl p-4 text-sm font-bold w-full bg-red-50/30">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-gray-400">Tempat Kegiatan</label>
                        <select name="location_name" id="edit_location_select" required class="border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold w-full bg-white transition-all focus:border-sipega-navy" onchange="syncCoordinates(this, 'edit_lat', 'edit_lng')">
                            <option value="">-- Pilih Tempat --</option>
                            @foreach($locations as $loc)
                            <option value="{{ $loc->name }}" data-lat="{{ $loc->latitude }}" data-lng="{{ $loc->longitude }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[9px] font-black uppercase text-gray-400">Agenda Dasar</label>
                        <input type="text" name="agenda" id="edit_agenda" required class="border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold w-full">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="text" name="lat" id="edit_lat" placeholder="Computed Lat" class="border-2 border-gray-100 rounded-2xl p-4 text-[10px] font-bold w-full bg-gray-50" readonly>
                    <input type="text" name="lng" id="edit_lng" placeholder="Computed Lng" class="border-2 border-gray-100 rounded-2xl p-4 text-[10px] font-bold w-full bg-gray-50" readonly>
                    <div class="relative">
                        <input type="number" name="geofence_radius" id="edit_radius" placeholder="Radius (Meter)" class="border-2 border-orange-100 rounded-2xl p-4 text-[10px] font-bold w-full bg-orange-50/20">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[8px] font-black text-sipega-orange uppercase">Meter</span>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-100 text-gray-500 font-black py-4 rounded-2xl hover:bg-gray-200 transition uppercase tracking-widest text-[10px]">BATAL</button>
                    <button type="submit" class="flex-2 bg-sipega-navy text-white font-black py-4 rounded-2xl shadow-xl hover:bg-black transition uppercase tracking-widest text-[10px]">SIMPAN PERUBAHAN 🏁</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode = null;

        async function startScanner() {
            const modal = document.getElementById('scannerModal');
            const status = document.getElementById('scannerStatus');
            
            modal.classList.remove('hidden');
            status.innerText = "Menyiapkan Kamera & GPS...";
            status.className = "mb-4 text-xs font-bold text-sipega-navy animate-pulse uppercase";

            // Permintaan izin GPS
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    () => console.log("SIPEGA: GPS Ready"), 
                    (err) => console.warn("SIPEGA: GPS Permission Denied")
                );
            }

            // Pastikan instance lama dibersihkan jika ada
            if (html5QrCode) {
                try {
                    await html5QrCode.stop();
                } catch (e) {
                    console.log("Cleaning up old scanner instance");
                }
            }

            html5QrCode = new Html5Qrcode("reader");
            const config = { 
                fps: 15, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            try {
                // Mencoba memulai dengan kamera belakang (environment)
                await html5QrCode.start(
                    { facingMode: "environment" }, 
                    config, 
                    onScanSuccess
                );
                status.innerText = "Kamera Aktif. Silakan arahkan ke QR Code.";
                status.className = "mb-4 text-xs font-bold text-green-600 uppercase";
            } catch (err) {
                console.error("Scanner Error:", err);
                
                // Fallback: Jika environment gagal, coba gunakan kamera apa saja yang tersedia
                try {
                    await html5QrCode.start(
                        { facingMode: "user" }, 
                        config, 
                        onScanSuccess
                    );
                    status.innerText = "Kamera Depan Aktif. Silakan Scan.";
                } catch (fallbackErr) {
                    status.innerText = "Gagal Akses Kamera.";
                    status.className = "mb-4 text-xs font-bold text-red-600 uppercase";
                    alert("❌ SIPEGA: Kamera tidak dapat diakses. Mohon periksa izin browser atau segarkan halaman.");
                    stopScanner();
                }
            }
        }

        async function stopScanner() {
            const modal = document.getElementById('scannerModal');
            if (html5QrCode && html5QrCode.isScanning) {
                try {
                    await html5QrCode.stop();
                    html5QrCode.clear();
                } catch (err) {
                    console.error("SIPEGA: Error stopping scanner", err);
                }
            }
            modal.classList.add('hidden');
        }

        async function onScanSuccess(decodedText, decodedResult) {
            console.log("SIPEGA Decoded:", decodedText);
            try {
                const data = JSON.parse(decodedText);
                if (!data.id || !data.token) throw new Error("Format QR SIPEGA Tidak Valid.");

                // Hentikan kamera melalui fungsi terpusat
                await stopScanner();
                document.getElementById('scannerStatus').innerText = "Verifikasi Lokasi & Data...";

                // Capture GPS & Check-in
                navigator.geolocation.getCurrentPosition(async (position) => {
                    const checkInData = {
                        meeting_id: data.id,
                        token: data.token,
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        device_id: 'WEB-' + (localStorage.getItem('sipega_id') || Math.random().toString(36).substring(7))
                    };

                    // Simpan device ID dummy jika belum ada
                    if (!localStorage.getItem('sipega_id')) localStorage.setItem('sipega_id', checkInData.device_id);

                    const response = await fetch("{{ route('attendance.check-in') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(checkInData)
                    });

                    const result = await response.json();
                    if (result.success) {
                        alert("✅ BERHASIL: " + result.message);
                        window.location.reload();
                    } else {
                        alert("❌ GAGAL: " + result.message);
                        startScanner(); // Coba lagi
                    }
                }, (err) => {
                    alert("❌ SIPEGA: Gagal Mendeteksi Lokasi. Mohon aktifkan GPS HP Anda.");
                    startScanner();
                });

            } catch (e) {
                alert("⚠️ SIPEGA: QR Code ini bukan milik sistem resmi SIPEGA.");
                console.error(e);
            }
        }

        // ... existing scripts below ...
        // Data Rapat Global untuk SIPEGA
        window.sipegaMeetings = @json($availableMeetings);

        function syncCoordinates(select, latId, lngId) {
            const option = select.options[select.selectedIndex];
            document.getElementById(latId).value = option.dataset.lat || '';
            document.getElementById(lngId).value = option.dataset.lng || '';
        }

        function openLocationModal() {
            document.getElementById('locationModal').classList.remove('hidden');
        }

        function closeLocationModal() {
            document.getElementById('locationModal').classList.add('hidden');
        }

        function openEditModal(meetingId) {
            console.log('SIPEGA: Mencoba membuka edit untuk ID', meetingId);
            const meeting = window.sipegaMeetings.find(m => m.id == meetingId);
            
            if (!meeting) {
                console.error('SIPEGA: Data rapat tidak ditemukan!');
                return;
            }

            const form = document.getElementById('editMeetingForm');
            const baseUrl = "{{ url('/') }}";
            form.action = `${baseUrl}/admin/meetings/${meetingId}`;
            
            document.getElementById('edit_title').value = meeting.title;
            document.getElementById('edit_date').value = meeting.date;
            document.getElementById('edit_start_time').value = meeting.start_time;
            document.getElementById('edit_open_time').value = meeting.open_time || '';
            document.getElementById('edit_close_time').value = meeting.close_time || '';
            document.getElementById('edit_agenda').value = meeting.agenda;
            document.getElementById('edit_location_select').value = meeting.location_name;
            document.getElementById('edit_lat').value = meeting.gps_lat || '';
            document.getElementById('edit_lng').value = meeting.gps_lng || '';
            document.getElementById('edit_radius').value = meeting.geofence_radius || 100;
            
            document.getElementById('editMeetingModal').classList.remove('hidden');
            console.log('SIPEGA: Modal Edit berhasil ditampilkan.');
        }

        function closeEditModal() {
            document.getElementById('editMeetingModal').classList.add('hidden');
        }

        function toggleUserSelection(type) {
            const area = document.getElementById('user_selection_area');
            const labelAll = document.getElementById('label_all');
            const labelSpecific = document.getElementById('label_specific');

            if (type === 'Specific') {
                area.classList.remove('hidden');
                labelSpecific.classList.add('border-sipega-orange', 'bg-orange-50/20');
                labelSpecific.querySelector('span').classList.add('text-sipega-orange');
                labelAll.classList.remove('border-sipega-orange', 'bg-orange-50/20');
                labelAll.querySelector('span').classList.remove('text-sipega-orange');
            } else {
                area.classList.add('hidden');
                labelAll.classList.add('border-sipega-orange', 'bg-orange-50/20');
                labelAll.querySelector('span').classList.add('text-sipega-orange');
                labelSpecific.classList.remove('border-sipega-orange', 'bg-orange-50/20');
                labelSpecific.querySelector('span').classList.remove('text-sipega-orange');
            }
        }

        // Initialize display
        document.addEventListener('DOMContentLoaded', () => {
            toggleUserSelection('All');

            // --- AUTO START SCANNER FOR MOBILE ---
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const mobileBadge = document.getElementById('mobileScannerBadge');

            if (isMobile) {
                if (mobileBadge) mobileBadge.classList.remove('hidden');
                
                // Menunggu interaksi pertama atau sedikit delay untuk memastikan DOM & Geolocation siap
                setTimeout(() => {
                    console.log("SIPEGA: Mobile detected, launching auto-scanner...");
                    startScanner();
                }, 1500); 
            }
        });

        async function saveNewLocation() {
            const name = document.getElementById('loc_name').value;
            const lat = document.getElementById('loc_lat').value;
            const lng = document.getElementById('loc_lng').value;

            if (!name || !lat || !lng) return alert('⚠️ SIPEGA: Mohon lengkapi data lokasi (Nama, Lat, Lng).');

            const saveBtn = document.querySelector('#locationModal button[onclick="saveNewLocation()"]');
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = 'Memuat...';
            }

            try {
                const response = await fetch("{{ route('admin.locations.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ name: name, latitude: lat, longitude: lng })
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('✅ BERHASIL: ' + result.message);
                    window.location.reload();
                } else {
                    alert('❌ Gagal SIPEGA: ' + (result.message || 'Data tidak valid.'));
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = 'Simpan Master 💾';
                    }
                }
            } catch (error) {
                console.error('SIPEGA Location Error:', error);
                alert('🚫 Terjadi kesalahan sistem SIPEGA atau koneksi terputus.');
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'Simpan Master 💾';
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
