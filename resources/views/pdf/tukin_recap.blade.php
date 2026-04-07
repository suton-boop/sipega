<!DOCTYPE html>
<html>
<head>
    <title>Rekapitulasi Tukin SIPEGA - {{ $month }}</title>
    <style>
        @page { size: a3 landscape; margin: 1cm; }
        body { font-family: 'Arial', sans-serif; font-size: 8px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; font-size: 14px; text-transform: uppercase; }
        .header p { margin: 5px 0; font-size: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 3px; text-align: center; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; font-size: 7px; }
        
        .bg-gray { background-color: #e9ecef; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        /* Specific Column Widths */
        .col-no { width: 20px; }
        .col-name { width: 120px; }
        .col-nip { width: 80px; }
        .col-kj { width: 25px; }
        .col-stat { width: 20px; }
        .col-money { width: 65px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAPITULASI PEMBAYARAN TUNJANGAN KINERJA PEGAWAI</h2>
        <p>BALAI PENJAMINAN MUTU PENDIDIKAN (BPMP) PROVINSI KALIMANTAN TIMUR</p>
        <p>BULAN: {{ strtoupper($month) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" class="col-no">NO</th>
                <th rowspan="2" class="col-name">NAMA PEGAWAI</th>
                <th rowspan="2" class="col-nip">NIP</th>
                <th rowspan="2" class="col-stat">GOL</th>
                <th rowspan="2" class="col-kj">KJ</th>
                <th colspan="5">KEHADIRAN (HARI)</th>
                <th colspan="4">KETERLAMBATAN (TL)</th>
                <th colspan="4">PULANG CEPAT (PSW)</th>
                <th rowspan="2">TOTAL (%)</th>
                <th rowspan="2" class="col-money">TUKIN KOTOR</th>
                <th rowspan="2" class="col-money">POTONGAN (RP)</th>
                <th rowspan="2" class="col-money">TUKIN BERSIH</th>
                <th rowspan="2" class="col-money">PAJAK PPh21</th>
                <th rowspan="2" class="col-money">NETTO DITERIMA</th>
            </tr>
            <tr>
                <th class="col-stat">C</th>
                <th class="col-stat">S</th>
                <th class="col-stat">I</th>
                <th class="col-stat">DL</th>
                <th class="col-stat">A</th>
                <th>TL 1</th>
                <th>TL 2</th>
                <th>TL 3</th>
                <th>TL 4</th>
                <th>PSW 1</th>
                <th>PSW 2</th>
                <th>PSW 3</th>
                <th>PSW 4</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $index => $user)
                @php
                    $tukinResult = $user->calculateMonthlyTukin();
                    $tukinData = $tukinResult['data'];
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left font-bold">{{ $user->name }}</td>
                    <td>{{ $user->nip ?? '-' }}</td>
                    <td>{{ $user->golongan ?? '-' }}</td>
                    <td>{{ $user->jobClass->class_name ?? '-' }}</td>
                    
                    <!-- Attendance Stats -->
                    <td>{{ $tukinData['summary']['total_cuti'] ?? 0 }}</td>
                    <td>{{ $tukinData['summary']['total_sakit'] ?? 0 }}</td>
                    <td>{{ $tukinData['summary']['total_ijin'] ?? 0 }}</td>
                    <td>{{ $tukinData['summary']['total_st'] ?? 0 }}</td>
                    <td style="color: red;">{{ $tukinData['summary']['total_alpa'] ?? 0 }}</td>

                    <!-- Tiered TL Counters -->
                    <td>{{ $tukinData['summary']['tl_tiers'][1] ?? 0 }}</td>
                    <td>{{ $tukinData['summary']['tl_tiers'][2] ?? 0 }}</td>
                    <td>{{ $tukinData['summary']['tl_tiers'][3] ?? 0 }}</td>
                    <td>{{ $tukinData['summary']['tl_tiers'][4] ?? 0 }}</td>

                    <!-- Tiered PSW Counters -->
                    <td>{{ $tukinData['summary']['psw_tiers'][1] ?? 0 }}</td>
                    <td>{{ $tukinData['summary']['psw_tiers'][2] ?? 0 }}</td>
                    <td>{{ $tukinData['summary']['psw_tiers'][3] ?? 0 }}</td>
                    <td>{{ $tukinData['summary']['psw_tiers'][4] ?? 0 }}</td>

                    <td class="font-bold">{{ number_format($tukinResult['total_penalty_percentage'], 2) }}%</td>
                    <td class="text-right">Rp {{ number_format($tukinResult['base_amount'], 0, ',', '.') }}</td>
                    <td class="text-right" style="color: red;">Rp {{ number_format($tukinResult['total_penalty_amount'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($tukinResult['bruto_amount'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp 0</td>
                    <td class="text-right font-bold bg-gray">Rp {{ number_format($tukinResult['bruto_amount'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; float: right; width: 300px; text-align: center;">
        <p>Samarinda, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        <p>Bendahara Pengeluaran,</p>
        <br><br><br>
        <p class="font-bold" style="text-decoration: underline;">NAMA BENDAHARA</p>
        <p>NIP. XXXXXXXXXXXXXXXXXX</p>
    </div>
</body>
</html>
