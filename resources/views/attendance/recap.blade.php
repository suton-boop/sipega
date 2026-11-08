<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-navy-800 leading-tight">
            {{ __('Rekapitulasi Kehadiran Perorangan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter & Mode Actions (Tombol Pilihan Laporan Harian & Slip Tukin) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl mb-6 p-6 border border-gray-100">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 pb-6 border-b border-gray-100">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-sky-50 text-sky-800 text-[10px] font-black uppercase tracking-widest rounded-full mb-2">
                            <span>⏱️ Portal Data & Unduh Laporan</span>
                        </div>
                        <h3 class="text-xl font-black text-sipega-navy">Pusat Laporan & Slip Dokumen</h3>
                        <p class="text-xs text-gray-500 font-medium">Pilih periode bulan & tahun untuk meninjau log presensi harian atau mengunduh dokumen resmi (PDF).</p>
                    </div>

                    <!-- Tombol Pilihan Tampilan -->
                    <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-2xl border border-slate-200/60 shadow-inner">
                        <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-sipega-navy text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md">
                            <span>📄 Laporan Harian</span>
                            <span class="text-[8px] bg-sipega-orange text-white px-2 py-0.5 rounded-full font-black">AKTIF</span>
                        </span>

                        @if(\App\Models\Setting::get('show_slip_tukin', '1') == '1' || \App\Models\User::find(auth()->id())?->canManageTukin())
                        <a href="{{ route('tukin.pegawai', ['month' => $monthYear, 'period' => $monthYear]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-slate-700 hover:text-sipega-navy hover:bg-white text-xs font-bold uppercase tracking-wider rounded-xl transition">
                            <span>💰 Buka Tukin</span>
                            <span class="text-[9px] text-gray-400">({{ $monthYear }})</span>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="pt-6 flex flex-wrap justify-between items-center gap-4">
                    <form method="GET" action="{{ route('attendance.recap', $user->id) }}" class="flex flex-wrap items-center gap-4">
                        @if(isset($allPegawai) && count($allPegawai) > 1 && in_array(auth()->user()->role, ['Admin', 'Kasubag', 'Pimpinan', 'Operator']))
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1">Pilih Pegawai</label>
                            <select onchange="window.location.href='{{ url('/attendance/recap') }}/' + this.value + '?month={{ $monthYear }}'" class="text-xs font-bold rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 max-w-xs">
                                @foreach($allPegawai as $p)
                                <option value="{{ $p->id }}" {{ $p->id == $user->id ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ $p->nip ?? 'No NIP' }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1">Filter Bulan & Tahun</label>
                            <div class="flex items-center gap-2">
                                <input type="month" name="month" value="{{ $monthYear }}" onchange="this.form.submit()" class="text-xs font-black rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="text-[10px] font-bold text-gray-400">({{ \Carbon\Carbon::parse($monthYear . '-01')->locale('id')->isoFormat('MMMM Y') }})</span>
                            </div>
                        </div>
                    </form>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Unduh PDF Laporan Harian -->
                        <a href="{{ route('attendance.download_pdf', ['id' => $user->id, 'month' => $monthYear]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-700 hover:bg-sky-800 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow-md shadow-sky-700/20 transition transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Unduh PDF Laporan Harian</span>
                        </a>

                        <!-- Unduh PDF Slip Tukin (Sesuai Bulan & Tahun) -->
                        @if(\App\Models\Setting::get('show_slip_tukin', '1') == '1' || \App\Http\Controllers\TukinController::isPrivilegedUser(auth()->user()))
                        <a href="{{ route('tukin.download_slip', ['id' => $user->id, 'period' => $monthYear, 'month' => $monthYear]) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-xs uppercase tracking-wider shadow-md shadow-emerald-600/20 transition transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span>Unduh PDF Slip Tukin ({{ $monthYear }})</span>
                        </a>
                        @endif

                        <!-- Cetak Halaman -->
                        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-xl font-bold text-xs uppercase tracking-wider transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>Print</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Employee Info Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6 border-l-4 border-indigo-500">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Nama Pegawai</p>
                        <p class="text-lg font-bold text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">NIP</p>
                        <p class="text-lg font-bold text-gray-900">{{ $user->nip ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Kelas Jabatan</p>
                        <p class="text-lg font-bold text-gray-900">KJ {{ $user->jobClass->class_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Unit Kerja</p>
                        <p class="text-lg font-bold text-gray-900">{{ $user->unit_kerja ?? 'BPMP Kaltim' }}</p>
                    </div>
                </div>
            </div>

            <!-- Summary Table (Excel Style) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                    <thead style="background-color: #4A90E2;" class="text-white">
                        <tr>
                            <th class="px-3 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider">No.</th>
                            <th class="px-6 py-3 border border-blue-400 text-left text-xs font-bold uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider">Kedatangan</th>
                            <th class="px-6 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider">Kepulangan</th>
                            <th class="px-6 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider">Keterangan</th>
                            <th class="px-4 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider bg-blue-700">TL (Menit)</th>
                            <th class="px-4 py-3 border border-blue-400 text-center text-xs font-bold uppercase tracking-wider bg-blue-700">PSW (Menit)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recapData as $index => $row)
                        <tr class="{{ $row['status'] == 'ALPA' ? 'bg-red-50' : (in_array($row['status'], ['DL', 'Tugas Luar (ST)']) ? 'bg-green-50' : '') }}">
                            <td class="px-3 py-2 border border-gray-300 text-center text-sm font-medium text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-2 border border-gray-300 text-sm text-gray-900">{{ \Carbon\Carbon::parse($row['date'])->translatedFormat('l, d F Y') }}</td>
                            <td class="px-6 py-2 border border-gray-300 text-center text-sm font-mono font-bold text-blue-700">{{ $row['check_in'] ?? '-' }}</td>
                            <td class="px-6 py-2 border border-gray-300 text-center text-sm font-mono font-bold text-purple-700">{{ $row['check_out'] ?? '-' }}</td>
                            <td class="px-6 py-2 border border-gray-300 text-center text-sm">
                                @php
                                    $statusClass = match($row['status']) {
                                        'Hadir' => 'bg-blue-100 text-blue-800',
                                        'DL', 'Tugas Luar (ST)' => 'bg-green-100 text-green-800',
                                        'Cuti' => 'bg-purple-100 text-purple-800',
                                        'Sakit' => 'bg-amber-100 text-amber-800',
                                        'Izin' => 'bg-teal-100 text-teal-800',
                                        'LD', 'Lupa Absen Datang (LD)' => 'bg-amber-100 text-amber-900',
                                        'LP', 'Lupa Absen Pulang (LP)' => 'bg-indigo-100 text-indigo-900',
                                        'LDS' => 'bg-sky-100 text-sky-900',
                                        'LPS' => 'bg-violet-100 text-violet-900',
                                        'ALPA' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full {{ $statusClass }}">
                                    {{ in_array($row['status'], ['DL', 'Tugas Luar (ST)']) ? 'Tugas Luar (ST)' : $row['status'] }}
                                </span>
                            </td>
                            <td class="px-4 py-2 border border-gray-300 text-center text-sm text-gray-900">
                                @if(!empty($row['needs_check_in_verif']))
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900" title="Jam masuk kosong (Perlu verifikasi)">⚠️ Verif</span>
                                @elseif($row['tl_minutes'] > 0)
                                    <span class="font-bold text-red-600">{{ $row['tl_minutes'] }} mnt</span>
                                @elseif($row['check_in'])
                                    <span class="text-emerald-700 font-semibold text-xs">0 mnt</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-2 border border-gray-300 text-center text-sm text-gray-900">
                                @if(!empty($row['needs_check_out_verif']))
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900" title="Jam pulang kosong (Perlu verifikasi)">⚠️ Verif</span>
                                @elseif($row['psw_minutes'] > 0)
                                    <span class="font-bold text-amber-700">{{ $row['psw_minutes'] }} mnt</span>
                                @elseif($row['check_out'])
                                    <span class="text-emerald-700 font-semibold text-xs">0 mnt</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <!-- Footer Aggregation -->
                    <tfoot class="bg-gray-100 font-bold">
                        <tr>
                            <td colspan="4" class="px-6 py-3 border border-gray-300 text-right">JUMLAH KEHADIRAN:</td>
                            <td class="px-6 py-3 border border-gray-300 text-center text-indigo-600">{{ $summary['total_present'] }} Hari</td>
                            <td class="px-4 py-3 border border-gray-300 text-center text-red-600">{{ $summary['total_tl_minutes'] }}</td>
                            <td class="px-4 py-3 border border-gray-300 text-center text-red-600">{{ $summary['total_psw_minutes'] }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-6 py-3 border border-gray-300 text-right">TOTAL ALPA & ST:</td>
                            <td class="px-6 py-3 border border-gray-300 text-center">
                                <span class="text-red-700">Alpa: {{ $summary['total_alpa'] }}</span> | 
                                <span class="text-green-700">ST: {{ $summary['total_st'] }}</span>
                            </td>
                            <td colspan="2" class="px-4 py-3 border border-gray-300"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Styles Override -->
    <style>
        @media print {
            .py-12 { padding-top: 0 !important; padding-bottom: 0 !important; }
            button, a, form { display: none !important; }
            .shadow-sm { box-shadow: none !important; }
            .max-w-7xl { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
            table { border-collapse: collapse !important; width: 100% !important; font-size: 10pt !important; }
            th, td { border: 1px solid #000 !important; padding: 4px !important; }
            .bg-gray-50 { background: white !important; }
            thead { background-color: #f3f4f6 !important; color: black !important; }
            thead th { border: 1px solid #000 !important; }
        }
    </style>
</x-app-layout>
