<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas - {{ $letter->number }}</title>
    @php
        // Konversi Gambar ke Base64 Data URI agar 100% tampil di DomPDF tanpa masalah path Windows/Linux
        $kopPath = public_path('images/kop-surat-bpmp-kaltim-2026.png');
        $kopBase64 = file_exists($kopPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath)) : '';

        $footerPath = public_path('images/footer-bpmp-ziwbk.png');
        $footerBase64 = file_exists($footerPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($footerPath)) : '';

        // Konversi Font Bookman ke Base64 agar selalu tersedia di hosting / local
        $fontRegPath = public_path('fonts/BOOKOS.TTF');
        $fontBoldPath = public_path('fonts/BOOKOSB.TTF');
        $fontItalicPath = public_path('fonts/BOOKOSI.TTF');

        $fontRegBase64 = file_exists($fontRegPath) ? base64_encode(file_get_contents($fontRegPath)) : '';
        $fontBoldBase64 = file_exists($fontBoldPath) ? base64_encode(file_get_contents($fontBoldPath)) : '';
        $fontItalicBase64 = file_exists($fontItalicPath) ? base64_encode(file_get_contents($fontItalicPath)) : '';

        \Carbon\Carbon::setLocale('id');

        // Tanggal Tanda Tangan Surat: Mode Auto (sesuai tanggal buat / created_at) vs Manual (diinput)
        if ($letter->signature_date_type === 'manual' && $letter->signed_at) {
            $tglSurat = \Carbon\Carbon::parse($letter->signed_at)->translatedFormat('d F Y');
        } elseif ($letter->created_at) {
            $tglSurat = $letter->created_at->translatedFormat('d F Y');
        } else {
            $tglSurat = date('d F Y');
        }

        // Hari & Tanggal Pelaksanaan
        if ($letter->date_start && $letter->date_end) {
            $start = \Carbon\Carbon::parse($letter->date_start);
            $end = \Carbon\Carbon::parse($letter->date_end);
            if ($start->format('Y-m-d') == $end->format('Y-m-d')) {
                $hariTanggal = $start->translatedFormat('l, d F Y');
            } else {
                $hariTanggal = $start->translatedFormat('l') . ' - ' . $end->translatedFormat('l') . ', ' . $start->format('d') . ' s.d ' . $end->translatedFormat('d F Y');
            }
        } else {
            $hariTanggal = '-';
        }

        // Hindari kata ganda "Kegiatan Kegiatan"
        $activityTitle = trim($letter->title);
        if (preg_match('/^kegiatan\s+/i', $activityTitle)) {
            $activityTitle = preg_replace('/^kegiatan\s+/i', '', $activityTitle);
        }

        // Bersihkan entitas HTML ganda pada lokasi kegiatan
        $cleanLocation = $letter->location;
        while (strpos($cleanLocation, '&amp;') !== false) {
            $cleanLocation = html_entity_decode($cleanLocation, ENT_QUOTES, 'UTF-8');
        }
    @endphp

    <style>
        @if($fontRegBase64)
        @font-face {
            font-family: 'Bookman';
            src: url(data:font/truetype;charset=utf-8;base64,{{ $fontRegBase64 }}) format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @endif
        @if($fontBoldBase64)
        @font-face {
            font-family: 'Bookman';
            src: url(data:font/truetype;charset=utf-8;base64,{{ $fontBoldBase64 }}) format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @endif
        @if($fontItalicBase64)
        @font-face {
            font-family: 'Bookman';
            src: url(data:font/truetype;charset=utf-8;base64,{{ $fontItalicBase64 }}) format('truetype');
            font-weight: normal;
            font-style: italic;
        }
        @endif

        @page {
            margin-top: 1.3cm;
            margin-left: 2.0cm;
            margin-right: 2.0cm;
            margin-bottom: 2.0cm;
        }
        body {
            font-family: 'Bookman', 'Bookman Old Style', 'URW Bookman', serif;
            font-size: 12pt;
            line-height: 1.25;
            color: #000;
        }

        /* KOP SURAT / HEADER */
        .kop-container {
            width: 100%;
            margin-bottom: 0;
            text-align: center;
        }
        .kop-container img {
            width: 100%;
            display: block;
        }

        /* FOOTER RESMI GAMBAR (SETIAP HALAMAN - TURUN 2 SPASI) */
        .page-footer {
            position: fixed;
            bottom: -45px; /* Turun 2 spasi (~24pt/30px) dari posisi sebelumnya (-15px) */
            left: 0;
            right: 0;
            text-align: center;
        }
        .footer-img {
            width: 85%;
            display: block;
            margin: 0 auto;
        }

        /* NOTIS GRATIFIKASI (HANYA DI HALAMAN TERAKHIR, DI PALING BAWAH TEPAT DI ATAS FOOTER) */
        .last-page-notice {
            position: absolute;
            bottom: 56px; /* Mengikuti turun 2 spasi sehingga tetap rapi 1 spasi di atas footer */
            left: 0;
            right: 0;
            text-align: center;
        }
        .gratifikasi-notice {
            font-family: 'Bookman', 'Bookman Old Style', 'URW Bookman', serif;
            font-size: 8pt;
            font-weight: bold;
            color: #000;
            margin: 0;
            padding: 0;
            text-align: center;
            line-height: 1.15;
            white-space: nowrap;
        }

        /* JUDUL SURAT (Jarak dengan Header 2 Spasi, Jarak Nomor dengan Paragraf 2 Spasi) */
        .judul-surat {
            text-align: center;
            margin-top: 24pt;   /* 2 spasi dari header / kop surat */
            margin-bottom: 24pt;/* 2 spasi dari nomor surat ke paragraf pertama */
        }
        .judul-surat h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .judul-surat p {
            font-size: 12pt;
            margin: 2px 0 0 0;
            line-height: 1.2;
        }

        .paragraf {
            text-align: justify;
            margin-bottom: 4px;
            font-size: 12pt;
            line-height: 1.25;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24pt;   /* 2 spasi dari paragraf sebelum tabel */
            margin-bottom: 24pt;/* 2 spasi dari tabel ke paragraf setelah tabel */
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 2.5px 5px;
            vertical-align: top;
            font-size: 11pt;
            line-height: 1.2;
        }
        table.data-table th {
            background-color: #fff;
            font-weight: bold;
            text-align: center;
        }

        .detail-kegiatan {
            width: 100%;
            margin: 3px 0 4px 0;
            border-collapse: collapse;
        }
        .detail-kegiatan td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 12pt;
            line-height: 1.25;
        }

        /* TANDA TANGAN (Jarak tanggal ke Kepala 1 spasi, Kepala ke Nama 4 spasi = 60pt, kelompok teks geser maju 0.5cm) */
        .ttd-container {
            width: 100%;
            margin-top: 14pt; /* 1 spasi dari paragraf penutup */
            page-break-inside: avoid;
        }
        .ttd-box {
            float: right;
            width: 250px;
            padding-left: 2.5em; /* Geser maju 5 karakter huruf (5 x 0.5em = 2.5em / 30pt) */
            text-align: left;
            font-size: 12pt;
        }
        .ttd-box p {
            margin: 0;
            line-height: 1.25; /* 1 spasi antara tanggal dan Kepala */
            white-space: nowrap;
            font-size: 12pt;
        }
        .stamp-area {
            height: 60pt; /* Tepat 4 spasi (4 baris x 15pt) antara Kepala dan Dr. Jarwoko, M. Pd */
            position: relative;
        }

        .page-break {
            page-break-after: always;
        }

        /* KHUSUS LAMPIRAN MODEL 4 */
        .lampiran-header {
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        .lampiran-title {
            text-align: center;
            font-size: 9.5pt;
            font-weight: bold;
            margin: 12px 0;
            line-height: 1.3;
        }
    </style>
</head>
<body>

    <!-- FOOTER RESMI (GAMBAR TAMPIL DI SETIAP HALAMAN) -->
    <div class="page-footer">
        @if($footerBase64)
            <img class="footer-img" src="{{ $footerBase64 }}">
        @endif
    </div>

@if($letter->st_model === 'model_4')
    <!-- ======================================================= -->
    <!-- MODEL 4: HALAMAN 1 SURAT PENGANTAR (PENUGASAN TERLAMPIR) -->
    <!-- ======================================================= -->
    
    <!-- KOP SURAT RESMI -->
    <div class="kop-container">
        @if($kopBase64)
            <img src="{{ $kopBase64 }}">
        @endif
    </div>

    <div class="judul-surat">
        <h2>SURAT TUGAS</h2>
        <p>Nomor : {{ $letter->number }}</p>
    </div>

    <div class="paragraf">
        @if($letter->basis)
            {{ $letter->basis }}
        @elseif($letter->invitation_from && $letter->invitation_number)
            Berdasarkan surat dari {{ $letter->invitation_from }}, nomor {{ $letter->invitation_number }}, tanggal {{ $letter->invitation_date ? $letter->invitation_date->translatedFormat('d F Y') : '-' }} perihal {{ $letter->invitation_subject ?? $letter->title }}, Maka Kepala Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur dengan ini menugaskan kepada nama, tempat, dan tanggal terlampir untuk melakukan Kegiatan {{ $letter->title }}.
        @else
            Berdasarkan Surat Kepala Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur nomor {{ $letter->number }} tanggal {{ $tglSurat }} Tentang {{ $letter->title }}, Maka Kepala Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur dengan ini menugaskan kepada nama, tempat, dan tanggal terlampir untuk melakukan Kegiatan {{ $letter->title }}.
        @endif
    </div>

    <div class="paragraf">
        Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.
    </div>

    <!-- TANDA TANGAN HALAMAN 1 -->
    <div class="ttd-container">
        <div class="ttd-box">
            <p>{{ $tglSurat }}</p>
            <p>Kepala,</p>
            <div class="stamp-area"></div>
            <p>{{ $letter->signatory_name ?? 'Dr. Jarwoko, M. Pd' }}</p>
            <p>NIP. {{ $letter->signatory_nip ?? '197003191997031001' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- PAGE BREAK MENUJU LAMPIRAN -->
    <div class="page-break"></div>

    <!-- ======================================================= -->
    <!-- MODEL 4: HALAMAN LAMPIRAN (MATRIKS 6 KOLOM)             -->
    <!-- ======================================================= -->

    <div class="lampiran-header">
        LAMPIRAN 1<br>
        KEPUTUSAN KEPALA BALAI PENJAMINAN MUTU PENDIDIKAN<br>
        PROVINSI KALIMANTAN TIMUR<br>
        NOMOR : {{ $letter->number }}<br>
        TANGGAL : {{ $tglSurat }}
    </div>

    <div class="lampiran-title">
        DAFTAR NAMA PENANGGUNG JAWAB, NARASUMBER DAN PANITIA<br>
        KEGIATAN {{ strtoupper($letter->title) }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="14%">Kab/Kota</th>
                <th width="28%">Nama & Peran</th>
                <th width="22%">Tempat Kegiatan</th>
                <th width="16%">Tanggal pelaksanaan</th>
                <th width="16%">PenanggungJawab</th>
            </tr>
        </thead>
        <tbody>
            @foreach($letter->users as $idx => $u)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}.</td>
                <td><b>{{ $u->pivot->city_destination ?? '-' }}</b></td>
                <td>
                    <b>{{ $u->name }}</b><br>
                    <span style="font-size: 8pt; color: #333;">({{ $u->pivot->custom_role ?? 'Narasumber' }})</span><br>
                    <span style="font-size: 8pt; color: #444;">NIP. {{ $u->pivot->user_nip ?? $u->nip ?? '-' }}</span>
                </td>
                <td>{{ $u->pivot->venue ?? $cleanLocation }}</td>
                <td>{{ $u->pivot->execution_dates ?? $hariTanggal }}</td>
                <td>{{ $u->pivot->person_in_charge ?? ($letter->signatory_name ?? 'Dr. Jarwoko, M.Pd.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TTD DI AKHIR LAMPIRAN -->
    <div class="ttd-container">
        <div class="ttd-box">
            <p>KEPALA BPMP<br>PROVINSI KALIMANTAN TIMUR</p>
            <div class="stamp-area"></div>
            <p style="font-weight: bold; text-decoration: underline;">{{ strtoupper($letter->signatory_name ?? 'JARWOKO') }}</p>
            <p>NIP. {{ $letter->signatory_nip ?? '197003191997031001' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- NOTIS GRATIFIKASI HANYA DI HALAMAN TERAKHIR LAMPIRAN -->
    <div class="last-page-notice">
        <div class="gratifikasi-notice">
            **Pegawai BPMP Provinsi Kalimantan Timur tidak menerima GRATIFIKASI dalam bentuk apapun dalam melaksanakan tugas**
        </div>
    </div>

@else
    <!-- ======================================================= -->
    <!-- MODEL 1, MODEL 2, MODEL 3, MODEL 5                      -->
    <!-- ======================================================= -->

    <!-- KOP SURAT RESMI -->
    <div class="kop-container">
        @if($kopBase64)
            <img src="{{ $kopBase64 }}">
        @endif
    </div>

    <div class="judul-surat">
        <h2>SURAT TUGAS</h2>
        <p>Nomor : {{ $letter->number }}</p>
    </div>

    <!-- PARAGRAF DASAR -->
    <div class="paragraf">
        @if($letter->basis)
            {{ $letter->basis }}
        @elseif($letter->invitation_from && $letter->invitation_number)
            Berdasarkan surat dari {{ $letter->invitation_from }}, nomor {{ $letter->invitation_number }}, hal {{ $letter->invitation_subject ?? $letter->title }}, maka Kepala Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur dengan ini menugaskan kepada,
        @else
            Berdasarkan agenda kedinasan Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur, maka Kepala Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur dengan ini menugaskan kepada,
        @endif
    </div>

    <!-- TABEL PEGAWAI DITUGASKAN -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No.</th>
                @if($letter->show_keterangan || $letter->st_model === 'model_5')
                    <th width="48%">Nama, NIP, Pangkat dan Golongan</th>
                    <th width="30%">Jabatan</th>
                    <th width="18%">Keterangan</th>
                @else
                    <th width="56%">Nama, NIP, Pangkat dan Golongan</th>
                    <th width="40%">Jabatan</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($letter->users as $idx => $user)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}.</td>
                <td>
                    <b>{{ $user->name }}</b><br>
                    NIP. {{ $user->pivot->user_nip ?? $user->nip ?? '-' }}<br>
                    {{ $user->pivot->user_golongan ?? $user->golongan ?? 'Pembina Tingkat I, IV/b' }}
                </td>
                <td>
                    {{ $user->pivot->user_position ?? $user->position ?? 'Widyaprada Ahli Madya' }}
                </td>
                @if($letter->show_keterangan || $letter->st_model === 'model_5')
                <td style="text-align: center; font-weight: bold;">
                    {{ $user->pivot->keterangan ?? '-' }}
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TUJUAN, HARI/TANGGAL & TEMPAT -->
    <div class="paragraf">
        @if($letter->purpose)
            {{ $letter->purpose }}
        @else
            Untuk mengikuti Kegiatan {{ $activityTitle }}, yang akan dilaksanakan pada:
        @endif
    </div>

    <table class="detail-kegiatan">
        <tr>
            <td width="130">Hari, Tanggal</td>
            <td width="10">:</td>
            <td>{{ $hariTanggal }}</td>
        </tr>
        <tr>
            <td>Tempat</td>
            <td>:</td>
            <td>{{ $cleanLocation }}</td>
        </tr>
    </table>

    <!-- DIPA & PENUTUP -->
    <div class="paragraf" style="margin-top: 6px;">
        @if($letter->dipa_source)
            Kegiatan ini dibebankan pada {{ $letter->dipa_source }}, surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan membuat laporan setelah 5 hari kerja.
        @else
            Surat tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab dan membuat laporan setelah 5 hari kerja.
        @endif
    </div>

    <!-- TANDA TANGAN (PERSIS GAMBAR 2: TANPA UNDERLINE, FONT REGULAR) -->
    <div class="ttd-container">
        <div class="ttd-box">
            <p>{{ $tglSurat }}</p>
            <p>Kepala,</p>
            <div class="stamp-area"></div>
            <p>{{ $letter->signatory_name ?? 'Dr. Jarwoko, M. Pd' }}</p>
            <p>NIP. {{ $letter->signatory_nip ?? '197003191997031001' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- NOTIS GRATIFIKASI HANYA DI HALAMAN TERAKHIR -->
    <div class="last-page-notice">
        <div class="gratifikasi-notice">
            **Pegawai BPMP Provinsi Kalimantan Timur tidak menerima GRATIFIKASI dalam bentuk apapun dalam melaksanakan tugas**
        </div>
    </div>

@endif

</body>
</html>
