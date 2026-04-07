<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('letters.index') }}" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-sipega-navy font-bold shadow-md hover:-translate-x-1 transition-transform border border-gray-100">
                ←
            </a>
            <h2 class="font-bold text-2xl text-sipega-navy leading-tight italic uppercase tracking-wider">
                ✍️ Terbitkan Dokumen Baru (ST/SK)
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-5 mb-6 rounded-r-xl shadow-sm" role="alert">
                <p class="font-black flex items-center gap-2"><span>⚠️</span> TERJADI KENDALA KEPATUHAN</p>
                <p class="font-semibold text-sm mt-1">{{ session('error') }}</p>
                <p class="text-xs mt-2 italic">*Jika dilanjutkan, pimpinan harus menyertakan Catatan/Justifikasi pada kolom di bawah formulir.</p>
            </div>
            @endif

            @if ($errors->any())
            <div class="bg-red-50 text-red-500 p-4 mb-6 rounded-2xl border-2 border-red-100">
                <ul class="list-disc pl-5 text-sm font-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('letters.store') }}" method="POST" class="bg-white overflow-hidden shadow-2xl sm:rounded-[3rem] p-10 relative z-10 border-t-[10px] border-sipega-orange" enctype="multipart/form-data">
                @csrf
                
                <div class="flex gap-4 mb-10 border-b-2 border-gray-50 pb-8">
                    <div class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center text-3xl shrink-0 border border-orange-100">📋</div>
                    <div>
                        <h3 class="text-2xl font-black text-sipega-navy leading-none italic uppercase -mt-1">Metadata Dokumen</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-2 leading-relaxed">Formulir penerbitan ST/SK terintegrasi dengan mesin Anti-Clash & Performa Pegawai (Tukin).</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Kolom 1 -->
                    <div class="space-y-6">
                        <input type="hidden" name="type" value="ST">
                        
                        <div class="group">
                            <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2 ml-1">Kategori/Tujuan Kegiatan <span class="text-red-500">*</span></label>
                            <input type="text" placeholder="Draft / Internal / Eksternal" disabled class="w-full border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold bg-gray-50 text-gray-400 focus:ring-0 transition-all cursor-not-allowed" value="Pencatatan Universal (ST, SK & KAK)">
                            <p class="text-[9px] text-gray-400 font-bold mt-1 ml-1">Output dokumen dipilih saat mencetak.</p>
                        </div>
                        
                        <div class="group">
                            <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2 ml-1">Nomor Registrasi (Opsional)</label>
                            <div class="relative">
                                <input type="text" name="number" value="{{ old('number') }}" placeholder="123" class="w-full border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold bg-gray-50 focus:bg-white focus:border-sipega-navy focus:ring-0 transition-all">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">/C6.24/KP...</div>
                            </div>
                        </div>

                        <div class="group">
                            <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2 ml-1">Perihal / Judul Kegiatan <span class="text-red-500">*</span></label>
                            <textarea name="title" required rows="3" placeholder="Contoh: Rapat Koordinasi Wilayah..." class="w-full border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold bg-gray-50 focus:bg-white focus:border-sipega-navy focus:ring-0 transition-all resize-none">{{ old('title') }}</textarea>
                        </div>
                    </div>

                    <!-- Kolom 2 -->
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="group">
                                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2 ml-1">Tgl Mulai</label>
                                <input type="date" name="date_start" value="{{ old('date_start') }}" class="w-full border-2 border-gray-100 rounded-xl p-4 text-xs font-black text-sipega-navy bg-blue-50/30 focus:border-sipega-navy focus:ring-0 transition-all">
                            </div>
                            <div class="group">
                                <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2 ml-1">Tgl Selesai</label>
                                <input type="date" name="date_end" value="{{ old('date_end') }}" class="w-full border-2 border-gray-100 rounded-xl p-4 text-xs font-black text-sipega-navy bg-blue-50/30 focus:border-sipega-navy focus:ring-0 transition-all">
                            </div>
                        </div>

                        <div class="group">
                            <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2 ml-1">Lokasi Dinas/Penugasan</label>
                            <input type="text" name="location" value="{{ old('location') }}" placeholder="Ex: Hotel Aston, Jakarta" class="w-full border-2 border-gray-100 rounded-2xl p-4 text-sm font-bold bg-gray-50 focus:bg-white focus:border-sipega-navy focus:ring-0 transition-all">
                        </div>

                        <div class="group">
                            <label class="text-[10px] font-black uppercase text-gray-400 tracking-widest block mb-2 ml-1">Catatan Pimpinan (Wajib Jika Bentrok/Merah)</label>
                            <textarea name="justification" rows="2" placeholder="Tuliskan justifikasi/alasan override jadwal di sini..." class="w-full border-2 border-red-100 bg-red-50/30 rounded-2xl p-4 text-xs font-medium focus:bg-white focus:border-red-400 focus:ring-0 transition-all resize-none placeholder-red-300">{{ old('justification') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Bagian Penunjukan Pegawai -->
                <div class="mt-8 bg-gray-50 rounded-3xl p-8 border-2 border-gray-100 relative">
                    <h4 class="font-black text-lg text-sipega-navy mb-1 italic uppercase tracking-tighter">👥 Pemilihan Personil (Kolektif)</h4>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-6">Sistem akan mengecek jadwal bentrok & performa secara otomatis saat disimpan.</p>

                    <div class="max-h-60 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                        @foreach($users as $user)
                        <label class="flex items-center justify-between p-4 bg-white rounded-2xl border-2 border-transparent hover:border-sipega-orange cursor-pointer transition-all shadow-sm group has-[:checked]:border-sipega-orange has-[:checked]:bg-orange-50/20">
                            <div class="flex items-center gap-4">
                                <!-- Checkbox Standar -->
                                <div class="flex items-center">
                                    <input type="checkbox" name="users[]" value="{{ $user->id }}" class="w-5 h-5 text-sipega-orange bg-gray-100 border-gray-300 rounded focus:ring-sipega-orange focus:ring-2 cursor-pointer" {{ (is_array(old('users')) && in_array($user->id, old('users'))) ? 'checked' : '' }}>
                                </div>
                                
                                <div>
                                    <p class="font-bold text-sm text-gray-800">{{ $user->name }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[9px] uppercase tracking-widest font-black text-gray-400 bg-gray-100 px-2 py-0.5 rounded">{{ $user->role }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <!-- Indikator Performa -->
                                <div class="flex items-center gap-1.5 justify-end mb-1">
                                    @if(in_array($user->performance_color, ['Biru', 'Hijau']))
                                        <div class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]"></div>
                                        <span class="text-[10px] font-black text-green-600 uppercase">Aman</span>
                                    @else
                                        <div class="w-2.5 h-2.5 rounded-full {{ $user->performance_color == 'Kuning' ? 'bg-yellow-400' : 'bg-red-500' }} shadow-[0_0_8px_currentColor] opacity-70"></div>
                                        <span class="text-[10px] font-black {{ $user->performance_color == 'Kuning' ? 'text-yellow-600' : 'text-red-600' }} uppercase">{{ $user->performance_color }}</span>
                                    @endif
                                </div>
                                
                                <!-- Alert Overlap Placeholder (Akan Dihitung di Controller) -->
                                @if(count($user->letters) > 0)
                                <p class="text-[9px] text-gray-400 italic">Memiliki {{ count($user->letters) }} tugas aktif</p>
                                @else
                                <p class="text-[9px] text-green-600/70 italic font-semibold">Jadwal Kosong</p>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-10 flex justify-end">
                    <button type="submit" class="bg-sipega-navy hover:bg-blue-900 text-white font-black py-5 px-10 rounded-2xl shadow-xl shadow-blue-900/20 hover:-translate-y-1 transition-all uppercase tracking-widest text-xs flex items-center gap-3 cursor-pointer">
                        Proses Pengajuan 🚀
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
