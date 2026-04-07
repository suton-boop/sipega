<!DOCTYPE html>
<html>
<head>
    <title>Daftar Pembayaran Tukin - {{ $month }}</title>
    <style>
        @page { size: a4 landscape; margin: 1cm; }
        body { font-family: 'Arial', sans-serif; font-size: 9px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h3 { margin: 0; font-size: 11px; text-decoration: underline; text-transform: uppercase; }
        .header p { margin: 5px 0; font-size: 10px; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; word-wrap: break-word; }
        th { background-color: #d1d5db; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .bg-gray { background-color: #f3f4f6; }
        
        /* Column Widths to match Excel proportion */
        .col-no { width: 30px; }
        .col-grade { width: 40px; }
        .col-name { width: 150px; }
        .col-job { width: 150px; }
        .col-gol { width: 40px; }
        .col-money { width: 70px; }
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
                <th style="width: 50px;">% Pengurangan Kehadiran</th>
                <th class="col-money">Rupiah</th>
                <th class="col-money">Faktor Pengurang</th>
                <th class="col-money">Jumlah Netto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $index => $user)
                @php
                    $tukinResult = $user->calculateMonthlyTukin($month);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->jobClass->class_name ?? 'N/A' }}</td>
                    <td class="text-left font-bold">{{ $user->name }}</td>
                    <td class="text-left">{{ $user->position ?? '-' }}</td>
                    <td>{{ $user->golongan ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($tukinResult['base_amount'], 0, ',', '.') }}</td>
                    <td>{{ $tukinResult['total_penalty_percentage'] > 0 ? number_format($tukinResult['total_penalty_percentage'], 1, ',', '.') : '' }}</td>
                    <td class="text-right">Rp {{ number_format($tukinResult['bruto_amount'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ $tukinResult['total_penalty_amount'] > 0 ? 'Rp ' . number_format($tukinResult['total_penalty_amount'], 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-bold bg-gray">Rp {{ number_format($tukinResult['bruto_amount'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="font-bold bg-gray">
            @php
                $totalPlafon = $users->sum(fn($u) => $u->calculateMonthlyTukin($month)['base_amount']);
                $totalPotongan = $users->sum(fn($u) => $u->calculateMonthlyTukin($month)['total_penalty_amount']);
                $totalNetto = $users->sum(fn($u) => $u->calculateMonthlyTukin($month)['bruto_amount']);
            @endphp
            <tr>
                <td colspan="5">TOTAL</td>
                <td class="text-right">Rp {{ number_format($totalPlafon, 0, ',', '.') }}</td>
                <td>-</td>
                <td class="text-right">Rp {{ number_format($totalNetto, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalPotongan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalNetto, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px; width: 100%;">
        <table style="border: none; width: 100%;">
            <tr style="border: none;">
                <td style="border: none; width: 40%; text-align: center;">
                    <p>Mengetahui,</p>
                    <p>Pejabat Pembuat Komitmen</p>
                    <br><br><br><br>
                    <p class="font-bold" style="text-decoration: underline;">NAMA PPK</p>
                    <p>NIP. XXXXXXXXXXXXXXXXXX</p>
                </td>
                <td style="border: none; width: 20%;"></td>
                <td style="border: none; width: 40%; text-align: center;">
                    <p>Samarinda, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    <p>Bendahara Pengeluaran</p>
                    <br><br><br><br>
                    <p class="font-bold" style="text-decoration: underline;">NAMA BENDAHARA</p>
                    <p>NIP. XXXXXXXXXXXXXXXXXX</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
