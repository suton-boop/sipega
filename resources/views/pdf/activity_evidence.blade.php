<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Dukung e-Kinerja - {{ $item->plan_description }}</title>
    <style>
        @page {
            margin: 1.5cm 2cm 2cm 2cm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.45;
        }
        .header {
            text-align: center;
            border-bottom: 2.5px solid #003366;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header-instansi {
            font-size: 11pt;
            font-weight: bold;
            color: #003366;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .header-unit {
            font-size: 13pt;
            font-weight: 900;
            color: #002244;
            text-transform: uppercase;
            margin: 2px 0;
        }
        .header-address {
            font-size: 8pt;
            color: #64748b;
            margin: 0;
        }
        .title-box {
            text-align: center;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 18px;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: 900;
            text-transform: uppercase;
            color: #003366;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .doc-subtitle {
            font-size: 8pt;
            color: #64748b;
            margin: 2px 0 0;
            font-style: italic;
        }
        .section-label {
            font-size: 9pt;
            font-weight: 900;
            text-transform: uppercase;
            color: #003366;
            margin: 12px 0 6px 0;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }
        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.info-table td {
            padding: 3px 6px;
            vertical-align: top;
            font-size: 9.5pt;
        }
        table.info-table td.label {
            width: 28%;
            font-weight: bold;
            color: #334155;
        }
        table.info-table td.colon {
            width: 2%;
            text-align: center;
        }
        table.info-table td.value {
            width: 70%;
            color: #0f172a;
        }
        table.ekinerja-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 16px;
        }
        table.ekinerja-table th, table.ekinerja-table td {
            border: 1px solid #94a3b8;
            padding: 8px 10px;
            text-align: left;
            font-size: 9pt;
        }
        table.ekinerja-table th {
            background-color: #003366;
            color: #ffffff;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .photo-container {
            text-align: center;
            margin: 14px 0;
            padding: 10px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
        }
        .photo-img {
            max-width: 480px;
            max-height: 260px;
            border: 1px solid #94a3b8;
            border-radius: 4px;
        }
        .photo-caption {
            font-size: 8pt;
            color: #64748b;
            margin-top: 6px;
            font-style: italic;
        }
        .footer-table {
            width: 100%;
            margin-top: 24px;
            border-collapse: collapse;
        }
        .footer-table td {
            vertical-align: top;
            width: 50%;
        }
        .sig-box {
            text-align: center;
        }
        .sig-date {
            font-size: 9pt;
            margin-bottom: 4px;
        }
        .sig-role {
            font-weight: bold;
            font-size: 9.5pt;
            margin-bottom: 50px;
        }
        .sig-name {
            font-weight: 900;
            text-decoration: underline;
            font-size: 10pt;
            margin: 0;
        }
        .sig-nip {
            font-size: 8.5pt;
            color: #475569;
            margin: 2px 0 0;
        }
        .verification-badge {
            display: inline-block;
            border: 1.5px solid #059669;
            background-color: #ecfdf5;
            color: #065f46;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 8pt;
            font-weight: bold;
            text-align: left;
            line-height: 1.35;
        }
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 7.5pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            text-align: justify;
        }
    </style>
</head>
<body>
    <!-- KOP RESMI -->
    <div class="header">
        <h2 class="header-instansi">KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h2>
        <h1 class="header-unit">BALAI PENJAMINAN MUTU PENDIDIKAN PROVINSI KALIMANTAN TIMUR</h1>
        <p class="header-address">Jl. Cipto Mangunkusumo Km. 2, Samarinda Seberang, Kota Samarinda, Kalimantan Timur 75131</p>
    </div>

    <!-- TITLE DOKUMEN -->
    <div class="title-box">
        <h3 class="doc-title">BUKTI DUKUNG LAPORAN KEGIATAN HARIAN (e-KINERJA BKN)</h3>
        <p class="doc-subtitle">Dokumen Verifikasi Pelaksanaan Tugas & Logbook Kinerja ASN</p>
    </div>

    <!-- IDENTITAS PEGAWAI -->
    <div class="section-label">I. Identitas Pegawai</div>
    <table class="info-table">
        <tr>
            <td class="label">Nama Pegawai</td>
            <td class="colon">:</td>
            <td class="value"><strong>{{ $user->name }}</strong></td>
        </tr>
        <tr>
            <td class="label">NIP</td>
            <td class="colon">:</td>
            <td class="value">{{ $user->nip ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pangkat / Golongan</td>
            <td class="colon">:</td>
            <td class="value">{{ $user->golongan ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td class="colon">:</td>
            <td class="value">{{ $user->position ?: 'Pegawai ASN' }}</td>
        </tr>
        <tr>
            <td class="label">Unit Kerja</td>
            <td class="colon">:</td>
            <td class="value">BPMP Provinsi Kalimantan Timur</td>
        </tr>
    </table>

    <!-- DETAIL KEGIATAN SESUAI STANDAR 7 KOLOM E-KINERJA -->
    <div class="section-label">II. Rincian Pelaksanaan Kegiatan (7 Kolom BKN)</div>
    <table class="ekinerja-table">
        <thead>
            <tr>
                <th width="28%">Komponen e-Kinerja</th>
                <th width="72%">Uraian & Data Kegiatan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>1. RHK Terkait</strong></td>
                <td>{{ $item->rhk_text ?: 'Melaksanakan tugas kedinasan dan penugasan pimpinan' }}</td>
            </tr>
            <tr>
                <td><strong>2. Tanggal Pelaksanaan</strong></td>
                <td><strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</strong></td>
            </tr>
            <tr>
                <td><strong>3. Jam Pelaksanaan</strong></td>
                <td>{{ $item->start_time ?: '08:00' }} s.d {{ $item->end_time ?: '10:00' }} WITA</td>
            </tr>
            <tr>
                <td><strong>4. Uraian Kegiatan (Kolom 5)</strong></td>
                <td><strong>{{ $item->plan_description }}</strong></td>
            </tr>
            <tr>
                <td><strong>5. Realisasi / Output (Kolom 6)</strong></td>
                <td>{{ $item->realization_notes ?: ($item->status === 'completed' ? 'Telah dilaksanakan dengan baik dan terdokumentasi.' : 'Dalam pelaksanaan / progres kerja harian.') }}</td>
            </tr>
            <tr>
                <td><strong>6. Status Kinerja</strong></td>
                <td>{{ strtoupper($item->status ?: 'COMPLETED') }} (Tercatat Resmi di SIPEGA)</td>
            </tr>
        </tbody>
    </table>

    <!-- FOTO DOKUMENTASI FISIK JIKA ADA -->
    @if(!empty($photoBase64))
        <div class="section-label">III. Dokumentasi Bukti Fisik</div>
        <div class="photo-container">
            <img src="{{ $photoBase64 }}" class="photo-img">
            <div class="photo-caption">
                Dokumentasi Kegiatan • Diambil pada: {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }} • Tersemat Watermark SIPEGA
            </div>
        </div>
    @endif

    <!-- TANDA TANGAN & PENGESAHAN ELEKTRONIK -->
    <table class="footer-table">
        <tr>
            <td style="padding-right: 15px;">
                <div class="verification-badge">
                    <strong style="color: #047857;">VERIFIKASI SISTEM ELEKTRONIK SIPEGA</strong><br>
                    <span>ID Berkas: <code>BKT-{{ str_pad($item->id, 6, '0', STR_PAD_LEFT) }}</code></span><br>
                    <span>Waktu Cetak: {{ \Carbon\Carbon::now('Asia/Makassar')->format('d/m/Y H:i:s') }} WITA</span><br>
                    <span>Integritas Dokumen: Otentik & Terdaftar di Cloud Storage BPMP</span>
                </div>
            </td>
            <td>
                <div class="sig-box">
                    <div class="sig-date">Samarinda, {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</div>
                    <div class="sig-role">Pegawai yang Bersangkutan,</div>
                    <h4 class="sig-name">{{ $user->name }}</h4>
                    <p class="sig-nip">NIP. {{ $user->nip ?: '-' }}</p>
                </div>
            </td>
        </tr>
    </table>

    <!-- FOOTER RESMI -->
    <div class="page-footer">
        Dokumen bukti dukung resmi dihasilkan secara otomatis oleh Sistem Informasi Performa & Penugasan (SIPEGA) BPMP Provinsi Kalimantan Timur untuk pemenuhan eviden aplikasi e-Kinerja BKN.
    </div>
</body>
</html>