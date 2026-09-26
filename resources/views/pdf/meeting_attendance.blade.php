<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daftar Hadir - {{ $meeting->title }}</title>
    @php
        $kopPath = public_path('images/kop-surat-bpmp-kaltim-2026.png');
        if (!file_exists($kopPath)) {
            $kopPath = public_path('images/kop-surat-bpmp-kaltim-2026.PNG');
        }
        $kopBase64 = file_exists($kopPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath)) : '';
        \Carbon\Carbon::setLocale('id');
    @endphp
    <style>
        @page {
            margin: 1cm 1.5cm 1.5cm 1.5cm;
        }
        body { 
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            font-size: 10pt;
            line-height: 1.3;
        }
        .kop-container {
            width: 100%;
            text-align: center;
            margin-bottom: 15px;
        }
        .kop-img {
            width: 100%;
            height: auto;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .title { 
            font-size: 14pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            color: #000;
            text-decoration: underline;
            margin-bottom: 6px;
        }
        .meta-title { 
            font-size: 11pt; 
            font-weight: bold; 
            text-transform: uppercase;
            color: #222;
            margin-bottom: 4px;
        }
        .meta { 
            font-size: 9.5pt; 
            color: #444; 
            margin-top: 2px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        th, td { 
            border: 1px solid #444; 
            padding: 8px 10px; 
            text-align: left; 
            font-size: 9.5pt; 
            vertical-align: middle;
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            text-transform: uppercase; 
            text-align: center;
            font-size: 9pt;
            letter-spacing: 0.5px;
        }
        .footer { 
            margin-top: 30px; 
            font-size: 8.5pt; 
            text-align: right; 
            color: #777; 
            font-style: italic;
        }
    </style>
</head>
<body>
    @if(!empty($kopBase64))
        <div class="kop-container">
            <img src="{{ $kopBase64 }}" class="kop-img">
        </div>
    @elseif(file_exists(public_path('images/kop-surat-bpmp-kaltim-2026.png')))
        <div class="kop-container">
            <img src="{{ public_path('images/kop-surat-bpmp-kaltim-2026.png') }}" class="kop-img">
        </div>
    @endif

    <div class="header">
        <div class="title">DAFTAR HADIR</div>
        <div class="meta-title">{{ $meeting->title }}</div>
        <div class="meta">
            Tanggal: {{ \Carbon\Carbon::parse($meeting->date)->translatedFormat('l, d F Y') }} 
            &nbsp;|&nbsp; 
            Pukul: {{ $meeting->start_time }} WITA
            @if($meeting->location_name)
                &nbsp;|&nbsp; 
                Tempat: {{ $meeting->location_name }}
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">No</th>
                <th>Nama Pegawai</th>
                <th>NIP</th>
                <th>Jabatan / Role</th>
                <th style="text-align: center; width: 110px;">Waktu Presensi</th>
                <th style="text-align: center; width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($meeting->logs as $log)
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td style="font-weight: bold;">{{ $log->user->name }}</td>
                <td>{{ $log->user->nip ?? '-' }}</td>
                <td>{{ $log->user->position ?: ($log->user->role ?? '-') }}</td>
                <td style="text-align: center;">{{ \Carbon\Carbon::parse($log->check_in_time)->format('H:i:s') }} WITA</td>
                <td style="text-align: center; font-weight: bold; color: #15803d;">Hadir</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #888; font-style: italic; padding: 20px;">
                    Belum ada data presensi yang terekam untuk kegiatan ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis melalui Sistem SIPEGA pada {{ now('Asia/Makassar')->translatedFormat('d F Y H:i:s') }} WITA
    </div>
</body>
</html>
