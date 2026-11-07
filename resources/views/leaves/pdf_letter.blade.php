<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permohonan Cuti - {{ $leave->employee_name }}</title>
    @php
        $kopPath = public_path('images/kop-surat-bpmp-kaltim-2026.png');
        $kopBase64 = file_exists($kopPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath)) : '';
        \Carbon\Carbon::setLocale('id');
    @endphp
    <style>
        @page {
            size: a4 portrait;
            margin: 15mm 20mm 20mm 20mm;
        }
        body {
            font-family: 'Bookman Old Style', 'Bookman', 'Times New Roman', serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <div style="width: 100%; margin-bottom: 20px; text-align: center;">
        @if($kopBase64)
            <img src="{{ $kopBase64 }}" style="width: 100%; display: block;">
        @else
            <img src="{{ public_path('images/kop-surat-bpmp-kaltim-2026.png') }}" style="width: 100%; display: block;">
        @endif
    </div>

    <p style="margin-bottom: 25px;">
        Kepada Yth.<br>
        Kepala BPMP Provinsi Kalimantan Timur<br>
        c.q Kepala Subbagian Umum<br>
        di tempat
    </p>

    <p style="margin-bottom: 15px;">Dengan Hormat,</p>
    <p style="margin-bottom: 10px;">Saya yang bertandatangan di bawah ini :</p>

    <table style="width: 100%; border: none; margin-bottom: 20px; font-size: 11pt;">
        <tr>
            <td style="width: 120px; border: none; padding: 3px 0;">Nama</td>
            <td style="width: 15px; border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0; font-weight: bold;">{{ $leave->employee_name }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">NIP</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $leave->employee_nip }}</td>
        </tr>
        <tr>
            <td style="border: none; padding: 3px 0;">Jabatan</td>
            <td style="border: none; padding: 3px 0;">:</td>
            <td style="border: none; padding: 3px 0;">{{ $leave->employee_position }}</td>
        </tr>
    </table>

    <p style="text-align: justify; margin-bottom: 20px;">
        Dengan ini mengajukan Permohonan Cuti untuk {{ $leave->reason }} {{ $leave->duration_amount }} {{ $leave->duration_unit }} pada tanggal {{ $leave->start_date->translatedFormat('d F Y') }}@if($leave->start_date->format('Y-m-d') !== $leave->end_date->format('Y-m-d')) s.d {{ $leave->end_date->translatedFormat('d F Y') }}@endif.
    </p>

    <p style="margin-bottom: 35px;">
        Besar harapan saya permohonan ini dapat disetujui, atas perhatiannya saya ucapkan terimakasih.
    </p>

    <table style="width: 100%; border: none; margin-top: 30px;">
        <tr>
            <td style="border: none; width: 50%;"></td>
            <td style="border: none; width: 50%; text-align: left;">
                Samarinda, {{ $leave->request_date->translatedFormat('d F Y') }}<br>
                Pemohon,<br><br><br><br>
                <strong style="text-decoration: underline;">{{ $leave->employee_name }}</strong><br>
                NIP. {{ $leave->employee_nip }}
            </td>
        </tr>
    </table>

</body>
</html>
