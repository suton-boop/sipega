<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Perjalanan Dinas - {{ $report->employee_name }}</title>
    @php
        // Konversi Font Bookman ke Base64 jika ada
        $fontRegPath = public_path('fonts/BOOKOS.TTF');
        $fontBoldPath = public_path('fonts/BOOKOSB.TTF');
        $fontItalicPath = public_path('fonts/BOOKOSI.TTF');

        $fontRegBase64 = file_exists($fontRegPath) ? base64_encode(file_get_contents($fontRegPath)) : '';
        $fontBoldBase64 = file_exists($fontBoldPath) ? base64_encode(file_get_contents($fontBoldPath)) : '';
        $fontItalicBase64 = file_exists($fontItalicPath) ? base64_encode(file_get_contents($fontItalicPath)) : '';

        // Tanda tangan base64
        $signatureBase64 = null;
        if (!empty($report->signature_image)) {
            $sigPath = public_path('storage/' . $report->signature_image);
            if (!file_exists($sigPath)) {
                $sigPath = public_path($report->signature_image);
            }
            if (file_exists($sigPath)) {
                $ext = pathinfo($sigPath, PATHINFO_EXTENSION);
                $signatureBase64 = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($sigPath));
            }
        }

        // Foto-foto dokumentasi base64
        $photosList = [];
        if (!empty($report->photos) && is_array($report->photos)) {
            foreach ($report->photos as $p) {
                $path = $p['path'] ?? '';
                $caption = $p['caption'] ?? '';
                $fullPath = public_path('storage/' . $path);
                if (!file_exists($fullPath)) {
                    $fullPath = public_path($path);
                }
                if (file_exists($fullPath) && !is_dir($fullPath)) {
                    $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $photosList[] = [
                        'base64' => 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($fullPath)),
                        'caption' => $caption
                    ];
                }
            }
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
            margin-top: 2.0cm;
            margin-left: 2.5cm;
            margin-right: 2.0cm;
            margin-bottom: 2.0cm;
        }

        body {
            font-family: 'Bookman', 'Bookman Old Style', 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000;
        }

        .report-header {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 22px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-decoration: underline;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #333;
            padding: 7px 10px;
            vertical-align: top;
            font-size: 10.5pt;
        }

        .section-header {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11pt;
            text-align: left;
            padding: 8px 10px;
        }

        .col-label {
            width: 25%;
            font-weight: normal;
        }

        .col-colon {
            width: 3%;
            text-align: center;
        }

        .col-value {
            width: 72%;
            text-align: justify;
        }

        .content-narration {
            text-align: justify;
            line-height: 1.45;
            white-space: pre-line;
        }

        .signature-section {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }

        .sig-box {
            float: right;
            width: 45%;
            text-align: center;
        }

        .sig-image {
            height: 70px;
            margin: 5px auto;
            display: block;
        }

        .photo-grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .photo-grid-table td {
            border: none;
            padding: 10px;
            text-align: center;
            vertical-align: top;
            width: 50%;
        }

        .photo-img {
            max-width: 100%;
            max-height: 230px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .photo-caption {
            font-size: 9.5pt;
            font-weight: bold;
            margin-top: 6px;
            color: #333;
            text-transform: uppercase;
        }

        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    <div class="report-header">
        LAPORAN PERJALANAN DINAS
    </div>

    <table class="report-table">
        <!-- 1. IDENTITAS -->
        <tr>
            <th colspan="3" class="section-header">
                IDENTITAS YANG MELAKUKAN PERJALANAN DINAS
            </th>
        </tr>
        <tr>
            <td class="col-label">Nama</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $report->employee_name }}</td>
        </tr>
        <tr>
            <td class="col-label">NIP</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $report->employee_nip ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-label">Jabatan</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $report->employee_position ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-label">Nomor Surat Tugas</td>
            <td class="col-colon">:</td>
            <td class="col-value">
                {{ $report->st_number }}
                @if($report->st_date)
                    Tanggal {{ \Carbon\Carbon::parse($report->st_date)->translatedFormat('d F Y') }}
                @endif
            </td>
        </tr>

        <!-- 2. KEGIATAN -->
        <tr>
            <th colspan="3" class="section-header">
                KEGIATAN
            </th>
        </tr>
        <tr>
            <td class="col-label">Nama Kegiatan</td>
            <td class="col-colon">:</td>
            <td class="col-value"><strong>{{ $report->activity_name }}</strong></td>
        </tr>
        <tr>
            <td class="col-label">Tanggal</td>
            <td class="col-colon">:</td>
            <td class="col-value">{{ $report->formatted_date_range }}</td>
        </tr>
        <tr>
            <td class="col-label">Tempat</td>
            <td class="col-colon">:</td>
            <td class="col-value">{!! nl2br(e($report->location)) !!}</td>
        </tr>
        <tr>
            <td class="col-label">Tujuan</td>
            <td class="col-colon">:</td>
            <td class="col-value">{!! nl2br(e($report->purpose)) !!}</td>
        </tr>
        <tr>
            <td class="col-label">Sasaran</td>
            <td class="col-colon">:</td>
            <td class="col-value">{!! nl2br(e($report->target_audience)) !!}</td>
        </tr>
        <tr>
            <td class="col-label">Output</td>
            <td class="col-colon">:</td>
            <td class="col-value">{!! nl2br(e($report->output)) !!}</td>
        </tr>

        <!-- 3. DESKRIPSI KEGIATAN -->
        <tr>
            <td class="col-label"><strong>Deskripsi Kegiatan</strong></td>
            <td class="col-colon">:</td>
            <td class="col-value">
                <div class="content-narration">
                    {!! nl2br(e($report->description)) !!}
                </div>

                @if(!empty($report->conclusion))
                    <div style="margin-top: 15px;">
                        <strong>Penutup / Kesimpulan:</strong>
                        <div class="content-narration" style="margin-top: 4px;">
                            {!! nl2br(e($report->conclusion)) !!}
                        </div>
                    </div>
                @endif
            </td>
        </tr>

        <!-- 4. TANDA TANGAN (Dalam tabel seperti format asli) -->
        <tr>
            <td colspan="3" style="padding: 20px 25px; text-align: right; border-top: 1px solid #333;">
                <table style="width: 280px; margin-left: auto; text-align: center; border-collapse: collapse;">
                    <tr>
                        <td style="border: none; padding: 2px;">
                            {{ $report->formatted_signed_date }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px 2px 5px 2px;">
                            Pelaksana Perjadin,
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; height: 75px; vertical-align: middle;">
                            @if($signatureBase64)
                                <img src="{{ $signatureBase64 }}" style="max-height: 70px; max-width: 180px; display: inline-block;">
                            @else
                                <div style="height: 65px;"></div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px; font-weight: bold; text-decoration: underline;">
                            {{ $report->employee_name }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2px; font-size: 10pt;">
                            NIP. {{ $report->employee_nip ?: '-' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- 5. DOKUMENTASI KEGIATAN -->
        @if(count($photosList) > 0)
        <tr>
            <th colspan="3" class="section-header" style="text-align: center;">
                DOKUMENTASI KEGIATAN
            </th>
        </tr>
        <tr>
            <td colspan="3" style="padding: 15px;">
                <table class="photo-grid-table">
                    @for($i = 0; $i < count($photosList); $i += 2)
                        <tr>
                            <td style="width: 50%;">
                                @if(isset($photosList[$i]))
                                    <img src="{{ $photosList[$i]['base64'] }}" class="photo-img">
                                    <div class="photo-caption">{{ $photosList[$i]['caption'] }}</div>
                                @endif
                            </td>
                            <td style="width: 50%;">
                                @if(isset($photosList[$i + 1]))
                                    <img src="{{ $photosList[$i + 1]['base64'] }}" class="photo-img">
                                    <div class="photo-caption">{{ $photosList[$i + 1]['caption'] }}</div>
                                @endif
                            </td>
                        </tr>
                    @endfor
                </table>
            </td>
        </tr>
        @endif
    </table>

</body>
</html>
