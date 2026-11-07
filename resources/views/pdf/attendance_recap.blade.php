<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Harian Presensi - {{ $user->name }} - {{ $monthYear }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2.5px solid #003366;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 13px;
            margin: 0;
            text-transform: uppercase;
            color: #003366;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 11px;
            margin: 2px 0;
            color: #111827;
            font-weight: bold;
        }
        .header p {
            margin: 1px 0;
            font-size: 9px;
            color: #4b5563;
        }
        .doc-title {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 12px;
        }
        .doc-title h3 {
            font-size: 12px;
            font-weight: 800;
            margin: 0;
            text-transform: uppercase;
            color: #003366;
            letter-spacing: 0.5px;
        }
        .doc-title .badge {
            display: inline-block;
            background-color: #f3f4f6;
            color: #374151;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            margin-top: 4px;
            border: 1px solid #e5e7eb;
        }
        
        .meta-container {
            width: 100%;
            margin-bottom: 12px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2.5px 4px;
            vertical-align: top;
            font-size: 9.5px;
        }
        .meta-label {
            width: 120px;
            font-weight: bold;
            color: #4b5563;
        }
        
        .summary-cards {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .summary-card {
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            padding: 6px 8px;
            text-align: center;
            border-radius: 4px;
        }
        .summary-card .num {
            font-size: 13px;
            font-weight: 900;
            color: #003366;
        }
        .summary-card .lbl {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-top: 1px;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #003366;
            color: #ffffff;
            border: 1px solid #002244;
            padding: 5px 4px;
            font-size: 8.5px;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.data-table td {
            border: 1px solid #d1d5db;
            padding: 4px 5px;
            font-size: 8.5px;
            vertical-align: middle;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        
        .row-weekend {
            background-color: #f9fafb;
            color: #9ca3af;
        }
        .row-holiday {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-hadir {
            color: #065f46;
            font-weight: bold;
        }
        .badge-st {
            color: #0369a1;
            font-weight: bold;
        }
        .badge-alpa {
            color: #b91c1c;
            font-weight: bold;
        }
        .badge-libur {
            color: #6b7280;
            font-style: italic;
        }
        .text-danger {
            color: #dc2626;
            font-weight: bold;
        }
        
        .signatures {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signatures td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 9.5px;
        }
        .sign-space {
            height: 55px;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- KOP RESMI -->
    <div class="header">
        <h1>Kementerian Pendidikan Dasar dan Menengah</h1>
        <h2>Balai Penjaminan Mutu Pendidikan (BPMP) Provinsi Kalimantan Timur</h2>
        <p>Jalan Cipto Mangunkusumo KM. 2, Samarinda Seberang, Kota Samarinda, Kalimantan Timur 75131</p>
        <p>Sistem Informasi Performa dan Kepegawaian (SIPEGA) — Portal Kehadiran Resmi</p>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title">
        <h3>Laporan Harian Rekapitulasi Presensi & Kehadiran Pegawai</h3>
        <span class="badge">Periode: {{ \Carbon\Carbon::parse($monthYear . '-01')->locale('id')->isoFormat('MMMM Y') }} (WITA - UTC+8)</span>
    </div>

    <!-- IDENTITAS PEGAWAI -->
    <div class="meta-container">
        <table class="meta-table">
            <tr>
                <td class="meta-label">Nama Pegawai</td>
                <td style="width: 320px;">: <strong>{{ $user->name }}</strong></td>
                <td class="meta-label">Unit Kerja / Gugus</td>
                <td>: {{ $user->unit_kerja ?? ($user->gugusMutu->name ?? 'BPMP Provinsi Kalimantan Timur') }}</td>
            </tr>
            <tr>
                <td class="meta-label">NIP</td>
                <td>: {{ $user->nip ?? '-' }}</td>
                <td class="meta-label">Jabatan / Golongan</td>
                <td>: {{ $user->position ?? '-' }} (KJ {{ $user->jobClass->class_name ?? '-' }})</td>
            </tr>
            <tr>
                <td class="meta-label">Status Kepegawaian</td>
                <td>: ASN BPMP Kaltim</td>
                <td class="meta-label">Waktu Cetak</td>
                <td>: {{ \Carbon\Carbon::now('Asia/Makassar')->locale('id')->isoFormat('dddd, D MMMM Y HH:mm') }} WITA</td>
            </tr>
        </table>
    </div>

    <!-- KARTU RINGKASAN STATISTIK -->
    <table class="summary-cards">
        <tr>
            <td style="width: 16.6%;" class="summary-card">
                <div class="num">{{ $summary['total_working_days'] ?? 0 }}</div>
                <div class="lbl">Hari Kerja</div>
            </td>
            <td style="width: 16.6%;" class="summary-card">
                <div class="num" style="color: #059669;">{{ $summary['total_present'] ?? 0 }}</div>
                <div class="lbl">Total Hadir</div>
            </td>
            <td style="width: 16.6%;" class="summary-card">
                <div class="num" style="color: #0284c7;">{{ $summary['total_st'] ?? 0 }}</div>
                <div class="lbl">Dinas Luar (ST)</div>
            </td>
            <td style="width: 16.6%;" class="summary-card">
                <div class="num" style="color: #ea580c;">{{ $summary['total_tl_minutes'] ?? 0 }}m</div>
                <div class="lbl">Terlambat (TL)</div>
            </td>
            <td style="width: 16.6%;" class="summary-card">
                <div class="num" style="color: #d97706;">{{ $summary['total_psw_minutes'] ?? 0 }}m</div>
                <div class="lbl">Pulang Cepat (PSW)</div>
            </td>
            <td style="width: 16.6%;" class="summary-card">
                <div class="num" style="color: #dc2626;">{{ $summary['total_alpa'] ?? 0 }}</div>
                <div class="lbl">Tanpa Ket. (Alpa)</div>
            </td>
        </tr>
    </table>

    <!-- TABEL LOG HARIAN -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 75px;">Tanggal</th>
                <th style="width: 60px;">Hari</th>
                <th style="width: 65px;">Masuk (In)</th>
                <th style="width: 65px;">Pulang (Out)</th>
                <th style="width: 55px;">TL (m)</th>
                <th style="width: 55px;">PSW (m)</th>
                <th style="width: 110px;">Status</th>
                <th>Keterangan / Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recapData as $idx => $row)
            @php
                $isWeekendOrOff = in_array($row['status'], ['Libur/Off', 'Libur']);
                $isST = in_array($row['status'], ['Tugas Luar (ST)', 'DL']);
                $isAlpa = $row['status'] === 'ALPA';
            @endphp
            <tr class="{{ $isWeekendOrOff ? 'row-weekend' : '' }}">
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center text-bold">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                <td class="text-center">{{ $row['day_name'] }}</td>
                <td class="text-center">
                    {{ $row['check_in'] ? substr($row['check_in'], 0, 5) : '-' }}
                </td>
                <td class="text-center">
                    {{ $row['check_out'] ? substr($row['check_out'], 0, 5) : '-' }}
                </td>
                <td class="text-center {{ $row['tl_minutes'] > 0 ? 'text-danger' : '' }}">
                    {{ $row['tl_minutes'] > 0 ? $row['tl_minutes'] . ' m' : '-' }}
                </td>
                <td class="text-center {{ $row['psw_minutes'] > 0 ? 'text-danger' : '' }}">
                    {{ $row['psw_minutes'] > 0 ? $row['psw_minutes'] . ' m' : '-' }}
                </td>
                <td class="text-center">
                    @if($isST)
                        <span class="badge-st">Tugas Luar (ST)</span>
                    @elseif($isAlpa)
                        <span class="badge-alpa">ALPA</span>
                    @elseif($isWeekendOrOff)
                        <span class="badge-libur">Libur Akhir Pekan</span>
                    @elseif($row['status'] === 'Hadir')
                        <span class="badge-hadir">Hadir</span>
                    @else
                        <strong>{{ $row['status'] }}</strong>
                    @endif
                </td>
                <td style="font-size: 8px;">
                    @if($row['needs_check_in_verif'] && $row['needs_check_out_verif'])
                        <span style="color: #ea580c;">Lupa Absen Datang & Pulang</span>
                    @elseif($row['needs_check_in_verif'])
                        <span style="color: #ea580c;">Lupa Absen Datang</span>
                    @elseif($row['needs_check_out_verif'])
                        <span style="color: #ea580c;">Lupa Absen Pulang</span>
                    @elseif($isST)
                        <span style="color: #0369a1;">Penugasan Kedinasan Resmi</span>
                    @elseif($row['tl_minutes'] > 0 || $row['psw_minutes'] > 0)
                        <span>TL: {{ $row['tl_minutes'] }}m, PSW: {{ $row['psw_minutes'] }}m</span>
                    @elseif($row['status'] === 'Hadir')
                        <span style="color: #059669;">Tepat Waktu (Disiplin)</span>
                    @else
                        <span>-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TANDA TANGAN PENGESAHAN -->
    <table class="signatures">
        <tr>
            <td>
                Pegawai yang Bersangkutan,
                <div class="sign-space"></div>
                <div class="sign-name">{{ $user->name }}</div>
                <div>NIP. {{ $user->nip ?? '-' }}</div>
            </td>
            <td>
                Samarinda, {{ \Carbon\Carbon::parse($monthYear . '-01')->endOfMonth()->locale('id')->isoFormat('D MMMM Y') }}<br>
                Kasubbag Umum / Atasan Langsung,
                <div class="sign-space"></div>
                <div class="sign-name">Kasubbag Umum BPMP Kaltim</div>
                <div>NIP. 197505122002121002</div>
            </td>
        </tr>
    </table>
</body>
</html>
