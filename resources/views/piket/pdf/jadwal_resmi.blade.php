<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Resmi Piket Pelayanan - {{ $schedule->unit->name }} ({{ $schedule->year }})</title>
    @php
        $kopPath = public_path('images/kop-surat-bpmp-kaltim-2026.png');
        $kopBase64 = file_exists($kopPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath)) : '';
        \Carbon\Carbon::setLocale('id');
    @endphp
    <style>
        @page {
            size: a4 landscape;
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.3;
        }
        .header-kop {
            width: 100%;
            margin-bottom: 12px;
            text-align: center;
        }
        .header-kop img {
            width: 90%;
            max-height: 100px;
        }
        .title-box {
            text-align: center;
            margin-bottom: 15px;
        }
        .title-main {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .title-sub {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 2px;
        }
        .title-doc {
            font-size: 9.5pt;
            margin-top: 4px;
            font-style: italic;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #333;
            padding: 5px 6px;
            font-size: 9pt;
        }
        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .signature-box {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            border: none;
        }
        .signature-table td {
            border: none;
            padding: 2px;
            vertical-align: top;
            font-size: 10pt;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT BPMP PROVINSI KALIMANTAN TIMUR -->
    <div class="header-kop">
        @if($kopBase64)
            <img src="{{ $kopBase64 }}">
        @else
            <img src="{{ public_path('images/kop-surat-bpmp-kaltim-2026.png') }}">
        @endif
    </div>

    <!-- JUDUL DOKUMEN RESMI -->
    <div class="title-box">
        <div class="title-main">JADWAL RESMI PETUGAS PIKET PELAYANAN</div>
        <div class="title-sub">{{ strtoupper($schedule->unit->name) }} ({{ $schedule->unit->code }}) - TAHUN {{ $schedule->year }}</div>
        @if($schedule->publication)
            <div class="title-doc">
                Lampiran Keputusan / Surat Tugas No: {{ $schedule->publication->document_number }} | Tanggal Berlaku: {{ \Carbon\Carbon::parse($schedule->publication->effective_date)->translatedFormat('d F Y') }}
            </div>
        @endif
    </div>

    <!-- TABEL JADWAL PELAYANAN -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 130px;">Hari & Tanggal</th>
                <th style="width: 250px;">Petugas Utama</th>
                <th style="width: 200px;">Petugas Cadangan</th>
                <th>Keterangan / Status Petugas</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($assignments as $dateKey => $items)
                @php
                    $first = $items->first();
                    $dateObj = \Carbon\Carbon::parse($dateKey);
                    $primaryOfficers = $items->map(function($i) {
                        $name = $i->activeOfficer->name ?? $i->primaryOfficer->name ?? '-';
                        $nip = $i->activeOfficer->nip ?? $i->primaryOfficer->nip ?? '';
                        return $i->role_label . ': ' . $name . ($nip ? ' (' . $nip . ')' : '');
                    })->join('<br>');

                    $backupOfficers = $items->map(function($i) {
                        if ($i->backupOfficer) {
                            return $i->backupOfficer->name . ($i->backupOfficer->nip ? ' (' . $i->backupOfficer->nip . ')' : '');
                        }
                        return null;
                    })->filter()->unique()->join('<br>');

                    $notes = $items->map(function($i) {
                        if ($i->is_replaced) {
                            return 'Digantikan: ' . ($i->activeOfficer->name ?? '') . ' (' . ($i->notes ?? 'Penugasan') . ')';
                        }
                        return null;
                    })->filter()->join('; ');
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-left font-bold">
                        {{ $dateObj->translatedFormat('l, d F Y') }}
                    </td>
                    <td class="text-left">
                        {!! $primaryOfficers !!}
                    </td>
                    <td class="text-left">
                        {!! $backupOfficers ?: '<span style="color: #888; font-style: italic;">Sesuai daftar siaga</span>' !!}
                    </td>
                    <td class="text-left" style="font-size: 8.5pt;">
                        {!! $notes ?: 'Bertugas normal' !!}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">
                        Belum ada data jadwal pelayanan yang digenerate.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN PEJABAT PENGESAH -->
    <div class="signature-box">
        <table class="signature-table">
            <tr>
                <td style="width: 60%;">
                    <div style="font-size: 8.5pt; color: #555;">
                        <strong>Ketentuan Petugas:</strong><br>
                        1. Petugas wajib hadir 15 menit sebelum jam operasional loket dimulai (08:00 WITA).<br>
                        2. Apabila berhalangan hadir karena dinas luar/cuti, wajib mengajukan mekanisme penggantian petugas melalui SIPEGA.<br>
                        3. Menjaga ketertiban, kebersihan, dan keramahtamahan dalam melayani seluruh pemangku kepentingan.
                    </div>
                </td>
                <td style="width: 40%; text-align: left; padding-left: 40px;">
                    Samarinda, {{ $schedule->publication && $schedule->publication->effective_date ? \Carbon\Carbon::parse($schedule->publication->effective_date)->translatedFormat('d F Y') : \Carbon\Carbon::now('Asia/Makassar')->translatedFormat('d F Y') }}<br>
                    <strong>{{ $schedule->publication->signatory_position ?? 'Kepala BPMP Provinsi Kalimantan Timur' }}</strong>
                    <br><br><br><br>
                    <strong><u>{{ $schedule->publication->signatory_name ?? 'Dr. Jarwoko, M.Pd.' }}</u></strong><br>
                    NIP. {{ $schedule->publication->signatory_nip ?? '196901011993031002' }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
