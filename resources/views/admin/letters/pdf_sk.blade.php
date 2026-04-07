<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keputusan - {{ $letter->number ?? 'Draft' }}</title>
    <style>
        @page {
            margin: 0cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            margin: 1.5cm 1.5cm 2cm 1.5cm;
            color: #000;
        }
        .kop {
            width: 100%;
            margin-bottom: 20px;
        }
        .header-content {
            text-align: right;
            color: #0072bc;
            font-size: 9pt;
        }
        .header-content h1 {
            font-size: 10pt;
            margin: 0;
            font-weight: bold;
        }
        .header-content p {
            margin: 1px 0;
        }

        .judul-container {
            text-align: center;
            margin-bottom: 25px;
            text-transform: uppercase;
        }
        .judul-container h3 {
            font-size: 12pt;
            margin: 0;
            font-weight: bold;
        }
        .judul-container p {
            margin: 5px 0;
            font-size: 11pt;
            font-weight: bold;
        }

        .content {
            text-align: justify;
            margin-bottom: 20px;
        }
        
        table.konsideran {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.konsideran td {
            vertical-align: top;
            padding: 2px 0;
        }

        .ttd-section {
            width: 100%;
            margin-top: 30px;
        }
        .ttd-right {
            float: right;
            width: 250px;
            text-align: left;
        }
        .stamp-space {
            height: 70px;
        }

        .page-break {
            page-break-after: always;
        }

        table.lampiran {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table.lampiran th, table.lampiran td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 10pt;
        }

        .footer-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
        }
        .footer-banner img {
            width: 100%;
            display: block;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT (Full Image) -->
    <div class="kop">
        <img src="{{ public_path('images/Kop Surat BPMP Kaltim 2026 oke.png') }}" style="width: 100%;">
    </div>

    <div class="judul-container">
        <h3>KEPUTUSAN KEPALA BALAI PENJAMINAN MUTU PENDIDIKAN KALIMANTAN TIMUR</h3>
        <p>Nomor : {{ $letter->number ?? '.../C6.24/KP.10.00/2026' }}</p>
        <p style="margin-top: 15px;">TENTANG</p>
        <h3 style="margin-bottom: 15px;">{{ $letter->title }}</h3>
        <p>KEPALA BALAI PENJAMINAN MUTU PENDIDIKAN PROVINSI KALIMANTAN TIMUR,</p>
    </div>

    <div class="content">
        <table class="konsideran">
            <tr>
                <td width="85">Menimbang</td>
                <td width="10">:</td>
                <td>
                    <ol style="margin: 0; padding-left: 20px;" type="a">
                        <li>bahwa untuk kelancaran pelaksanaan {{ $letter->title }}, dipandang perlu membentuk Tim Pelaksana;</li>
                        <li>bahwa nama-nama yang tercantum dalam lampiran keputusan ini dipandang mampu mengemban tugas tersebut;</li>
                    </ol>
                </td>
            </tr>
            <tr>
                <td>Mengingat</td>
                <td>:</td>
                <td>
                    <ol style="margin: 0; padding-left: 20px;" type="1">
                        <li>Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;</li>
                        <li>Peraturan Pemerintah Nomor 4 Tahun 2022 tentang Standar Nasional Pendidikan;</li>
                    </ol>
                </td>
            </tr>
        </table>

        <div style="text-align: center; font-weight: bold; margin: 15px 0;">MEMUTUSKAN:</div>

        <table class="konsideran">
            <tr>
                <td width="85">Menetapkan</td>
                <td width="10">:</td>
                <td style="font-weight: bold; text-transform: uppercase;">KEPUTUSAN KEPALA BPMP TENTANG {{ $letter->title }}</td>
            </tr>
            <tr>
                <td>KESATU</td>
                <td>:</td>
                <td>Menetapkan Susunan Tim Pelaksana sebagaimana tercantum dalam lampiran keputusan ini.</td>
            </tr>
            <tr>
                <td>KEDUA</td>
                <td>:</td>
                <td>Tugas sebagaimana dimaksud pada diktum KESATU adalah merencanakan dan melaporkan hasil kegiatan.</td>
            </tr>
            <tr>
                <td>KETIGA</td>
                <td>:</td>
                <td>Biaya pelaksanaan dibebankan pada DIPA BPMP Provinsi Kalimantan Timur.</td>
            </tr>
        </table>
    </div>

    <div class="ttd-section">
        <div class="ttd-right" style="margin-top: 20px;">
            <p>Ditetapkan di Samarinda</p>
            <p>Pada tanggal {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p>Kepala,</p>
            <div class="stamp-space"></div>
            <p style="font-weight: bold; text-decoration: underline;">Dr. Jarwoko, M.Pd</p>
            <p>NIP 197003191997031001</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="page-break"></div>

    <!-- LAMPIRAN -->
    <div style="font-size: 10pt; text-align: right; margin-bottom: 20px;">
        Lampiran Keputusan Kepala BPMP Kaltim<br>
        Nomor : {{ $letter->number ?? 'Draft' }}<br>
        Tanggal : {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
    </div>

    <h3 style="text-align: center; text-transform: uppercase; font-size: 11pt;">LAMPIRAN SUSUNAN TIM<br>{{ $letter->title }}</h3>

    <table class="lampiran">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th width="5%">No.</th>
                <th width="45%">Nama / NIP</th>
                <th width="30%">Pangkat / Golongan</th>
                <th width="20%">Kedudukan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($letter->users as $idx => $user)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td><b>{{ $user->name }}</b><br>NIP {{ $user->nip ?? '-' }}</td>
                <td style="text-align: center;">{{ $user->golongan ?? '-' }}</td>
                <td style="text-align: center;">Anggota</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-banner">
        @if(file_exists(public_path('images/footer bpmp.png')))
            <img src="{{ public_path('images/footer bpmp.png') }}">
        @endif
    </div>

</body>
</html>
