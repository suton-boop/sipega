<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SLIP TUKIN - {{ $user->name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 0; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 10px; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        .label { font-weight: bold; width: 140px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th { background-color: #f2f2f2; border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 10px; }
        table.data-table td { border: 1px solid #ddd; padding: 6px 8px; font-size: 10px; }
        
        .summary-box { float: right; width: 300px; border: 1px solid #000; padding: 10px; margin-top: 10px; }
        .summary-row { clear: both; margin-bottom: 5px; }
        .summary-label { float: left; font-weight: bold; }
        .summary-value { float: right; text-align: right; }
        
        .footer { margin-top: 50px; clear: both; }
        .signature { float: right; text-align: center; width: 200px; margin-top: 30px; }
        .signature-name { margin-top: 60px; font-weight: bold; text-decoration: underline; }
        
        .page-break { page-break-after: always; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Kementerian Pendidikan Dasar dan Menengah</h1>
        <p>Balai Penjaminan Mutu Pendidikan (BPMP) Provinsi Kalimantan Timur</p>
        <p>Sistem Informasi Performa dan Penugasan (SIPEGA)</p>
    </div>

    <h2 style="text-align: center; text-transform: uppercase; font-size: 14px;">Slip Rincian Tunjangan Kinerja</h2>
    <p style="text-align: center; font-weight: bold;">Periode: {{ $tukin['month'] }}</p>

    <div class="info-table">
        <table width="100%">
            <tr>
                <td class="label">Nama Pegawai</td>
                <td>: {{ $user->name }}</td>
                <td class="label">NIP</td>
                <td>: {{ $user->nip ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kelas Jabatan</td>
                <td>: {{ $tukin['job_class'] }}</td>
                <td class="label">Predikat Kinerja</td>
                <td style="color: {{ $tukin['performance_penalty_percent'] > 0 ? 'red' : 'green' }}; font-weight: bold;">
                    : {{ $tukin['performance_predicate'] }}
                </td>
            </tr>
        </table>
    </div>

    <p class="text-bold">I. Rincian Pemotongan Kehadiran (Permendikbud 14/2022):</p>
    <table class="data-table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="100">Tanggal</th>
                <th>Keterangan Potongan</th>
                <th width="80" class="text-right">Persentase</th>
                <th width="120" class="text-right">Jumlah Potongan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tukin['details'] as $index => $detail)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($detail['date'])->translatedFormat('d F Y') }}</td>
                <td>
                    @if($detail['type'] == 'TL/PSW')
                        Pelanggaran Jam Kerja (Terlambat/Pulang Cepat)
                    @elseif($detail['type'] == 'ALPA')
                        Alpa (Tanpa Keterangan/ST) Berdasarkan Log Absen
                    @else
                        {{ $detail['type'] }}
                    @endif
                </td>
                <td class="text-right">{{ number_format($detail['penalty_percent'], 2) }}%</td>
                <td class="text-right">{{ number_format($detail['amount'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @if(count($tukin['details']) == 0)
            <tr>
                <td colspan="5" style="text-align: center; color: #888;">Tidak ada potongan kehadiran untuk periode ini.</td>
            </tr>
            @endif
        </tbody>
    </table>

    <p class="text-bold">II. Komputasi Final:</p>
    <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #fafafa;">
        <table width="100%">
            <tr>
                <td width="300">Penghasilan Tunjangan Dasar (100%)</td>
                <td class="text-right">: Rp {{ number_format($tukin['base_tukin'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Potongan Presensi ({{ $tukin['attendance_penalty_percent'] }}%)</td>
                <td class="text-right" style="color: red;">: (Rp {{ number_format($tukin['attendance_penalty_amount'], 0, ',', '.') }})</td>
            </tr>
            <tr>
                <td>Potongan Predikat Kinerja ({{ $tukin['performance_penalty_percent'] }}%)</td>
                <td class="text-right" style="color: red;">: (Rp {{ number_format($tukin['performance_penalty_amount'], 0, ',', '.') }})</td>
            </tr>
            <tr style="font-size: 13px; font-weight: bold; border-top: 2px solid #000;">
                <td style="padding-top: 10px;">JUMLAH TUKIN DIBAYARKAN (NET)</td>
                <td class="text-right" style="padding-top: 10px; color: #003366;">: Rp {{ number_format($tukin['net_tukin'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p style="font-size: 9px; color: #888; font-style: italic;">
            * Dokumen ini dihasilkan secara otomatis oleh sistem SIPEGA BPMP Kaltim berdasarkan Permendikbud No. 14 Tahun 2022.<br>
            * Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WITA
        </p>

        <div class="signature">
            <p>Samarinda, {{ now()->translatedFormat('d F Y') }}</p>
            <p>Bendahara/Pengelola Keuangan,</p>
            <div class="signature-name">
                ( ......................................... )
            </div>
            <p>NIP. ..................................</p>
        </div>
    </div>
</body>
</html>
