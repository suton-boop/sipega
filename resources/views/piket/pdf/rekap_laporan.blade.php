<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    @php
        $kopPath = public_path('images/kop-surat-bpmp-kaltim-2026.png');
        $kopBase64 = file_exists($kopPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath)) : '';
        \Carbon\Carbon::setLocale('id');

        // Fetch rekap data according to reportType
        if ($reportType === 'per_pegawai') {
            $reportData = \App\Models\User::realPegawai()->where('is_active', true)->get()->map(function($emp) use ($year, $month, $unit) {
                $assignQuery = \App\Models\PiketScheduleAssignment::where('active_officer_id', $emp->id)->whereYear('date', $year);
                if ($month) $assignQuery->whereMonth('date', $month);
                if ($unit) $assignQuery->where('piket_unit_id', $unit->id);
                return [
                    'user' => $emp,
                    'total_shifts' => $assignQuery->count(),
                    'ult_shifts' => (clone $assignQuery)->whereHas('unit', fn($u) => $u->where('code', 'ULT'))->count(),
                    'perpus_shifts' => (clone $assignQuery)->whereHas('unit', fn($u) => $u->where('code', 'PERPUS'))->count(),
                    'replaced_count' => (clone $assignQuery)->where('is_replaced', true)->count(),
                ];
            });
        } elseif ($reportType === 'per_unit') {
            $unitsList = $unit ? collect([$unit]) : \App\Models\PiketUnit::all();
            $reportData = $unitsList->map(function($u) use ($year, $month) {
                $q = \App\Models\PiketScheduleAssignment::where('piket_unit_id', $u->id)->whereYear('date', $year);
                if ($month) $q->whereMonth('date', $month);
                $att = \App\Models\PiketAttendance::whereHas('assignment', fn($sq) => $sq->where('piket_unit_id', $u->id)->whereYear('date', $year))->when($month, fn($sq) => $sq->whereMonth('date', $month))->get();
                return [
                    'unit' => $u,
                    'total_days' => (clone $q)->distinct('date')->count(),
                    'total_shifts' => (clone $q)->count(),
                    'conflict_count' => (clone $q)->where('has_conflict', true)->count(),
                    'replaced_count' => (clone $q)->where('is_replaced', true)->count(),
                    'visitors_served' => $att->sum('visitors_served_count'),
                ];
            });
        } else {
            $reportData = collect([]);
        }
    @endphp
    <style>
        @page {
            size: a4 portrait;
            margin: 15mm 18mm 18mm 18mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.4;
        }
        .header-kop {
            width: 100%;
            margin-bottom: 15px;
            text-align: center;
        }
        .header-kop img {
            width: 100%;
            max-height: 110px;
        }
        .title-box {
            text-align: center;
            margin-bottom: 20px;
        }
        .title-main {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .title-sub {
            font-size: 10.5pt;
            margin-top: 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #333;
            padding: 5px 8px;
            font-size: 9.5pt;
        }
        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .signature-box {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            border: none;
        }
        .signature-table td {
            border: none;
            padding: 2px;
            vertical-align: top;
            font-size: 10pt;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <div class="header-kop">
        @if($kopBase64)
            <img src="{{ $kopBase64 }}">
        @else
            <img src="{{ public_path('images/kop-surat-bpmp-kaltim-2026.png') }}">
        @endif
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="title-box">
        <div class="title-main">LAPORAN REKAPITULASI PELAKSANAAN PIKET PELAYANAN</div>
        <div class="title-sub">
            BALAI PENJAMINAN MUTU PENDIDIKAN PROVINSI KALIMANTAN TIMUR<br>
            Periode: Tahun {{ $year }} {{ $month ? 'Bulan ' . \Carbon\Carbon::create(2026, $month, 1)->translatedFormat('F') : '(Setahun Penuh)' }}
            {{ $unit ? ' - Unit ' . $unit->name : '' }}
        </div>
    </div>

    <!-- DATA TABEL -->
    @if($reportType === 'per_pegawai')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th>Nama Pegawai / NIP</th>
                    <th>Unit Kerja / Jabatan</th>
                    <th style="width: 70px;">Piket ULT</th>
                    <th style="width: 70px;">Piket Perpus</th>
                    <th style="width: 70px;">Diganti</th>
                    <th style="width: 80px;">Total Shift</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $idx => $r)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-left font-bold">
                            {{ $r['user']->name }}<br>
                            <span style="font-weight: normal; font-size: 8.5pt; font-family: monospace;">NIP. {{ $r['user']->nip ?? '-' }}</span>
                        </td>
                        <td class="text-left" style="font-size: 9pt;">
                            {{ $r['user']->unit_kerja ?? '-' }}
                        </td>
                        <td class="text-center">{{ $r['ult_shifts'] }}x</td>
                        <td class="text-center">{{ $r['perpus_shifts'] }}x</td>
                        <td class="text-center">{{ $r['replaced_count'] }}x</td>
                        <td class="text-center font-bold">{{ $r['total_shifts'] }} Shift</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($reportType === 'per_unit')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th>Unit Pelayanan</th>
                    <th style="width: 80px;">Hari Layanan</th>
                    <th style="width: 90px;">Total Shift</th>
                    <th style="width: 80px;">Konflik</th>
                    <th style="width: 80px;">Digantikan</th>
                    <th style="width: 110px;">Pengunjung</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $idx => $r)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-left font-bold">{{ $r['unit']->name }} ({{ $r['unit']->code }})</td>
                        <td class="text-center">{{ $r['total_days'] }} Hari</td>
                        <td class="text-center font-bold">{{ $r['total_shifts'] }} Shift</td>
                        <td class="text-center">{{ $r['conflict_count'] }} Kasus</td>
                        <td class="text-center">{{ $r['replaced_count'] }} Kali</td>
                        <td class="text-center font-bold">{{ $r['visitors_served'] }} Orang</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- SIGNATURE BLOCK -->
    <div class="signature-box">
        <table class="signature-table">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%; text-align: left;">
                    Samarinda, {{ \Carbon\Carbon::now('Asia/Makassar')->translatedFormat('d F Y') }}<br>
                    <strong>Kepala Subbagian Umum BPMP Kaltim</strong>
                    <br><br><br><br>
                    <strong><u>Kasubag Umum</u></strong><br>
                    NIP. 197505122002121001
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
