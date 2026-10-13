<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Raport Capaian Pegawai</title>
    @php
        // Konversi Font Bookman ke Base64 jika ada
        $fontRegPath = public_path('fonts/BOOKOS.TTF');
        $fontBoldPath = public_path('fonts/BOOKOSB.TTF');
        $fontRegBase64 = file_exists($fontRegPath) ? base64_encode(file_get_contents($fontRegPath)) : '';
        $fontBoldBase64 = file_exists($fontBoldPath) ? base64_encode(file_get_contents($fontBoldPath)) : '';
    @endphp

    <style>
        @if($fontRegBase64)
        @font-face {
            font-family: 'Bookman';
            src: url(data:font/truetype;charset=utf-8;base64,{{ $fontRegBase64 }}) format('truetype');
            font-weight: normal;
        }
        @endif
        @if($fontBoldBase64)
        @font-face {
            font-family: 'Bookman';
            src: url(data:font/truetype;charset=utf-8;base64,{{ $fontBoldBase64 }}) format('truetype');
            font-weight: bold;
        }
        @endif

        @page {
            size: A4 landscape;
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }

        body {
            font-family: 'Bookman', 'Bookman Old Style', 'Times New Roman', serif;
            font-size: 9.5pt;
            line-height: 1.3;
            color: #111;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 10pt;
            margin: 0;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stats-summary {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
        }

        .stats-summary td {
            padding: 6px 12px;
            border: 1px solid #ccc;
            font-size: 9pt;
            text-align: center;
        }

        .stats-biru { background-color: #dbeafe; color: #1e40af; font-weight: bold; }
        .stats-hijau { background-color: #d1fae5; color: #065f46; font-weight: bold; }
        .stats-kuning { background-color: #fef3c7; color: #92400e; font-weight: bold; }
        .stats-merah { background-color: #fee2e2; color: #991b1b; font-weight: bold; }
        .stats-total { background-color: #f3f4f6; color: #111827; font-weight: bold; }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #444;
            padding: 5px 7px;
            vertical-align: middle;
        }

        table.data-table th {
            background-color: #1e293b;
            color: #fff;
            text-transform: uppercase;
            font-size: 8.5pt;
            font-weight: bold;
            text-align: center;
        }

        .badge-biru { background-color: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
        .badge-hijau { background-color: #d1fae5; color: #065f46; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
        .badge-kuning { background-color: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
        .badge-merah { background-color: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; font-weight: bold; }

        .footer-sig {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .sig-box {
            float: right;
            width: 280px;
            text-align: center;
            font-size: 9.5pt;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>REKAPITULASI RAPORT CAPAIAN KINERJA PEGAWAI</h1>
        <p>SISTEM INFORMASI KEPEGAWAIAN (SIPEGA) &bull; TANGGAL: {{ now()->translatedFormat('d F Y') }}</p>
    </div>

    <!-- REKAPITULASI KATEGORI WARNA -->
    <table class="stats-summary">
        <tr>
            <td class="stats-total">TOTAL PEGAWAI: {{ $stats['total'] }} ORANG (RATA-RATA: {{ $stats['avg_score'] }} PTS)</td>
            <td class="stats-biru">🔵 BIRU (> 90): {{ $stats['biru'] }} Pegawai</td>
            <td class="stats-hijau">🟢 HIJAU (76 - 90): {{ $stats['hijau'] }} Pegawai</td>
            <td class="stats-kuning">🟡 KUNING (61 - 75): {{ $stats['kuning'] }} Pegawai</td>
            <td class="stats-merah">🔴 MERAH (≤ 60): {{ $stats['merah'] }} Pegawai</td>
        </tr>
    </table>

    <!-- TABEL DATA -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No / Rank</th>
                <th style="width: 25%; text-align: left;">Nama Pegawai</th>
                <th style="width: 15%;">NIP</th>
                <th style="width: 12%;">Golongan / Ruang</th>
                <th style="width: 20%; text-align: left;">Jabatan</th>
                <th style="width: 8%;">Gugus</th>
                <th style="width: 8%;">Warna</th>
                <th style="width: 7%;">Skor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $emp)
            @php
                $color = $emp->performance_color ?: 'Merah';
                $badgeClass = match($color) {
                    'Biru' => 'badge-biru',
                    'Hijau' => 'badge-hijau',
                    'Kuning' => 'badge-kuning',
                    default => 'badge-merah'
                };
            @endphp
            <tr>
                <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                <td style="font-weight: bold;">{{ $emp->name }}</td>
                <td style="text-align: center;">{{ $emp->nip ?: '-' }}</td>
                <td style="text-align: center;">{{ \App\Models\User::formatPangkatGolongan($emp->golongan) ?: ($emp->golongan ?: '-') }}</td>
                <td>{{ $emp->position ?: 'Fungsional Umum' }}</td>
                <td style="text-align: center;">{{ $emp->gugus_mutu ?: '-' }}</td>
                <td style="text-align: center;">
                    <span class="{{ $badgeClass }}">{{ strtoupper($color) }}</span>
                </td>
                <td style="text-align: center; font-weight: bold;">
                    {{ number_format($emp->performance_score ?? 0, 1) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TANDA TANGAN PENANGGUNG JAWAB -->
    <table class="footer-sig">
        <tr>
            <td style="border: none;"></td>
            <td style="border: none; width: 300px;">
                <div class="sig-box">
                    <p>Samarinda, {{ now()->translatedFormat('d F Y') }}</p>
                    <p style="margin-bottom: 60px;">Pimpinan / Kepala Balai,</p>
                    <p style="font-weight: bold; text-decoration: underline;">Dr. Jarwoko, M.Pd.</p>
                    <p>NIP. 196803271994121001</p>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
