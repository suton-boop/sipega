<!DOCTYPE html>
<html>
<head>
    <title>Daftar Hadir - {{ $meeting->title }}</title>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; border-bottom: 2px solid #003366; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #003366; }
        .meta { font-size: 12px; color: #666; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 12px; }
        th { background-color: #f5f5f5; font-weight: bold; text-transform: uppercase; }
        .footer { margin-top: 30px; font-size: 10px; text-align: right; color: #aaa; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Daftar Hadir Kegiatan SIPEGA</div>
        <div class="meta">{{ $meeting->title }}</div>
        <div class="meta">Tanggal: {{ \Carbon\Carbon::parse($meeting->date)->format('d F Y') }} | Waktu: {{ $meeting->start_time }} WITA</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50px; text-align: center;">No</th>
                <th>Nama Pegawai</th>
                <th>Jabatan / Role</th>
                <th>Waktu Presensi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($meeting->logs as $log)
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td>{{ $log->user->name }}</td>
                <td>{{ $log->user->role }}</td>
                <td>{{ \Carbon\Carbon::parse($log->check_in_time)->format('H:i:s') }} WITA</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem SIPEGA pada {{ now('Asia/Makassar')->format('d/m/Y H:i:s') }} WITA
    </div>
</body>
</html>
