<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Daftar Pembayaran Tukin - {{ $month }}</title>
    <style>
        @page { size: a4 landscape; margin: 1cm; }
        body { font-family: 'Arial', sans-serif; font-size: 8px; color: #111; margin: 0; padding: 0; line-height: 1.2; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h3 { margin: 0; font-size: 11px; text-decoration: underline; text-transform: uppercase; font-weight: bold; }
        .header p { margin: 4px 0; font-size: 9px; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 8px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 3px 4px; text-align: center; word-wrap: break-word; }
        th { background-color: #d1d5db; font-weight: bold; text-transform: uppercase; font-size: 7.5px; }
        
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .bg-gray { background-color: #f3f4f6; }
        
        .col-no { width: 22px; }
        .col-grade { width: 28px; }
        .col-name { width: 140px; }
        .col-job { width: 140px; }
        .col-gol { width: 35px; }
        .col-money { width: 65px; }
        .col-pct { width: 45px; }
    </style>
</head>
<body>
    <div class="header">
        <h3>DAFTAR PEMBAYARAN TUNJANGAN KINERJA PEGAWAI BPMP PROVINSI KALIMANTAN TIMUR</h3>
        <p>UNTUK TUNJANGAN BULAN {{ strtoupper($month) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-no">NO</th>
                <th class="col-grade">Grade</th>
                <th class="col-name">Nama Pegawai</th>
                <th class="col-job">Jabatan</th>
                <th class="col-gol">GOL.</th>
                <th class="col-money">Tunj Kinerja Per Kelas Jab</th>
                <th class="col-pct">% Pengurangan Kehadiran</th>
                <th class="col-money">Rupiah</th>
                <th class="col-money">Faktor Pengurang</th>
                <th class="col-money">Jumlah Netto</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalBase = 0;
                $totalDeduction = 0;
                $totalNetto = 0;
            @endphp
            @foreach($calculations as $index => $c)
                @php
                    $totalBase += $c->base_amount;
                    $totalDeduction += $c->total_deduction;
                    $totalNetto += $c->netto_amount;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $c->grade }}</td>
                    <td class="text-left font-bold">{{ $c->name }}</td>
                    <td class="text-left">{{ $c->position ?? '-' }}</td>
                    <td>{{ $c->golongan ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($c->base_amount, 0, ',', '.') }}</td>
                    <td>{{ $c->total_penalty_percent > 0 ? number_format($c->total_penalty_percent, 2, ',', '.') : '0' }}</td>
                    <td class="text-right">Rp {{ number_format($c->bruto_after_deduction, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $c->total_deduction > 0 ? 'Rp ' . number_format($c->total_deduction, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-bold bg-gray">Rp {{ number_format($c->netto_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="font-bold bg-gray">
            <tr>
                <td colspan="5">TOTAL</td>
                <td class="text-right">Rp {{ number_format($totalBase, 0, ',', '.') }}</td>
                <td>-</td>
                <td class="text-right">Rp {{ number_format($totalNetto, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalDeduction, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalNetto, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 25px; width: 100%;">
        <table style="border: none; width: 100%;">
            <tr style="border: none;">
                <td style="border: none; width: 45%; text-align: center; vertical-align: top;">
                    <p style="margin: 0;">Bendahara Pengeluaran,</p>
                    <p style="margin: 2px 0 0 0;">BPMP Provinsi Kalimantan Timur</p>
                    <br><br><br><br>
                    <p class="font-bold" style="text-decoration: underline; margin: 0;">Sulistyo Rahayu, S.Pd.</p>
                    <p style="margin: 2px 0 0 0;">NIP 19760929 200312 2 001</p>
                </td>
                <td style="border: none; width: 10%;"></td>
                <td style="border: none; width: 45%; text-align: center; vertical-align: top;">
                    <p style="margin: 0;">Samarinda, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p style="margin: 2px 0 0 0;">Kuasa Pengguna Anggaran,</p>
                    <br><br><br><br>
                    <p class="font-bold" style="text-decoration: underline; margin: 0;">Dr. Jarwoko, M.Pd.</p>
                    <p style="margin: 2px 0 0 0;">NIP 19700201 199702 1 001</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
