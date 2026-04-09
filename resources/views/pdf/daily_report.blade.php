<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Realisasi Harian - SIPEGA</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11pt; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 16pt; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 10pt; font-style: italic; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; vertical-align: top; }
        .label { font-weight: bold; width: 150px; }
        
        .section-title { background: #f0f0f0; padding: 10px; font-weight: bold; text-transform: uppercase; border: 1px solid #ccc; margin-bottom: 15px; }
        
        table.activity-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.activity-table th, table.activity-table td { border: 1px solid #000; padding: 10px; text-align: left; }
        table.activity-table th { background: #f9f9f9; text-transform: uppercase; font-size: 9pt; }
        
        .proof-img { max-width: 300px; display: block; margin: 10px 0; border: 1px solid #ddd; padding: 5px; }
        
        .footer { margin-top: 50px; width: 100%; }
        .footer-table { width: 100%; text-align: center; }
        .footer-table td { width: 50%; }
        .signature-space { height: 80px; }
        
        .timestamp { font-size: 8pt; color: #999; text-align: right; position: fixed; bottom: 0; right: 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Realisasi Kegiatan Harian</h1>
        <p>Sistem Informasi Performa & Penugasan (SIPEGA) - BPMP Provinsi Kalimantan Timur</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Nama Pegawai</td>
            <td>: {{ $user->name }}</td>
        </tr>
        <tr>
            <td class="label">NIP</td>
            <td>: {{ $user->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td>: {{ $user->position ?? 'Fungsional' }}</td>
        </tr>
        <tr>
            <td class="label">Hari / Tanggal</td>
            <td>: {{ $date }}</td>
        </tr>
    </table>

    <div class="section-title">Realisasi Pekerjaan</div>

    <table class="activity-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="40%">Rencana Kegiatan & Tahapan</th>
                <th width="55%">Capaian / Realisasi & Bukti Fisik</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agenda->items as $item)
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td>
                    <strong>{{ $item->plan_description }}</strong><br>
                    <span style="color: #666; font-size: 9pt;">Tahapan: {{ strtoupper($item->workflow_phase ?? 'Kerja') }}</span>
                </td>
                <td>
                    <strong>Status:</strong> {{ strtoupper($item->status) }}<br>
                    <strong>Narasi:</strong> {{ $item->proof_text ?? $item->realization_notes ?? '-' }}<br>
                    
                    @if($item->evaluation_notes)
                        <div style="margin-top: 5px; color: #d97706; font-size: 9pt;">
                            <strong>Evaluasi:</strong> {{ $item->evaluation_notes }}<br>
                            <strong>Perbaikan:</strong> {{ $item->improvement_plan }}
                        </div>
                    @endif

                    @if($item->proof_file_path)
                        <img src="{{ public_path('storage/' . $item->proof_file_path) }}" class="proof-img">
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    Mengetahui,<br>
                    Atasan Langsung / Pejabat Penilai<br>
                    <div class="signature-space"></div>
                    ( ............................................ )<br>
                    NIP. .........................................
                </td>
                <td>
                    Samarinda, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Pegawai Yang Bersangkutan<br>
                    <div class="signature-space"></div>
                    <strong>{{ $user->name }}</strong><br>
                    NIP. {{ $user->nip ?? '-' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="timestamp">
        Dicetak otomatis melalui SIPEGA-Elite pada {{ $print_time }} WITA
    </div>
</body>
</html>
