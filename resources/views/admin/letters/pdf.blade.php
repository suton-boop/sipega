<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas - {{ $letter->number }}</title>
    <style>
        @page {
            margin: 0cm; /* Manage margins in body instead for absolute positioning in footers */
        }
        body {
            font-family: Arial, sans-serif; /* AS REQUESTED: ARIAL */
            font-size: 11pt;
            line-height: 1.3;
            margin: 1.5cm 1.5cm 2cm 1.5cm;
            color: #000;
        }
        .kop {
            width: 100%;
            margin-bottom: 20px;
        }
        .kop img {
            width: 100%; /* If using the header image from screenshot */
            max-height: 120px;
        }
        .header-content {
            text-align: right;
            color: #0072bc;
            font-size: 9pt;
        }
        .header-content h1 {
            font-size: 12pt;
            margin: 0;
            text-transform: none;
            font-weight: bold;
        }
        .header-content p {
            margin: 1px 0;
            font-weight: normal;
        }

        .judul-container {
            text-align: center;
            margin-bottom: 25px;
        }
        .judul-container h2 {
            font-size: 14pt;
            margin: 0;
            text-decoration: none;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .judul-container p {
            margin: 0;
            font-weight: bold;
        }

        .isi-surat {
            text-align: justify;
            margin-bottom: 15px;
        }
        .indent {
            text-indent: 0px;
        }

        table.pegawai {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table.pegawai th, table.pegawai td {
            border: 1px solid #000;
            padding: 8px 10px;
            vertical-align: top;
        }
        table.pegawai th {
            background-color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 10pt;
        }
        table.pegawai td {
            font-size: 10pt;
        }

        .detail-row {
            width: 100%;
            margin: 15px 0;
        }
        .detail-row td {
            padding: 3px 0;
            vertical-align: top;
        }

        .penutup {
            margin-top: 25px;
            text-align: justify;
        }

        .ttd-section {
            width: 100%;
            margin-top: 30px;
            position: relative;
        }
        .ttd-right {
            float: right;
            width: 250px;
            text-align: left;
        }
        /* Stamp/Cap simulation or space */
        .stamp-space {
            height: 80px;
            position: relative;
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
        
        .gratifikasi-notice {
            font-size: 8pt;
            text-align: center;
            font-weight: bold;
            color: #000;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT (Full Image) -->
    <div class="kop">
        <img src="{{ public_path('images/Kop Surat BPMP Kaltim 2026 oke.png') }}" style="width: 100%;">
    </div>

    <div class="judul-container">
        <h2>SURAT TUGAS</h2>
        <p>Nomor: {{ $letter->number ?? '.../C6.24/KP.10.00/2026' }}</p>
    </div>

    <div class="isi-surat">
        <p>
            {{ $letter->basis ?? ('Berdasarkan kepentingan kedinasan BPMP Provinsi Kalimantan Timur, maka Kepala Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur dengan ini menugaskan kepada,') }}
        </p>
    </div>

    <table class="pegawai">
        <thead>
            <tr>
                <th width="4%">No.</th>
                <th width="56%">Nama, NIP, Pangkat dan Golongan</th>
                <th width="40%">Jabatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($letter->users as $idx => $user)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>
                    <b>{{ $user->name }}</b><br>
                    NIP {{ $user->nip ?? '-' }}<br>
                    {{ $user->golongan ?? 'Penata Muda, III/a' }}
                </td>
                <td>
                    {{ $user->position ?? $user->role }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="isi-surat">
        <p>{{ $letter->purpose ?? ('Untuk mengikuti kegiatan ' . $letter->title . ', yang akan dilaksanakan pada:') }}</p>
        
        <table class="detail-row">
            <tr>
                <td width="150">Hari, Tanggal</td>
                <td width="10">:</td>
                <td style="font-weight: bold;">
                    @if($letter->date_start && $letter->date_end)
                        @php
                            \Carbon\Carbon::setLocale('id');
                            $start = \Carbon\Carbon::parse($letter->date_start);
                            $end = \Carbon\Carbon::parse($letter->date_end);
                        @endphp
                        @if($start->format('Y-m-d') == $end->format('Y-m-d'))
                            {{ $start->translatedFormat('l, d F Y') }}
                        @else
                            {{ $start->translatedFormat('l') }} - {{ $end->translatedFormat('l') }}, {{ $start->format('d') }} s.d {{ $end->translatedFormat('d F Y') }}
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td>Tempat</td>
                <td>:</td>
                <td style="font-weight: bold;">{{ $letter->location ?? 'BPMP Provinsi Kalimantan Timur' }}</td>
            </tr>
        </table>
    </div>

    <div class="penutup">
        <p>Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan membuat laporan setelah tugas berakhir.</p>
    </div>

    <div class="ttd-section">
        <div class="ttd-right">
            <p>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p>Kepala,</p>
            <div class="stamp-space">
                <!-- Spasi stempel -->
            </div>
            <p style="font-weight: bold; text-decoration: underline;">{{ $letter->signatory_name ?? 'Dr. Jarwoko, M.Pd' }}</p>
            <p>NIP {{ $letter->signatory_nip ?? '197003191997031001' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="footer-banner">
        <div class="gratifikasi-notice">
            **Pegawai BPMP Provinsi Kalimantan Timur tidak menerima GRATIFIKASI dalam bentuk apapun dalam melaksanakan tugas**
        </div>
        @if(file_exists(public_path('images/footer bpmp.png')))
            <img src="{{ public_path('images/footer bpmp.png') }}">
        @endif
    </div>

</body>
</html>
