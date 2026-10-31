<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekapitulasi Tukin SIPEGA - {{ $month }}</title>
    <style>
        @page { size: a3 landscape; margin: 0.8cm; }
        body { font-family: 'Arial', sans-serif; font-size: 8px; color: #111; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h2 { margin: 0; font-size: 13px; text-transform: uppercase; font-weight: bold; }
        .header p { margin: 3px 0; font-size: 9.5px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 8px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 3px 2px; text-align: center; word-wrap: break-word; font-size: 7.5px; }
        th { background-color: #f2f2f2; font-weight: bold; font-size: 7px; text-transform: uppercase; }
        
        .bg-gray { background-color: #e9ecef; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .col-no { width: 18px; }
        .col-name { width: 110px; }
        .col-nip { width: 75px; }
        .col-stat { width: 16px; }
        .col-kj { width: 20px; }
        .col-tier { width: 16px; }
        .col-money { width: 55px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAPITULASI PENGHITUNGAN TUNJANGAN KINERJA PEGAWAI</h2>
        <p>BALAI PENJAMINAN MUTU PENDIDIKAN (BPMP) PROVINSI KALIMANTAN TIMUR</p>
        <p>BERDASARKAN PERMENDIKDASMEN NO. 14 TAHUN 2025 - BULAN: {{ strtoupper($month) }}</p>
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
                <th colspan="5">KETERLAMBATAN (TL)</th>
                <th colspan="5">PULANG CEPAT (PSW)</th>
                <th colspan="2">ABSEN ELEKTRONIK</th>
                <th rowspan="2" style="width: 32px;">TOTAL POT (%)</th>
                <th rowspan="2" class="col-money">TUKIN KOTOR</th>
                <th rowspan="2" class="col-money">POTONGAN (RP)</th>
                <th rowspan="2" class="col-money">TUKIN BERSIH</th>
                <th rowspan="2" class="col-money">PAJAK PPh21</th>
                <th rowspan="2" class="col-money">NETTO DITERIMA</th>
            </tr>
            <tr>
                <th class="col-stat">H</th>
                <th class="col-stat">DL</th>
                <th class="col-stat">C</th>
                <th class="col-stat">S</th>
                <th class="col-stat">A</th>
                <th class="col-tier">TL1</th>
                <th class="col-tier">TL2</th>
                <th class="col-tier">TL3</th>
                <th class="col-tier">TL4</th>
                <th class="col-tier">TL5</th>
                <th class="col-tier">PSW1</th>
                <th class="col-tier">PSW2</th>
                <th class="col-tier">PSW3</th>
                <th class="col-tier">PSW4</th>
                <th class="col-tier">PSW5</th>
                <th class="col-tier">TKD</th>
                <th class="col-tier">TKP</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totBase = 0;
                $totDed = 0;
                $totNet = 0;
                $totTax = 0;
            @endphp
            @foreach($calculations as $index => $c)
                @php
                    $totBase += $c->base_amount;
                    $totDed += $c->total_deduction;
                    $totNet += $c->netto_amount;
                    $totTax += $c->tax_amount;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left font-bold">{{ $c->name }}</td>
                    <td>{{ $c->nip }}</td>
                    <td>{{ $c->golongan ?? '-' }}</td>
                    <td>{{ $c->grade }}</td>
                    
                    <td>{{ $c->hadir_count }}</td>
                    <td>{{ $c->dl_count }}</td>
                    <td>{{ $c->cuti_count }}</td>
                    <td>{{ $c->sakit_count }}</td>
                    <td style="{{ $c->alpa_count > 0 ? 'color:red; font-weight:bold;' : '' }}">{{ $c->alpa_count }}</td>

                    <td>{{ $c->tl1_count ?: '-' }}</td>
                    <td>{{ $c->tl2_count ?: '-' }}</td>
                    <td>{{ $c->tl3_count ?: '-' }}</td>
                    <td>{{ $c->tl4_count ?: '-' }}</td>
                    <td>{{ $c->tl5_count ?: '-' }}</td>

                    <td>{{ $c->psw1_count ?: '-' }}</td>
                    <td>{{ $c->psw2_count ?: '-' }}</td>
                    <td>{{ $c->psw3_count ?: '-' }}</td>
                    <td>{{ $c->psw4_count ?: '-' }}</td>
                    <td>{{ $c->psw5_count ?: '-' }}</td>

                    <td>{{ $c->tkd_count ?: '-' }}</td>
                    <td>{{ $c->tkp_count ?: '-' }}</td>

                    <td class="font-bold {{ $c->total_penalty_percent > 0 ? 'text-danger' : '' }}">{{ number_format($c->total_penalty_percent, 2) }}%</td>
                    <td class="text-right">Rp {{ number_format($c->base_amount, 0, ',', '.') }}</td>
                    <td class="text-right" style="{{ $c->total_deduction > 0 ? 'color: red;' : '' }}">Rp {{ number_format($c->total_deduction, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($c->bruto_after_deduction, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($c->tax_amount, 0, ',', '.') }}</td>
                    <td class="text-right font-bold bg-gray">Rp {{ number_format($c->netto_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="font-bold bg-gray">
            <tr>
                <td colspan="22" class="text-left">TOTAL KESELURUHAN</td>
                <td class="text-right">Rp {{ number_format($totBase, 0, ',', '.') }}</td>
                <td class="text-right" style="color: red;">Rp {{ number_format($totDed, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totBase - $totDed, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totTax, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totNet, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 25px; float: right; width: 300px; text-align: center;">
        <p>Samarinda, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        <p>Bendahara Pengeluaran,</p>
        <br><br><br>
        <p class="font-bold" style="text-decoration: underline;">Sulistyo Rahayu, S.Pd.</p>
        <p>NIP 19760929 200312 2 001</p>
    </div>
</body>
</html>
