<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-3xl text-sipega-navy leading-none tracking-tighter uppercase">
                    Detail Laporan Perjalanan Dinas 📑
                </h2>
                <p class="text-[10px] font-black text-sipega-orange uppercase tracking-[0.3em] mt-1">
                    {{ $report->employee_name }} &bull; {{ $report->st_number }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <!-- UNDUH PDF -->
                <a href="{{ route('travel-reports.pdf', $report->id) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white font-black py-2.5 px-5 rounded-2xl shadow-lg transition-all hover:-translate-y-0.5 flex items-center gap-2 uppercase text-[10px] tracking-widest">
                    <span>🖨️</span> Unduh PDF
                </a>

                <!-- UNDUH DOC -->
                <a href="{{ route('travel-reports.doc', $report->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-black py-2.5 px-5 rounded-2xl shadow-lg transition-all hover:-translate-y-0.5 flex items-center gap-2 uppercase text-[10px] tracking-widest">
                    <span>📥</span> Unduh DOC (Word)
                </a>

                <!-- EDIT -->
                @if(auth()->user()->role !== 'Pegawai' || $report->user_id === auth()->id())
                <a href="{{ route('travel-reports.edit', $report->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white font-black py-2.5 px-5 rounded-2xl shadow-lg transition-all hover:-translate-y-0.5 flex items-center gap-2 uppercase text-[10px] tracking-widest">
                    <span>✏️</span> Edit
                </a>
                @endif

                <!-- KEMBALI -->
                <a href="{{ route('travel-reports.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-black py-2.5 px-5 rounded-2xl transition-all flex items-center gap-2 uppercase text-[10px] tracking-widest">
                    <span>⬅️</span> Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
            <div class="p-4 bg-emerald-500 text-white rounded-2xl shadow-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-xl">✅</span>
                    <span class="text-xs font-bold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-white/80 hover:text-white font-bold text-sm">&times;</button>
            </div>
            @endif

            <!-- LEMBAR DOKUMEN RESMI (A4 PREVIEW) -->
            <div class="bg-white p-8 md:p-14 rounded-3xl shadow-2xl border border-gray-200 text-gray-900 font-serif leading-relaxed">
                
                <!-- HEADER TITLE -->
                <div class="text-center font-bold text-lg md:text-xl uppercase tracking-wider underline mb-8 text-black">
                    LAPORAN PERJALANAN DINAS
                </div>

                <!-- TABEL LAPORAN RESMI -->
                <table class="w-full border-collapse border border-gray-900 text-xs md:text-sm">
                    <!-- 1. IDENTITAS -->
                    <tr class="bg-gray-100">
                        <th colspan="3" class="border border-gray-900 p-3 text-left font-bold uppercase tracking-wider text-black">
                            IDENTITAS YANG MELAKUKAN PERJALANAN DINAS
                        </th>
                    </tr>
                    <tr>
                        <td class="border border-gray-900 p-2.5 w-1/4 font-semibold">Nama</td>
                        <td class="border border-gray-900 p-2.5 w-6 text-center">:</td>
                        <td class="border border-gray-900 p-2.5">{{ $report->employee_name }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-900 p-2.5 font-semibold">NIP</td>
                        <td class="border border-gray-900 p-2.5 text-center">:</td>
                        <td class="border border-gray-900 p-2.5">{{ $report->employee_nip ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-900 p-2.5 font-semibold">Jabatan</td>
                        <td class="border border-gray-900 p-2.5 text-center">:</td>
                        <td class="border border-gray-900 p-2.5">{{ $report->employee_position ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-900 p-2.5 font-semibold">Nomor Surat Tugas</td>
                        <td class="border border-gray-900 p-2.5 text-center">:</td>
                        <td class="border border-gray-900 p-2.5">
                            {{ $report->st_number }}
                            @if($report->st_date)
                                Tanggal {{ \Carbon\Carbon::parse($report->st_date)->translatedFormat('d F Y') }}
                            @endif
                        </td>
                    </tr>

                    <!-- 2. KEGIATAN -->
                    <tr class="bg-gray-100">
                        <th colspan="3" class="border border-gray-900 p-3 text-left font-bold uppercase tracking-wider text-black">
                            KEGIATAN
                        </th>
                    </tr>
                    <tr>
                        <td class="border border-gray-900 p-2.5 font-semibold">Nama Kegiatan</td>
                        <td class="border border-gray-900 p-2.5 text-center">:</td>
                        <td class="border border-gray-900 p-2.5 font-bold">{{ $report->activity_name }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-900 p-2.5 font-semibold">Tanggal</td>
                        <td class="border border-gray-900 p-2.5 text-center">:</td>
                        <td class="border border-gray-900 p-2.5">{{ $report->formatted_date_range }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-900 p-2.5 font-semibold">Tempat</td>
                        <td class="border border-gray-900 p-2.5 text-center">:</td>
                        <td class="border border-gray-900 p-2.5">{!! nl2br(e($report->location)) !!}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-900 p-2.5 font-semibold">Tujuan</td>
                        <td class="border border-gray-900 p-2.5 text-center">:</td>
                        <td class="border border-gray-900 p-2.5 text-justify">{!! nl2br(e($report->purpose)) !!}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-900 p-2.5 font-semibold">Sasaran</td>
                        <td class="border border-gray-900 p-2.5 text-center">:</td>
                        <td class="border border-gray-900 p-2.5">{!! nl2br(e($report->target_audience)) !!}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-900 p-2.5 font-semibold">Output</td>
                        <td class="border border-gray-900 p-2.5 text-center">:</td>
                        <td class="border border-gray-900 p-2.5 text-justify">{!! nl2br(e($report->output)) !!}</td>
                    </tr>

                    <!-- 3. DESKRIPSI KEGIATAN -->
                    <tr>
                        <td class="border border-gray-900 p-2.5 font-semibold align-top">Deskripsi Kegiatan</td>
                        <td class="border border-gray-900 p-2.5 text-center align-top">:</td>
                        <td class="border border-gray-900 p-3 text-justify leading-relaxed">
                            <div class="whitespace-pre-line font-serif">
                                {!! nl2br(e($report->description)) !!}
                            </div>

                            @if(!empty($report->conclusion))
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <strong class="font-bold block mb-1">Penutup / Kesimpulan:</strong>
                                <div class="whitespace-pre-line font-serif">
                                    {!! nl2br(e($report->conclusion)) !!}
                                </div>
                            </div>
                            @endif
                        </td>
                    </tr>

                    <!-- 4. TANDA TANGAN -->
                    <tr>
                        <td colspan="3" class="border border-gray-900 p-6 text-right">
                            <div class="inline-block text-center w-64">
                                <p class="mb-1">{{ $report->formatted_signed_date }}</p>
                                <p class="mb-2">Pelaksana Perjadin,</p>

                                @php
                                    $sigImg = null;
                                    if ($report->signature_image) {
                                        if (file_exists(public_path('storage/' . $report->signature_image))) {
                                            $sigImg = asset('storage/' . $report->signature_image);
                                        } elseif (file_exists(public_path($report->signature_image))) {
                                            $sigImg = asset($report->signature_image);
                                        }
                                    }
                                @endphp

                                <div class="h-20 flex items-center justify-center">
                                    @if($sigImg)
                                        <img src="{{ $sigImg }}" class="max-h-20 max-w-44 inline-block">
                                    @else
                                        <div class="h-16"></div>
                                    @endif
                                </div>

                                <p class="font-bold underline mt-1">{{ $report->employee_name }}</p>
                                <p class="text-xs">NIP. {{ $report->employee_nip ?: '-' }}</p>
                            </div>
                        </td>
                    </tr>

                    <!-- 5. DOKUMENTASI KEGIATAN -->
                    @if(!empty($report->photos) && count($report->photos) > 0)
                    <tr class="bg-gray-100">
                        <th colspan="3" class="border border-gray-900 p-3 text-center font-bold uppercase tracking-wider text-black">
                            DOKUMENTASI KEGIATAN
                        </th>
                    </tr>
                    <tr>
                        <td colspan="3" class="border border-gray-900 p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($report->photos as $ph)
                                    @php
                                        $phPath = $ph['path'] ?? '';
                                        $phCaption = $ph['caption'] ?? '';
                                        $phUrl = null;
                                        if (file_exists(public_path('storage/' . $phPath))) {
                                            $phUrl = asset('storage/' . $phPath);
                                        } elseif (file_exists(public_path($phPath))) {
                                            $phUrl = asset($phPath);
                                        }
                                    @endphp
                                    <div class="text-center bg-gray-50 p-4 rounded-xl border border-gray-200">
                                        @if($phUrl)
                                            <img src="{{ $phUrl }}" class="w-full h-56 object-cover rounded-lg border border-gray-300 shadow-sm mx-auto mb-2">
                                        @else
                                            <div class="w-full h-40 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400 font-sans text-xs mb-2">
                                                Foto Tidak Ditemukan
                                            </div>
                                        @endif
                                        <div class="text-xs font-bold text-gray-800 uppercase tracking-wide">
                                            {{ $phCaption }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endif
                </table>

            </div>

        </div>
    </div>
</x-app-layout>
