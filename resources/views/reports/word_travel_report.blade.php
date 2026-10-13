<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset="utf-8">
    <title>Laporan Perjalanan Dinas - {{ $report->employee_name }}</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page Section1 {
            size: 21.0cm 29.7cm;
            margin: 2.0cm 2.0cm 2.0cm 2.5cm;
            mso-header-margin: 35.4pt;
            mso-footer-margin: 35.4pt;
            mso-paper-source: 0;
        }
        div.Section1 {
            page: Section1;
        }
        body {
            font-family: 'Bookman Old Style', 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000000;
        }
        .title-doc {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 20px;
        }
        table.tbl-report {
            width: 100%;
            border-collapse: collapse;
            mso-table-layout-alt: fixed;
            margin-bottom: 20px;
        }
        table.tbl-report td, table.tbl-report th {
            border: 1pt solid #000000;
            padding: 6pt 8pt;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .header-section {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
            font-size: 11pt;
        }
        .col-title {
            width: 25%;
        }
        .col-sep {
            width: 3%;
            text-align: center;
        }
        .col-desc {
            width: 72%;
            text-align: justify;
        }
        .narasi {
            text-align: justify;
            line-height: 1.4;
        }
        .sig-container {
            width: 280px;
            float: right;
            text-align: center;
            margin-top: 15px;
        }
        .caption {
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin-top: 5pt;
        }
    </style>
</head>
<body>
<div class="Section1">

    <div class="title-doc">
        LAPORAN PERJALANAN DINAS
    </div>

    <table class="tbl-report" border="1" cellspacing="0" cellpadding="6">
        <!-- 1. IDENTITAS -->
        <tr>
            <th colspan="3" class="header-section">
                IDENTITAS YANG MELAKUKAN PERJALANAN DINAS
            </th>
        </tr>
        <tr>
            <td class="col-title">Nama</td>
            <td class="col-sep">:</td>
            <td class="col-desc">{{ $report->employee_name }}</td>
        </tr>
        <tr>
            <td class="col-title">NIP</td>
            <td class="col-sep">:</td>
            <td class="col-desc">{{ $report->employee_nip ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-title">Jabatan</td>
            <td class="col-sep">:</td>
            <td class="col-desc">{{ $report->employee_position ?: '-' }}</td>
        </tr>
        <tr>
            <td class="col-title">Nomor Surat Tugas</td>
            <td class="col-sep">:</td>
            <td class="col-desc">
                {{ $report->st_number }}
                @if($report->st_date)
                    Tanggal {{ \Carbon\Carbon::parse($report->st_date)->translatedFormat('d F Y') }}
                @endif
            </td>
        </tr>

        <!-- 2. KEGIATAN -->
        <tr>
            <th colspan="3" class="header-section">
                KEGIATAN
            </th>
        </tr>
        <tr>
            <td class="col-title">Nama Kegiatan</td>
            <td class="col-sep">:</td>
            <td class="col-desc"><strong>{{ $report->activity_name }}</strong></td>
        </tr>
        <tr>
            <td class="col-title">Tanggal</td>
            <td class="col-sep">:</td>
            <td class="col-desc">{{ $report->formatted_date_range }}</td>
        </tr>
        <tr>
            <td class="col-title">Tempat</td>
            <td class="col-sep">:</td>
            <td class="col-desc">{!! nl2br(e($report->location)) !!}</td>
        </tr>
        <tr>
            <td class="col-title">Tujuan</td>
            <td class="col-sep">:</td>
            <td class="col-desc">{!! nl2br(e($report->purpose)) !!}</td>
        </tr>
        <tr>
            <td class="col-title">Sasaran</td>
            <td class="col-sep">:</td>
            <td class="col-desc">{!! nl2br(e($report->target_audience)) !!}</td>
        </tr>
        <tr>
            <td class="col-title">Output</td>
            <td class="col-sep">:</td>
            <td class="col-desc">{!! nl2br(e($report->output)) !!}</td>
        </tr>

        <!-- 3. DESKRIPSI KEGIATAN -->
        <tr>
            <td class="col-title"><strong>Deskripsi Kegiatan</strong></td>
            <td class="col-sep">:</td>
            <td class="col-desc">
                <div class="narasi">
                    {!! nl2br(e($report->description)) !!}
                </div>

                @if(!empty($report->conclusion))
                    <div style="margin-top: 15pt;">
                        <strong>Penutup / Kesimpulan:</strong><br>
                        <div class="narasi" style="margin-top: 4pt;">
                            {!! nl2br(e($report->conclusion)) !!}
                        </div>
                    </div>
                @endif
            </td>
        </tr>

        <!-- 4. TANDA TANGAN (Dalam tabel dokumen) -->
        <tr>
            <td colspan="3" style="padding: 18pt 25pt; border-top: 1pt solid #000;">
                <table style="width: 280px; margin-left: auto; text-align: center; border: none;" border="0">
                    <tr>
                        <td style="border: none; padding: 2pt;">
                            {{ $report->formatted_signed_date }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2pt 2pt 6pt 2pt;">
                            Pelaksana Perjadin,
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; height: 75px; vertical-align: middle;">
                            @if(!empty($signatureBase64))
                                <img src="{{ $signatureBase64 }}" style="max-height: 70px; max-width: 180px; display: inline-block;">
                            @else
                                <div style="height: 60px;"></div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2pt; font-weight: bold; text-decoration: underline;">
                            {{ $report->employee_name }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 2pt; font-size: 10pt;">
                            NIP. {{ $report->employee_nip ?: '-' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- 5. DOKUMENTASI KEGIATAN -->
        @if(!empty($docPhotos) && count($docPhotos) > 0)
        <tr>
            <th colspan="3" class="header-section" style="text-align: center;">
                DOKUMENTASI KEGIATAN
            </th>
        </tr>
        <tr>
            <td colspan="3" style="padding: 15pt;">
                <table style="width: 100%; border: none;" border="0">
                    @for($i = 0; $i < count($docPhotos); $i += 2)
                        <tr>
                            <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 10pt;">
                                @if(isset($docPhotos[$i]) && !empty($docPhotos[$i]['base64']))
                                    <img src="{{ $docPhotos[$i]['base64'] }}" style="max-width: 280px; max-height: 220px; border: 1pt solid #ccc;">
                                    <div class="caption">{{ $docPhotos[$i]['caption'] }}</div>
                                @endif
                            </td>
                            <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 10pt;">
                                @if(isset($docPhotos[$i + 1]) && !empty($docPhotos[$i + 1]['base64']))
                                    <img src="{{ $docPhotos[$i + 1]['base64'] }}" style="max-width: 280px; max-height: 220px; border: 1pt solid #ccc;">
                                    <div class="caption">{{ $docPhotos[$i + 1]['caption'] }}</div>
                                @endif
                            </td>
                        </tr>
                    @endfor
                </table>
            </td>
        </tr>
        @endif
    </table>

</div>
</body>
</html>
