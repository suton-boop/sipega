<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Tugas - {{ $letter->letter_number }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 14pt;
            margin: 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 12pt;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .header p {
            font-size: 10pt;
            margin: 0;
        }
        .letter-info {
            text-align: center;
            margin-bottom: 30px;
        }
        .letter-info h3 {
            text-decoration: underline;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .content {
            margin-bottom: 30px;
        }
        .employee-list {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .employee-list th, .employee-list td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .employee-list th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            position: relative;
        }
        .signature {
            float: right;
            width: 250px;
            text-align: center;
        }
        .qr-code {
            float: left;
            margin-top: 20px;
        }
        .clear {
            clear: both;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.1;
            font-size: 60pt;
            font-weight: bold;
            color: #000;
            z-index: -1000;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    @if($letter->is_private)
        <div class="watermark">RAHASIA / PRIVATE</div>
    @endif

    <div class="header">
        <h1>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h1>
        <h2>BALAI PENJAMINAN MUTU PENDIDIKAN</h2>
        <h2>PROVINSI KALIMANTAN TIMUR</h2>
        <p>Jl. Hemat No. 1, Samarinda, Kalimantan Timur</p>
    </div>

    <div class="letter-info">
        <h3>SURAT TUGAS</h3>
        <p>Nomor: {{ $letter->letter_number ?? '.../BPMP-KT/ST/2026' }}</p>
    </div>

    <div class="content">
        <p>Kepala Balai Penjaminan Mutu Pendidikan (BPMP) Provinsi Kalimantan Timur, dengan ini menugaskan kepada pegawai yang namanya tercantum di bawah ini:</p>

        <table class="employee-list">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Pegawai</th>
                    <th>NIP</th>
                    <th>Jabatan/Role</th>
                </tr>
            </thead>
            <tbody>
                @foreach($letter->users as $index => $user)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->nip ?? '-' }}</td>
                    <td>{{ $user->role }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p>Untuk melaksanakan tugas sebagai berikut:</p>
        <div style="margin-left: 20px; font-weight: bold;">
            {{ $letter->title }}
        </div>
        
        <p>Kegiatan tersebut akan dilaksanakan pada:</p>
        <table style="margin-left: 20px;">
            <tr>
                <td width="100px">Tanggal</td>
                <td>: {{ $letter->date->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td>: {{ $letter->description ?? 'Terlampir dalam agenda sistem SIPEGA' }}</td>
            </tr>
        </table>

        <p style="margin-top: 20px;">Demikian surat tugas ini dibuat untuk dilaksanakan dengan sebaik-baiknya dan penuh tanggung jawab.</p>
    </div>

    <div class="footer">
        <div class="qr-code">
            <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate(url('/st/verify/' . $letter->id))) !!} ">
            <p style="font-size: 8pt; margin: 5px 0;">Scan untuk verifikasi SIPEGA</p>
        </div>

        <div class="signature">
            <p>Samarinda, {{ now()->translatedFormat('d F Y') }}</p>
            <p>Kepala BPMP Provinsi Kalimantan Timur,</p>
            <br><br><br>
            <p><strong>Dr. Sutono, M.Pd</strong></p>
            <p>NIP. 197501012000031001</p>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>
