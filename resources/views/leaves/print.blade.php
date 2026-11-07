<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Cuti ASN - {{ $leave->employee_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Bookman Old Style', 'Bookman', 'Times New Roman', serif;
            color: #000;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
        }

        .paper-a4 {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm 20mm 20mm 20mm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            box-sizing: border-box;
            position: relative;
        }

        .table-form {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 11pt;
            line-height: 1.35;
        }

        .table-form th, .table-form td {
            border: 1px solid #000;
            padding: 4px 8px;
            vertical-align: top;
        }

        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .check-box {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1px solid #000;
            text-align: center;
            line-height: 14px;
            font-weight: bold;
            font-size: 11pt;
            margin-right: 4px;
        }

        @media print {
            body {
                background: white !important;
            }
            .no-print {
                display: none !important;
            }
            .paper-a4 {
                width: 100% !important;
                margin: 0 !important;
                padding: 10mm 15mm !important;
                box-shadow: none !important;
                page-break-after: always;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Atas (No Print) -->
    <div class="no-print bg-sipega-navy text-white px-6 py-4 shadow-lg sticky top-0 z-50 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('leaves.show', $leave->id) }}" class="text-xs text-slate-300 hover:text-white font-bold flex items-center gap-1">
                <span>← Kembali ke Detail</span>
            </a>
            <div class="h-4 w-px bg-white/20"></div>
            <div class="text-xs font-bold">
                <span class="text-sipega-orange uppercase tracking-wider font-black">Pratinjau Cetak:</span>
                <span class="ml-1">{{ $leave->employee_name }} ({{ $leave->leave_type_label }})</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <!-- Mode Switcher -->
            <div class="bg-white/10 p-1 rounded-xl flex items-center gap-1 text-xs font-bold">
                <a href="{{ route('leaves.print', ['id' => $leave->id, 'view' => 'form']) }}" class="px-3 py-1.5 rounded-lg transition {{ $type !== 'letter' ? 'bg-white text-sipega-navy shadow' : 'text-slate-200 hover:text-white' }}">
                    Formulir BKN (Lampiran)
                </a>
                <a href="{{ route('leaves.print', ['id' => $leave->id, 'view' => 'letter']) }}" class="px-3 py-1.5 rounded-lg transition {{ $type === 'letter' ? 'bg-white text-sipega-navy shadow' : 'text-slate-200 hover:text-white' }}">
                    Surat Pengantar Permohonan
                </a>
            </div>

            <!-- Download PDF -->
            <a href="{{ route('leaves.pdf', ['id' => $leave->id, 'view' => $type]) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Unduh PDF</span>
            </a>

            <!-- Tombol Cetak / Print -->
            <button onclick="window.print()" class="px-5 py-2 bg-sipega-orange hover:bg-amber-600 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-lg transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Dokumen</span>
            </button>
        </div>
    </div>

    @if($type === 'letter')
    <!-- ================= VIEW SURAT PENGANTAR (SESUAI HALAMAN 3 LAMPIRAN) ================= -->
    <div class="paper-a4">
        <!-- KOP SURAT RESMI BPMP KALIMANTAN TIMUR -->
        <div style="width: 100%; margin-bottom: 20px;">
            <img src="{{ asset('images/kop-surat-bpmp-kaltim-2026.png') }}" style="width: 100%; height: auto; display: block;" alt="Kop Surat BPMP Kalimantan Timur">
        </div>

        <div style="font-size: 12pt; line-height: 1.6; padding-top: 10px;">
            <p style="margin-bottom: 25px;">
                Kepada Yth.<br>
                Kepala BPMP Provinsi Kalimantan Timur<br>
                c.q Kepala Subbagian Umum<br>
                di tempat
            </p>

            <p style="margin-bottom: 15px;">Dengan Hormat,</p>
            <p style="margin-bottom: 10px;">Saya yang bertandatangan di bawah ini :</p>

            <table style="width: 100%; border: none; margin-bottom: 20px; font-size: 12pt;">
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

            <div style="float: right; width: 280px; text-align: left; margin-top: 20px;">
                Samarinda, {{ $leave->request_date->translatedFormat('d F Y') }}<br>
                Pemohon,<br><br><br><br>
                <strong style="text-decoration: underline;">{{ $leave->employee_name }}</strong><br>
                NIP. {{ $leave->employee_nip }}
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

    @else
    <!-- ================= VIEW FORMULIR BKN (SESUAI HALAMAN 1 & 2 LAMPIRAN) ================= -->
    
    <!-- HALAMAN 1 -->
    <div class="paper-a4">
        <!-- KOP SURAT RESMI BPMP KALIMANTAN TIMUR -->
        <div style="width: 100%; margin-bottom: 15px;">
            <img src="{{ asset('images/kop-surat-bpmp-kaltim-2026.png') }}" style="width: 100%; height: auto; display: block;" alt="Kop Surat BPMP Kalimantan Timur">
        </div>

        <!-- Tanggal & Tujuan Surat -->
        <div style="float: right; width: 280px; margin-bottom: 10px; font-size: 10pt; line-height: 1.4;">
            Samarinda, {{ $leave->request_date->translatedFormat('d F Y') }}<br><br>
            Kepada<br>
            Yth. Kepala BPMP Kalimantan Timur<br>
            di Samarinda
        </div>
        <div style="clear: both;"></div>

        <!-- Judul Dokumen Resmi -->
        <div class="header-title">
            FORMULIR PERMINTAAN DAN PEMBERIAN CUTI
        </div>

        <!-- I. DATA PEGAWAI -->
        <table class="table-form">
            <thead>
                <tr>
                    <th colspan="4" style="text-align: left; background-color: #f8fafc; font-weight: bold;">I. DATA PEGAWAI</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 15%; font-weight: bold;">Nama</td>
                    <td style="width: 35%;">{{ $leave->employee_name }}</td>
                    <td style="width: 15%; font-weight: bold;">NIP</td>
                    <td style="width: 35%;">{{ $leave->employee_nip }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Jabatan</td>
                    <td>{{ $leave->employee_position }}</td>
                    <td style="font-weight: bold;">Masa Kerja</td>
                    <td>{{ $leave->work_duration }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Unit Kerja</td>
                    <td colspan="3">{{ $leave->work_unit }}</td>
                </tr>
            </tbody>
        </table>

        <!-- II. JENIS CUTI YANG DIAMBIL -->
        <table class="table-form">
            <thead>
                <tr>
                    <th colspan="4" style="text-align: left; background-color: #f8fafc; font-weight: bold;">II. JENIS CUTI YANG DIAMBIL**</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 40%;">1. Cuti Tahunan</td>
                    <td style="width: 10%; text-align: center; font-weight: bold;">
                        {!! $leave->leave_type === 'cuti_tahunan' ? '✓' : '' !!}
                    </td>
                    <td style="width: 40%;">2. Cuti Besar</td>
                    <td style="width: 10%; text-align: center; font-weight: bold;">
                        {!! $leave->leave_type === 'cuti_besar' ? '✓' : '' !!}
                    </td>
                </tr>
                <tr>
                    <td>3. Cuti Sakit</td>
                    <td style="text-align: center; font-weight: bold;">
                        {!! $leave->leave_type === 'cuti_sakit' ? '✓' : '' !!}
                    </td>
                    <td>4. Cuti Melahirkan</td>
                    <td style="text-align: center; font-weight: bold;">
                        {!! $leave->leave_type === 'cuti_melahirkan' ? '✓' : '' !!}
                    </td>
                </tr>
                <tr>
                    <td>5. Cuti Karena Alasan Penting</td>
                    <td style="text-align: center; font-weight: bold;">
                        {!! $leave->leave_type === 'cuti_alasan_penting' ? '✓' : '' !!}
                    </td>
                    <td>6. Cuti di Luar Tanggungan Negara</td>
                    <td style="text-align: center; font-weight: bold;">
                        {!! $leave->leave_type === 'cuti_cltn' ? '✓' : '' !!}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- III. ALASAN CUTI -->
        <table class="table-form">
            <thead>
                <tr>
                    <th style="text-align: left; background-color: #f8fafc; font-weight: bold;">III. ALASAN CUTI</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="min-height: 40px; padding: 8px;">{{ $leave->reason }}</td>
                </tr>
            </tbody>
        </table>

        <!-- IV. LAMANYA CUTI -->
        <table class="table-form">
            <thead>
                <tr>
                    <th colspan="6" style="text-align: left; background-color: #f8fafc; font-weight: bold;">IV. LAMANYA CUTI</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 15%; font-weight: bold;">Selama</td>
                    <td style="width: 25%;">{{ $leave->duration_amount }} ({{ $leave->duration_unit }})*</td>
                    <td style="width: 15%; font-weight: bold;">Mulai Tanggal</td>
                    <td style="width: 20%;">{{ $leave->start_date->translatedFormat('d F Y') }}</td>
                    <td style="width: 5%; text-align: center; font-weight: bold;">s.d</td>
                    <td style="width: 20%;">{{ $leave->end_date->translatedFormat('d F Y') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- V. CATATAN CUTI -->
        <table class="table-form">
            <thead>
                <tr>
                    <th colspan="5" style="text-align: left; background-color: #f8fafc; font-weight: bold;">V. CATATAN CUTI***</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- Kolom 1 Cuti Tahunan -->
                    <td colspan="3" style="width: 50%; padding: 0;">
                        <table style="width: 100%; border-collapse: collapse; border: none; font-size: 10pt;">
                            <tr style="border-bottom: 1px solid #000;">
                                <td colspan="3" style="font-weight: bold; border: none; padding: 3px 6px;">1. CUTI TAHUNAN</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000; text-align: center; font-weight: bold; background-color: #fafafa;">
                                <td style="border-right: 1px solid #000; padding: 2px 4px; width: 25%;">Tahun</td>
                                <td style="border-right: 1px solid #000; padding: 2px 4px; width: 25%;">Sisa</td>
                                <td style="padding: 2px 4px;">Keterangan</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000; text-align: center;">
                                <td style="border-right: 1px solid #000; padding: 2px 4px;">N-2</td>
                                <td style="border-right: 1px solid #000; padding: 2px 4px;">{{ $leave->quota_n2 }}</td>
                                <td style="padding: 2px 4px;">{{ $leave->year_n2 }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #000; text-align: center;">
                                <td style="border-right: 1px solid #000; padding: 2px 4px;">N-1</td>
                                <td style="border-right: 1px solid #000; padding: 2px 4px;">{{ $leave->quota_n1 }}</td>
                                <td style="padding: 2px 4px;">{{ $leave->year_n1 }}</td>
                            </tr>
                            <tr style="text-align: center;">
                                <td style="border-right: 1px solid #000; padding: 2px 4px; font-weight: bold;">N</td>
                                <td style="border-right: 1px solid #000; padding: 2px 4px; font-weight: bold;">{{ $leave->quota_n }}</td>
                                <td style="padding: 2px 4px; font-weight: bold;">{{ $leave->year_n }}</td>
                            </tr>
                        </table>
                    </td>
                    <!-- Kolom 2 Cuti Lainnya -->
                    <td colspan="2" style="width: 50%; padding: 4px 8px; vertical-align: top; font-size: 10pt;">
                        <div style="display: flex; justify-content: space-between; padding: 2px 0;">
                            <span>2. CUTI BESAR</span>
                            <span>{{ $leave->note_cuti_besar ?? '-' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 2px 0;">
                            <span>3. CUTI SAKIT</span>
                            <span>{{ $leave->note_cuti_sakit ?? '-' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 2px 0;">
                            <span>4. CUTI MELAHIRKAN</span>
                            <span>{{ $leave->note_cuti_melahirkan ?? '-' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 2px 0;">
                            <span>5. CUTI KARENA ALASAN PENTING</span>
                            <span>{{ $leave->note_cuti_alasan_penting ?? '-' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 2px 0;">
                            <span>6. CUTI DI LUAR TANGGUNGAN NEGARA</span>
                            <span>{{ $leave->note_cltn ?? '-' }}</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- VI. ALAMAT SELAMA MENJALANKAN CUTI -->
        <table class="table-form">
            <thead>
                <tr>
                    <th colspan="3" style="text-align: left; background-color: #f8fafc; font-weight: bold;">VI. ALAMAT SELAMA MENJALANKAN CUTI</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td rowspan="2" style="width: 55%; padding: 6px;">
                        {{ $leave->leave_address }}
                    </td>
                    <td style="width: 15%; font-weight: bold;">TELP</td>
                    <td style="width: 30%;">{{ $leave->phone_number }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: left; padding: 6px; vertical-align: bottom;">
                        Hormat saya,<br><br><br><br>
                        <strong>{{ $leave->employee_name }}</strong><br>
                        NIP {{ $leave->employee_nip }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- HALAMAN 2 -->
    <div class="paper-a4 page-break">
        <!-- VII. PERTIMBANGAN ATASAN LANGSUNG -->
        <table class="table-form" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th colspan="5" style="text-align: left; background-color: #f8fafc; font-weight: bold;">VII. PERTIMBANGAN ATASAN LANGSUNG**</th>
                </tr>
                <tr style="text-align: center; font-size: 10pt;">
                    <th style="width: 25%;">DISETUJUI</th>
                    <th style="width: 25%;">PERUBAHAN****</th>
                    <th style="width: 25%;">DITANGGUHKAN****</th>
                    <th style="width: 25%;">TIDAK DISETUJUI****</th>
                </tr>
            </thead>
            <tbody>
                <tr style="text-align: center; height: 35px; font-size: 14pt; font-weight: bold;">
                    <td>{!! $leave->supervisor_decision === 'DISETUJUI' ? '✓' : '' !!}</td>
                    <td>{!! $leave->supervisor_decision === 'PERUBAHAN' ? '✓' : '' !!}</td>
                    <td>{!! $leave->supervisor_decision === 'DITANGGUHKAN' ? '✓' : '' !!}</td>
                    <td>{!! $leave->supervisor_decision === 'TIDAK DISETUJUI' ? '✓' : '' !!}</td>
                </tr>
                <tr>
                    <td colspan="2" style="border-right: none; font-size: 9pt; color: #555; vertical-align: bottom;">
                        @if($leave->supervisor_notes)
                            Catatan: {{ $leave->supervisor_notes }}
                        @endif
                    </td>
                    <td colspan="2" style="border-left: none; text-align: left; padding: 10px; font-size: 10pt;">
                        <br><br><br>
                        <strong>{{ $leave->supervisor_name ?? ($leave->supervisor->name ?? 'Soerjo Adi Poernomo,SS.M.Pd') }}</strong><br>
                        NIP {{ $leave->supervisor_nip ?? ($leave->supervisor->nip ?? '197512192003121001') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI -->
        <table class="table-form">
            <thead>
                <tr>
                    <th colspan="5" style="text-align: left; background-color: #f8fafc; font-weight: bold;">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</th>
                </tr>
                <tr style="text-align: center; font-size: 10pt;">
                    <th style="width: 25%;">DISETUJUI</th>
                    <th style="width: 25%;">PERUBAHAN****</th>
                    <th style="width: 25%;">DITANGGUHKAN****</th>
                    <th style="width: 25%;">TIDAK DISETUJUI****</th>
                </tr>
            </thead>
            <tbody>
                <tr style="text-align: center; height: 35px; font-size: 14pt; font-weight: bold;">
                    <td>{!! $leave->approver_decision === 'DISETUJUI' ? '✓' : '' !!}</td>
                    <td>{!! $leave->approver_decision === 'PERUBAHAN' ? '✓' : '' !!}</td>
                    <td>{!! $leave->approver_decision === 'DITANGGUHKAN' ? '✓' : '' !!}</td>
                    <td>{!! $leave->approver_decision === 'TIDAK DISETUJUI' ? '✓' : '' !!}</td>
                </tr>
                <tr>
                    <td colspan="2" style="border-right: none; font-size: 9pt; color: #555; vertical-align: bottom;">
                        @if($leave->approver_notes)
                            Catatan: {{ $leave->approver_notes }}
                        @endif
                    </td>
                    <td colspan="2" style="border-left: none; text-align: left; padding: 10px; font-size: 10pt;">
                        <br><br><br>
                        <strong>{{ $leave->approver_name ?? ($leave->approver->name ?? 'Dr. Jarwoko, M.Pd') }}</strong><br>
                        NIP {{ $leave->approver_nip ?? ($leave->approver->nip ?? '197003191997031001') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- CATATAN KAKI RESMI BKN -->
        <div style="font-size: 9pt; line-height: 1.5; margin-top: 30px; font-family: Arial, sans-serif;">
            <strong>Catatan:</strong><br>
            * Coret yang tidak perlu<br>
            ** Pilih salah satu dengan memberi tanda centang (v)<br>
            *** Diisi oleh pejabat yang menangani bidang kepegawaian sebelum PNS mengajukan cuti<br>
            **** diberi tanda centang dan alasannya<br>
            N = Cuti tahun berjalan<br>
            N-1 = Sisa cuti 1 tahun sebelumnya<br>
            N-2 = Sisa cuti 2 tahun sebelumnya
        </div>
    </div>
    @endif

</body>
</html>
