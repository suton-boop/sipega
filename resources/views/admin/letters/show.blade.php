<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <a href="{{ url()->previous() != url()->current() ? url()->previous() : (auth()->user()->role === 'Pegawai' ? route('schedules.index') : route('letters.index')) }}" 
                   class="text-xs font-bold text-gray-400 hover:text-sipega-navy flex items-center gap-1 mb-1 transition">
                    ← Kembali
                </a>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black uppercase tracking-widest text-sipega-orange bg-orange-50 px-2.5 py-0.5 rounded-full border border-orange-200">
                        {{ $letter->type === 'SK' ? 'SURAT KEPUTUSAN' : 'SURAT TUGAS RESMI' }}
                    </span>
                    <span class="text-xs text-gray-400 font-bold">&bull;</span>
                    <span class="text-xs font-bold text-gray-500 font-mono">No: {{ $letter->number ?? '-' }}</span>
                </div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight uppercase tracking-wider mt-1">
                    {{ $letter->title }}
                </h2>
            </div>

            <!-- Tombol Aksi Dokumen -->
            <div class="flex flex-wrap items-center gap-2">
                @if($letter->type === 'SK')
                    <a href="{{ route('letters.pdf_sk', $letter->id) }}" target="_blank" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider rounded-2xl transition shadow-lg flex items-center gap-2">
                        <span>📄</span> Cetak PDF SK
                    </a>
                @else
                    <a href="{{ route('letters.pdf_st', $letter->id) }}" target="_blank" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider rounded-2xl transition shadow-lg flex items-center gap-2">
                        <span>📄</span> Cetak PDF Surat Tugas
                    </a>
                @endif

                @if(in_array(auth()->user()->role, ['Admin', 'Pimpinan', 'Kasubag']))
                    <a href="{{ route('letters.edit', $letter->id) }}" class="px-5 py-2.5 bg-sipega-navy hover:bg-black text-white text-xs font-black uppercase tracking-wider rounded-2xl transition shadow-lg flex items-center gap-2">
                        <span>✏️</span> Edit Surat
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 1. STATUS & DETAIL KILAS -->
            <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] border border-gray-100 shadow-xl">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-6 border-b border-gray-100">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Status Dokumen</span>
                        <div class="mt-1 flex items-center gap-3">
                            @if($letter->status === 'Approved')
                                <span class="px-3.5 py-1.5 bg-emerald-100 text-emerald-800 text-xs font-black rounded-full uppercase tracking-wider border border-emerald-200 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span> Disetujui (Approved)
                                </span>
                            @elseif($letter->status === 'Rejected')
                                <span class="px-3.5 py-1.5 bg-red-100 text-red-800 text-xs font-black rounded-full uppercase tracking-wider border border-red-200 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-red-600"></span> Ditolak (Rejected)
                                </span>
                            @else
                                <span class="px-3.5 py-1.5 bg-amber-100 text-amber-800 text-xs font-black rounded-full uppercase tracking-wider border border-amber-200 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Menunggu Persetujuan (Draft)
                                </span>
                            @endif

                            <span class="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-black rounded-xl border border-blue-200 uppercase">
                                {{ $letter->category === 'DLK' ? 'Kegiatan Kantor (DLK)' : ($letter->category === 'DLP' ? 'Kegiatan Pusat (DLP)' : 'Kegiatan Kemitraan (DLN)') }}
                            </span>

                            @if($letter->st_model)
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-black rounded-xl border border-gray-200 uppercase">
                                    {{ strtoupper(str_replace('_', ' ', $letter->st_model)) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="text-left md:text-right">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Dibuat Oleh</span>
                        <p class="text-xs font-bold text-gray-700 mt-1">
                            {{ $letter->creator->name ?? 'Administrator' }}
                        </p>
                        <p class="text-[10px] text-gray-400">
                            {{ $letter->created_at ? $letter->created_at->translatedFormat('d F Y H:i') : '-' }} WITA
                        </p>
                    </div>
                </div>

                <!-- Detail Grid Informasi Utama -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">📅 Rentang Waktu Pelaksanaan</span>
                        <p class="text-sm font-black text-sipega-navy">
                            {{ \Carbon\Carbon::parse($letter->date_start)->translatedFormat('d F Y') }}
                            @if($letter->date_end && $letter->date_end != $letter->date_start)
                                s.d {{ \Carbon\Carbon::parse($letter->date_end)->translatedFormat('d F Y') }}
                            @endif
                        </p>
                        @php
                            $start = \Carbon\Carbon::parse($letter->date_start);
                            $end = \Carbon\Carbon::parse($letter->date_end ?? $letter->date_start);
                            $days = $start->diffInDays($end) + 1;
                        @endphp
                        <span class="text-[11px] font-bold text-sipega-orange mt-0.5 inline-block">Durasi: {{ $days }} Hari Kerja</span>
                    </div>

                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">📍 Tempat / Lokasi Kegiatan</span>
                        <p class="text-sm font-bold text-gray-800">
                            {{ $letter->location ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">💰 Sumber Dana / Pembebanan Anggaran</span>
                        <p class="text-sm font-bold text-gray-800">
                            {{ $letter->dipa_source ?: 'Ditiadakan / Tanpa DIPA' }}
                        </p>
                    </div>
                </div>

                <!-- Informasi Undangan / Dasar Penugasan -->
                @if($letter->invitation_from || $letter->invitation_number || $letter->invitation_subject)
                    <div class="mt-6 p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 block">Dasar Surat Undangan:</span>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                            <div>
                                <span class="text-gray-500 font-medium">Asal Surat/Undangan:</span>
                                <p class="font-bold text-gray-800">{{ $letter->invitation_from ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 font-medium">Nomor Surat:</span>
                                <p class="font-bold text-gray-800">{{ $letter->invitation_number ?? '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-500 font-medium">Tanggal Undangan:</span>
                                <p class="font-bold text-gray-800">
                                    {{ $letter->invitation_date ? \Carbon\Carbon::parse($letter->invitation_date)->translatedFormat('d F Y') : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- 2. DAFTAR PEGAWAI YANG DITUGASKAN -->
            <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-xl overflow-hidden">
                <div class="p-6 sm:p-8 border-b border-gray-100 bg-gray-50/40 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-black text-sipega-navy flex items-center gap-2">
                            <span>👥 Daftar Pegawai yang Ditugaskan</span>
                        </h3>
                        <p class="text-xs text-gray-400 font-bold mt-0.5">Personil pelaksana kegiatan sesuai lampiran resmi surat tugas</p>
                    </div>
                    <span class="px-3 py-1 bg-sipega-navy text-white text-xs font-black rounded-xl">
                        {{ $letter->users->count() }} Orang
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-[10px] font-black uppercase tracking-wider text-gray-500 border-b border-gray-200">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-6 min-w-[220px]">Nama Pegawai</th>
                                <th class="py-3.5 px-5 min-w-[180px]">NIP & Golongan</th>
                                <th class="py-3.5 px-5 min-w-[180px]">Jabatan</th>
                                <th class="py-3.5 px-5 min-w-[150px]">Gugus Mutu</th>
                                @if($letter->show_keterangan || $letter->st_model === 'model_5')
                                    <th class="py-3.5 px-5 min-w-[150px]">Keterangan</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @foreach($letter->users as $idx => $u)
                                @php
                                    $isMe = auth()->id() === $u->id;
                                @endphp
                                <tr class="transition-colors {{ $isMe ? 'bg-orange-50/70 border-l-4 border-sipega-orange' : 'hover:bg-gray-50/50' }}">
                                    <td class="py-4 px-4 text-center font-bold text-gray-400">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            @if($u->photo)
                                                <img src="{{ asset('storage/' . $u->photo) }}" class="w-9 h-9 object-cover rounded-xl border border-sipega-navy shadow-sm">
                                            @else
                                                <div class="w-9 h-9 bg-sipega-navy flex items-center justify-center rounded-xl text-white font-black text-xs shadow-sm">
                                                    {{ substr($u->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-black text-sipega-navy text-sm flex items-center gap-1.5">
                                                    <span>{{ $u->name }}</span>
                                                    @if($isMe)
                                                        <span class="px-2 py-0.5 bg-sipega-orange text-white text-[9px] font-black rounded-md uppercase">Anda</span>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] text-gray-400 font-bold tracking-tight mt-0.5">{{ $u->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-gray-800">{{ $u->pivot->user_nip ?: ($u->nip ?? '-') }}</div>
                                        <div class="text-[10px] text-gray-500 font-medium mt-0.5">
                                            Gol: {{ $u->pivot->user_golongan ?: ($u->golongan ?? '-') }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-5">
                                        <div class="font-bold text-gray-800">
                                            {{ $u->pivot->user_position ?: ($u->position ?? '-') }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-5">
                                        @if($u->gugus_mutu)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-800 border border-blue-200 rounded-lg text-[10px] font-bold">
                                                🏛️ {{ $u->gugus_mutu }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 italic text-[11px]">-</span>
                                        @endif
                                    </td>
                                    @if($letter->show_keterangan || $letter->st_model === 'model_5')
                                        <td class="py-4 px-5">
                                            <span class="text-gray-700 font-medium">
                                                {{ $u->pivot->keterangan ?: '-' }}
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. PEJABAT PENANDATANGAN -->
            <div class="bg-white p-6 sm:p-8 rounded-[2.5rem] border border-gray-100 shadow-xl">
                <h3 class="text-lg font-black text-sipega-navy mb-4 flex items-center gap-2">
                    <span>✍️ Pejabat Penandatangan Surat Tugas</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-5 rounded-2xl border border-gray-100">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Nama Pejabat</span>
                        <p class="text-base font-black text-sipega-navy">
                            {{ $letter->signatory_name ?? 'Dr. Jarwoko, M. Pd' }}
                        </p>
                        <p class="text-xs text-gray-500 font-bold mt-0.5">
                            NIP: {{ $letter->signatory_nip ?? '197003191997031001' }}
                        </p>
                        <p class="text-[11px] text-gray-600 font-medium mt-1">
                            Kepala Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur
                        </p>
                    </div>

                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 block mb-1">Tanggal Tanda Tangan Dokumen</span>
                        <p class="text-sm font-bold text-gray-800">
                            @if($letter->signature_date_type === 'manual' && $letter->signed_at)
                                {{ \Carbon\Carbon::parse($letter->signed_at)->translatedFormat('d F Y') }}
                            @else
                                Sesuai Tanggal Mulai ({{ \Carbon\Carbon::parse($letter->date_start)->translatedFormat('d F Y') }})
                            @endif
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
