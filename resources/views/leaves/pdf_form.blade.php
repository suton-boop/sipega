<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Cuti - {{ $leave->employee_name }}</title>
    @php
        $kopPath = public_path('images/kop-surat-bpmp-kaltim-2026.png');
        $kopBase64 = file_exists($kopPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($kopPath)) : '';
        \Carbon\Carbon::setLocale('id');
    @endphp
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Bookman Old Style', 'Bookman', 'Times New Roman', serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.3;
        }
        .table-form {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9.5pt;
        }
        .table-form th, .table-form td {
            border: 1px solid #000;
            padding: 3.5px 6px;
            vertical-align: top;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 10px 0 8px 0;
            text-transform: uppercase;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- HALAMAN 1 -->
    <!-- KOP SURAT RESMI -->
    <div style="width: 100%; margin-bottom: 12px; text-align: center;">
        @if($kopBase64)
            <img src="{{ $kopBase64 }}" style="width: 100%; display: block;">
        @else
            <img src="{{ public_path('images/kop-surat-bpmp-kaltim-2026.png') }}" style="width: 100%; display: block;">
        @endif
    </div>

    <table style="width: 100%; border: none; margin-bottom: 5px; font-size: 9.5pt;">
        <tr>
            <td style="border: none; width: 55%;"></td>
            <td style="border: none; width: 45%; text-align: left;">
                Samarinda, {{ $leave->request_date->translatedFormat('d F Y') }}<br><br>
                Kepada<br>
                Yth. Kepala BPMP Kalimantan Timur<br>
                di Samarinda
            </td>
        </tr>
    </table>

    <div class="header-title">
        FORMULIR PERMINTAAN DAN PEMBERIAN CUTI
    </div>

    <!-- I. DATA PEGAWAI -->
    <table class="table-form">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th colspan="4" style="text-align: left;">I. DATA PEGAWAI</th>
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
            <tr style="background-color: #f1f5f9;">
                <th colspan="4" style="text-align: left;">II. JENIS CUTI YANG DIAMBIL**</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="width: 40%;">1. Cuti Tahunan</td>
                <td style="width: 10%; text-align: center; font-weight: bold;">{!! $leave->leave_type === 'cuti_tahunan' ? '✓' : '' !!}</td>
                <td style="width: 40%;">2. Cuti Besar</td>
                <td style="width: 10%; text-align: center; font-weight: bold;">{!! $leave->leave_type === 'cuti_besar' ? '✓' : '' !!}</td>
            </tr>
            <tr>
                <td>3. Cuti Sakit</td>
                <td style="text-align: center; font-weight: bold;">{!! $leave->leave_type === 'cuti_sakit' ? '✓' : '' !!}</td>
                <td>4. Cuti Melahirkan</td>
                <td style="text-align: center; font-weight: bold;">{!! $leave->leave_type === 'cuti_melahirkan' ? '✓' : '' !!}</td>
            </tr>
            <tr>
                <td>5. Cuti Karena Alasan Penting</td>
                <td style="text-align: center; font-weight: bold;">{!! $leave->leave_type === 'cuti_alasan_penting' ? '✓' : '' !!}</td>
                <td>6. Cuti di Luar Tanggungan Negara</td>
                <td style="text-align: center; font-weight: bold;">{!! $leave->leave_type === 'cuti_cltn' ? '✓' : '' !!}</td>
            </tr>
        </tbody>
    </table>

    <!-- III. ALASAN CUTI -->
    <table class="table-form">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th style="text-align: left;">III. ALASAN CUTI</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="min-height: 35px; padding: 6px;">{{ $leave->reason }}</td>
            </tr>
        </tbody>
    </table>

    <!-- IV. LAMANYA CUTI -->
    <table class="table-form">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th colspan="6" style="text-align: left;">IV. LAMANYA CUTI</th>
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
            <tr style="background-color: #f1f5f9;">
                <th colspan="5" style="text-align: left;">V. CATATAN CUTI***</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" style="width: 50%; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 9pt;">
                        <tr style="border-bottom: 1px solid #000;">
                            <td colspan="3" style="font-weight: bold; border: none; padding: 2px 5px;">1. CUTI TAHUNAN</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #000; text-align: center; font-weight: bold;">
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
                <td colspan="2" style="width: 50%; padding: 4px 6px; font-size: 8.5pt;">
                    <div>2. CUTI BESAR: {{ $leave->note_cuti_besar ?? '-' }}</div>
                    <div>3. CUTI SAKIT: {{ $leave->note_cuti_sakit ?? '-' }}</div>
                    <div>4. CUTI MELAHIRKAN: {{ $leave->note_cuti_melahirkan ?? '-' }}</div>
                    <div>5. CUTI KARENA ALASAN PENTING: {{ $leave->note_cuti_alasan_penting ?? '-' }}</div>
                    <div>6. CUTI DI LUAR TANGGUNGAN NEGARA: {{ $leave->note_cltn ?? '-' }}</div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- VI. ALAMAT SELAMA MENJALANKAN CUTI -->
    <table class="table-form">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th colspan="3" style="text-align: left;">VI. ALAMAT SELAMA MENJALANKAN CUTI</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td rowspan="2" style="width: 55%; padding: 5px;">
                    {{ $leave->leave_address }}
                </td>
                <td style="width: 15%; font-weight: bold;">TELP</td>
                <td style="width: 30%;">{{ $leave->phone_number }}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: left; padding: 6px; vertical-align: bottom;">
                    Hormat saya,<br><br><br>
                    <strong>{{ $leave->employee_name }}</strong><br>
                    NIP {{ $leave->employee_nip }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- HALAMAN 2 -->
    <!-- VII. PERTIMBANGAN ATASAN LANGSUNG -->
    <table class="table-form" style="margin-top: 15px;">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th colspan="5" style="text-align: left;">VII. PERTIMBANGAN ATASAN LANGSUNG**</th>
            </tr>
            <tr style="text-align: center; font-size: 9pt;">
                <th style="width: 25%;">DISETUJUI</th>
                <th style="width: 25%;">PERUBAHAN****</th>
                <th style="width: 25%;">DITANGGUHKAN****</th>
                <th style="width: 25%;">TIDAK DISETUJUI****</th>
            </tr>
        </thead>
        <tbody>
            <tr style="text-align: center; height: 30px; font-size: 13pt; font-weight: bold;">
                <td>{!! $leave->supervisor_decision === 'DISETUJUI' ? '✓' : '' !!}</td>
                <td>{!! $leave->supervisor_decision === 'PERUBAHAN' ? '✓' : '' !!}</td>
                <td>{!! $leave->supervisor_decision === 'DITANGGUHKAN' ? '✓' : '' !!}</td>
                <td>{!! $leave->supervisor_decision === 'TIDAK DISETUJUI' ? '✓' : '' !!}</td>
            </tr>
            <tr>
                <td colspan="2" style="border-right: none; font-size: 8pt; color: #555; vertical-align: bottom;">
                    @if($leave->supervisor_notes)
                        Catatan: {{ $leave->supervisor_notes }}
                    @endif
                </td>
                <td colspan="2" style="border-left: none; text-align: left; padding: 8px; font-size: 9.5pt;">
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
            <tr style="background-color: #f1f5f9;">
                <th colspan="5" style="text-align: left;">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</th>
            </tr>
            <tr style="text-align: center; font-size: 9pt;">
                <th style="width: 25%;">DISETUJUI</th>
                <th style="width: 25%;">PERUBAHAN****</th>
                <th style="width: 25%;">DITANGGUHKAN****</th>
                <th style="width: 25%;">TIDAK DISETUJUI****</th>
            </tr>
        </thead>
        <tbody>
            <tr style="text-align: center; height: 30px; font-size: 13pt; font-weight: bold;">
                <td>{!! $leave->approver_decision === 'DISETUJUI' ? '✓' : '' !!}</td>
                <td>{!! $leave->approver_decision === 'PERUBAHAN' ? '✓' : '' !!}</td>
                <td>{!! $leave->approver_decision === 'DITANGGUHKAN' ? '✓' : '' !!}</td>
                <td>{!! $leave->approver_decision === 'TIDAK DISETUJUI' ? '✓' : '' !!}</td>
            </tr>
            <tr>
                <td colspan="2" style="border-right: none; font-size: 8pt; color: #555; vertical-align: bottom;">
                    @if($leave->approver_notes)
                        Catatan: {{ $leave->approver_notes }}
                    @endif
                </td>
                <td colspan="2" style="border-left: none; text-align: left; padding: 8px; font-size: 9.5pt;">
                    <br><br><br>
                    <strong>{{ $leave->approver_name ?? ($leave->approver->name ?? 'Dr. Jarwoko, M.Pd') }}</strong><br>
                    NIP {{ $leave->approver_nip ?? ($leave->approver->nip ?? '197003191997031001') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- CATATAN KAKI -->
    <div style="font-size: 8pt; line-height: 1.4; margin-top: 20px; font-family: Arial, sans-serif;">
        <strong>Catatan:</strong><br>
        * Coret yang tidak perlu<br>
        ** Pilih salah satu dengan memberi tanda centang (v)<br>
        *** Diisi oleh pejabat yang menangani bidang kepegawaian sebelum PNS mengajukan cuti<br>
        **** diberi tanda centang dan alasannya<br>
        N = Cuti tahun berjalan<br>
        N-1 = Sisa cuti 1 tahun sebelumnya<br>
        N-2 = Sisa cuti 2 tahun sebelumnya
    </div>

</body>
</html>
