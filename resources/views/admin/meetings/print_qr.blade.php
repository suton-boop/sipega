<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Rapat - {{ $meeting->title }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif;
            text-align: center;
            padding: 50px;
            color: #003366;
        }

        .container {
            border: 15px solid #003366;
            padding: 40px;
            border-radius: 40px;
            max-width: 700px;
            margin: auto;
            background: white;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }

        .header-logo {
            font-size: 40px;
            font-weight: 900;
            letter-spacing: -2px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .header-logo span {
            color: #FF6600;
        }

        .qr-box {
            margin: 30px 0;
            padding: 20px;
            background: #f8fafc;
            border-radius: 30px;
            display: inline-block;
        }

        .meeting-title {
            font-size: 28px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .meeting-info {
            font-size: 16px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .footer-text {
            font-size: 14px;
            font-weight: 500;
            color: #94a3b8;
            margin-top: 30px;
        }

        .btn-print {
            margin-top: 30px;
            background: #003366;
            color: white;
            padding: 15px 30px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 900;
            display: inline-block;
        }

        @media print {
            .btn-print { display: none; }
            body { padding: 0; }
            .container { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-logo">SI<span>PEGA</span></div>
        <div class="meeting-info">Presensi Kehadiran Rapat</div>
        <h1 class="meeting-title">{{ $meeting->title }}</h1>
        <div class="meeting-info">
            📅 {{ \Carbon\Carbon::parse($meeting->date)->format('d F Y') }} <br>
            📍 {{ $meeting->location_name }}
        </div>

        <div class="qr-box">
            {!! $qrCode !!}
        </div>

        <div class="meeting-info">Scan Using SIPEGA Mobile App</div>
        <p class="footer-text">Pastikan GPS Anda Aktif & Berada di Lokasi Rapat</p>

        <a href="javascript:window.print()" class="btn-print">CETAK BARCODE 🖨️</a>
    </div>
</body>
</html>
