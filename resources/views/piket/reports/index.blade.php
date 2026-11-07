<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-sipega-navy leading-tight tracking-tight flex items-center gap-2">
                    <span>📊</span> Laporan dan Rekapitulasi Piket Pelayanan
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Rekapitulasi pelaksanaan piket tahunan ULT dan Perpustakaan BPMP Provinsi Kalimantan Timur
                </p>
            </div>
            <div>
                <a href="{{ route('piket.reports.pdf', request()->query()) }}" target="_blank" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-black shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 2l3 3h-3V4zM5 14a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1-4a1 1 0 100 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                    <span>Unduh Laporan PDF</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @include('piket.partials.nav')

            <!-- TABS PILIHAN JENIS LAPORAN -->
            <div class="bg-white p-2 rounded-2xl shadow-sm border border-slate-200/80 flex flex-wrap gap-1">
                <a href="{{ route('piket.reports.index', array_merge(request()->query(), ['report_type' => 'per_pegawai'])) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-1.5 {{ $reportType === 'per_pegawai' ? 'bg-sipega-navy text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span>👤</span> Rekap Per Pegawai
                </a>
                <a href="{{ route('piket.reports.index', array_merge(request()->query(), ['report_type' => 'per_unit'])) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-1.5 {{ $reportType === 'per_unit' ? 'bg-sipega-navy text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span>🏢</span> Rekap Per Unit Layanan
                </a>
                <a href="{{ route('piket.reports.index', array_merge(request()->query(), ['report_type' => 'penggantian'])) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-1.5 {{ $reportType === 'penggantian' ? 'bg-sipega-navy text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span>🔄</span> Rekap Penggantian Petugas
                </a>
                <a href="{{ route('piket.reports.index', array_merge(request()->query(), ['report_type' => 'konflik'])) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-1.5 {{ $reportType === 'konflik' ? 'bg-sipega-navy text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
                    <span>⚠️</span> Rekap Deteksi Konflik
                </a>
            </div>

            <!-- FILTER FILTER FORM -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <form method="GET" action="{{ route('piket.reports.index') }}" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="report_type" value="{{ $reportType }}">

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Tahun:</label>
                        <select name="year" onchange="this.form.submit()" class="text-xs rounded-lg border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-bold">
                            @for($y = date('Y') + 1; $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Bulan:</label>
                        <select name="month" onchange="this.form.submit()" class="text-xs rounded-lg border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium">
                            <option value="">Semua Bulan (Setahun Penuh)</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Unit:</label>
                        <select name="unit_id" onchange="this.form.submit()" class="text-xs rounded-lg border-slate-300 focus:ring-sipega-navy focus:border-sipega-navy font-medium">
                            <option value="">Semua Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ $unitId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="px-3.5 py-1.5 bg-sipega-navy text-white text-xs font-bold rounded-lg hover:bg-sipega-navy/90 transition">
                        Terapkan
                    </button>

                    <a href="{{ route('piket.reports.index', ['report_type' => $reportType]) }}" class="text-xs font-bold text-slate-500 hover:text-slate-700 ml-auto">
                        Reset Filter
                    </a>
                </form>
            </div>

            <!-- TAMPILAN DATA SESUAI TIPE LAPORAN -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">

                <!-- 1. REKAP PER PEGAWAI -->
                @if($reportType === 'per_pegawai')
                    <div class="p-5 border-b border-slate-100">
                        <h3 class="text-base font-black text-slate-800">Rekapitulasi Giliran & Tugas Piket per Pegawai (Tahun {{ $year }})</h3>
                        <p class="text-xs text-slate-500">Melihat pemerataan jumlah piket antara petugas ULT dan Perpustakaan.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-black tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">No</th>
                                    <th class="py-3 px-4">Nama Pegawai</th>
                                    <th class="py-3 px-4">NIP & Unit Kerja</th>
                                    <th class="py-3 px-4 text-center">Piket ULT</th>
                                    <th class="py-3 px-4 text-center">Piket Perpus</th>
                                    <th class="py-3 px-4 text-center">Pernah Diganti</th>
                                    <th class="py-3 px-4 text-center">Total Shift Bertugas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse($data as $idx => $row)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-4 text-center text-slate-400 font-bold">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-800">{{ $row['user']->name }}</div>
                                            <div class="text-[10px] text-slate-500">{{ $row['user']->jabatan ?? '-' }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-mono text-[11px] text-slate-600">{{ $row['user']->nip ?? 'Non-NIP' }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $row['user']->unit_kerja ?? '-' }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold text-blue-700">
                                            {{ $row['ult_shifts'] }}x
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold text-emerald-700">
                                            {{ $row['perpus_shifts'] }}x
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold text-rose-600">
                                            {{ $row['replaced_count'] }}x
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-800 border border-slate-200">
                                                {{ $row['total_shifts'] }} Shift
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-10 text-center text-slate-400 text-xs">
                                            Tidak ada data untuk filter yang dipilih.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                <!-- 2. REKAP PER UNIT LAYANAN -->
                @elseif($reportType === 'per_unit')
                    <div class="p-5 border-b border-slate-100">
                        <h3 class="text-base font-black text-slate-800">Rekapitulasi Kinerja per Unit Pelayanan (Tahun {{ $year }})</h3>
                        <p class="text-xs text-slate-500">Perbandingan operasional antara ULT dan Perpustakaan BPMP Kaltim.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-black tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-3 px-4">Unit Pelayanan</th>
                                    <th class="py-3 px-4 text-center">Hari Layanan</th>
                                    <th class="py-3 px-4 text-center">Total Shift Petugas</th>
                                    <th class="py-3 px-4 text-center">Konflik Terdeteksi</th>
                                    <th class="py-3 px-4 text-center">Petugas Digantikan</th>
                                    <th class="py-3 px-4 text-center">Tamu / Pengunjung Terlayani</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse($data as $row)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-4 px-4">
                                            <div class="font-black text-sm text-slate-800">{{ $row['unit']->name }}</div>
                                            <div class="text-[10px] text-slate-500 font-mono">Kode: {{ $row['unit']->code }} | Lokasi: {{ $row['unit']->location }}</div>
                                        </td>
                                        <td class="py-4 px-4 text-center font-bold text-slate-700">{{ $row['total_days'] }} Hari</td>
                                        <td class="py-4 px-4 text-center font-black text-sipega-navy text-sm">{{ $row['total_shifts'] }} Shift</td>
                                        <td class="py-4 px-4 text-center font-bold text-rose-600">{{ $row['conflict_count'] }} Kasus</td>
                                        <td class="py-4 px-4 text-center font-bold text-amber-600">{{ $row['replaced_count'] }} Kali</td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                                                👥 {{ $row['visitors_served'] }} Orang
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-slate-400 text-xs">Tidak ada data unit.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                <!-- 3. REKAP PENGGANTIAN PETUGAS -->
                @elseif($reportType === 'penggantian')
                    <div class="p-5 border-b border-slate-100">
                        <h3 class="text-base font-black text-slate-800">Rekapitulasi Pergantian Petugas Piket (Tahun {{ $year }})</h3>
                        <p class="text-xs text-slate-500">Transparansi dan ketertiban administrasi penggantian petugas pelayanan.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-black tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-3 px-4">Tanggal & Unit</th>
                                    <th class="py-3 px-4">Petugas Awal</th>
                                    <th class="py-3 px-4">Petugas Pengganti</th>
                                    <th class="py-3 px-4">Tingkat Prioritas</th>
                                    <th class="py-3 px-4">Alasan</th>
                                    <th class="py-3 px-4">Persetujuan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse($data as $rep)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="font-bold text-slate-800">{{ $rep->assignment->date ? $rep->assignment->date->translatedFormat('d M Y') : '-' }}</div>
                                            <div class="text-[10px] text-slate-500">{{ $rep->assignment->unit->code ?? '-' }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="line-through text-slate-500">{{ $rep->originalOfficer->name ?? '-' }}</div>
                                        </td>
                                        <td class="py-3 px-4 font-bold text-sipega-navy">
                                            {{ $rep->replacementOfficer->name ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                                {{ strtoupper(str_replace('_', ' ', $rep->priority_level)) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 max-w-xs text-slate-600 italic">"{{ $rep->reason }}"</td>
                                        <td class="py-3 px-4 whitespace-nowrap text-[11px] text-slate-600">
                                            {{ $rep->approver->name ?? 'Admin' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-slate-400 text-xs">Belum ada riwayat penggantian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                <!-- 4. REKAP DETEKSI KONFLIK -->
                @elseif($reportType === 'konflik')
                    <div class="p-5 border-b border-slate-100">
                        <h3 class="text-base font-black text-slate-800">Rekapitulasi Benturan Jadwal Kedinasan (Tahun {{ $year }})</h3>
                        <p class="text-xs text-slate-500">Mencatat deteksi benturan dengan Surat Tugas, Cuti, dan Agenda resmi kantor.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-black tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-3 px-4">Tanggal Konflik</th>
                                    <th class="py-3 px-4">Pegawai</th>
                                    <th class="py-3 px-4">Sumber Konflik</th>
                                    <th class="py-3 px-4">Keterangan Benturan</th>
                                    <th class="py-3 px-4">Status Resolusi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse($data as $c)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="py-3 px-4 whitespace-nowrap font-bold text-slate-800">
                                            {{ $c->conflict_date->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-800">{{ $c->user->name ?? '-' }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $c->user->nip ?? '-' }}</div>
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">
                                                {{ strtoupper(str_replace('_', ' ', $c->conflict_source)) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 max-w-xs text-slate-700">
                                            {{ $c->description }}
                                            @if($c->conflict_ref_number)
                                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">No: {{ $c->conflict_ref_number }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            @if($c->status === 'pending')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-700">
                                                    Pending
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800">
                                                    Diselesaikan
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-10 text-center text-slate-400 text-xs">Tidak ada data benturan jadwal.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>

        </div>
    </div>
</x-app-layout>
