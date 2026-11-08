<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SLIP TUKIN - {{ $user->name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        
        .kop-container {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }
        .kop-img {
            width: 100%;
            height: auto;
            display: block;
        }

        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 15px; }
        .header h1 { font-size: 15px; margin: 0; text-transform: uppercase; font-weight: bold; }
        .header p { margin: 2px 0; font-size: 10px; }
        
        .doc-title { text-align: center; text-transform: uppercase; font-size: 13px; font-weight: bold; margin-top: 8px; margin-bottom: 3px; }
        .doc-period { text-align: center; font-weight: bold; font-size: 11px; margin-top: 0; margin-bottom: 15px; }

        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .label { font-weight: bold; width: 140px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th { background-color: #f2f2f2; border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 10px; }
        table.data-table td { border: 1px solid #ddd; padding: 5px 8px; font-size: 10px; }
        
        .summary-box { float: right; width: 300px; border: 1px solid #000; padding: 10px; margin-top: 10px; }
        .summary-row { clear: both; margin-bottom: 5px; }
        .summary-label { float: left; font-weight: bold; }
        .summary-value { float: right; text-align: right; }
        
        .footer { margin-top: 35px; clear: both; }
        .signature { float: right; text-align: center; width: 220px; margin-top: 20px; }
        .signature-name { margin-top: 55px; font-weight: bold; text-decoration: underline; }
        
        .page-break { page-break-after: always; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>
    @php
        $periodDate = !empty($period) ? \Carbon\Carbon::parse($period . '-01') : (!empty($month) ? \Carbon\Carbon::parse($month . '-01') : now());
        $periodLabel = $periodDate->translatedFormat('F Y');
        
        // Pengecekan gambar kop surat resmi BPMP Provinsi Kalimantan Timur
        $possibleKopFiles = [
            public_path('images/kop-bpmp-prov-kaltim.png'),
            public_path('images/kop-bpmp-prov-kaltim.jpg'),
            public_path('images/kop-bpmp-prov-kaltim.jpeg'),
            public_path('images/kop-surat-bpmp-kaltim-2026.png'),
            public_path('images/kop-surat-bpmp-kaltim-2026.PNG'),
            public_path('images/kop.png'),
            public_path('images/kop.jpg'),
        ];

        $kopBase64 = null;
        foreach ($possibleKopFiles as $file) {
            if (file_exists($file)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $mime = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/png';
                $kopBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file));
                break;
            }
        }

        $details = $tukin['details'] ?? $tukin['data']['details'] ?? [];
        
        $baseAmount = $calcItem ? $calcItem->base_amount : ($tukin['base_tukin'] ?? 0);
        $grade = $calcItem ? ($calcItem->grade ?: ($tukin['job_class'] ?? '-')) : ($tukin['job_class'] ?? '-');
        $attendancePenaltyPercent = $calcItem ? $calcItem->attendance_penalty_percent : ($tukin['attendance_penalty_percent'] ?? 0);
        $attendanceDeduction = $calcItem ? $calcItem->attendance_deduction : ($tukin['attendance_penalty_amount'] ?? 0);
        $performancePenaltyPercent = $calcItem ? $calcItem->performance_penalty_percent : ($tukin['performance_penalty_percent'] ?? 0);
        $performanceDeduction = $calcItem ? $calcItem->performance_deduction : ($tukin['performance_penalty_amount'] ?? 0);
        $perfPredicate = $calcItem ? ($calcItem->performance_predicate ?: ($user->performance_predicate ?? 'Baik')) : ($tukin['performance_predicate'] ?? ($user->performance_predicate ?? 'Baik'));
        $nettoAmount = $calcItem ? $calcItem->netto_amount : ($tukin['net_tukin'] ?? 0);
        $taxAmount = $calcItem ? ($calcItem->tax_amount ?? 0) : 0;
        $taxPercent = $calcItem ? ($calcItem->tax_percent ?? 0) : 0;
    @endphp

    <!-- KOP SURAT RESMI BPMP PROV KALTIM -->
    @if(!empty($kopBase64))
        <div class="kop-container">
            <img src="{{ $kopBase64 }}" class="kop-img" alt="Kop BPMP Prov Kaltim">
        </div>
    @else
        <div class="header">
            <h1>KEMENTERIAN PENDIDIKAN DASAR DAN MENENGAH</h1>
            <p>Balai Penjaminan Mutu Pendidikan (BPMP) Provinsi Kalimantan Timur</p>
            <p>Sistem Informasi Performa dan Penugasan (SIPEGA)</p>
        </div>
    @endif

    <div class="doc-title">Slip Rincian Tunjangan Kinerja</div>
    <div class="doc-period">Periode: {{ $periodLabel }}</div>

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
                <td>: Grade {{ $grade }}</td>
                <td class="label">Predikat Kinerja</td>
                <td style="color: {{ $performancePenaltyPercent > 0 ? 'red' : 'green' }}; font-weight: bold;">
                    : {{ $perfPredicate }}
                </td>
            </tr>
        </table>
    </div>

    <p class="text-bold">I. Rincian Pemotongan Kehadiran (Permendikdasmen 14/2025):</p>
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
            @forelse($details as $index => $detail)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ !empty($detail['date']) ? \Carbon\Carbon::parse($detail['date'])->translatedFormat('d F Y') : '-' }}</td>
                <td>
                    @if(($detail['type'] ?? '') == 'TL/PSW')
                        Pelanggaran Jam Kerja (Terlambat/Pulang Cepat)
                    @elseif(($detail['type'] ?? '') == 'ALPA')
                        Alpa (Tanpa Keterangan/ST) Berdasarkan Log Absen
                    @else
                        {{ $detail['type'] ?? '-' }}
                    @endif
                </td>
                <td class="text-right">{{ number_format($detail['penalty_percent'] ?? 0, 2) }}%</td>
                <td class="text-right">{{ number_format($detail['amount'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #888;">Tidak ada potongan kehadiran untuk periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p class="text-bold">II. Komputasi Final:</p>
    <div style="border: 1px solid #ddd; padding: 12px; border-radius: 5px; background: #fafafa;">
        <table width="100%">
            <tr>
                <td width="300">Penghasilan Tunjangan Dasar (100%)</td>
                <td class="text-right">: Rp {{ number_format($baseAmount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Total Potongan Presensi ({{ number_format($attendancePenaltyPercent, 2) }}%)</td>
                <td class="text-right" style="color: red;">: (Rp {{ number_format($attendanceDeduction, 0, ',', '.') }})</td>
            </tr>
            <tr>
                <td>Potongan Predikat Kinerja ({{ number_format($performancePenaltyPercent, 2) }}%)</td>
                <td class="text-right" style="color: red;">: (Rp {{ number_format($performanceDeduction, 0, ',', '.') }})</td>
            </tr>
            @if($taxAmount > 0)
            <tr>
                <td>Pajak Penghasilan PPh 21 ({{ number_format($taxPercent, 1) }}%)</td>
                <td class="text-right" style="color: red;">: (Rp {{ number_format($taxAmount, 0, ',', '.') }})</td>
            </tr>
            @endif
            <tr style="font-size: 13px; font-weight: bold; border-top: 2px solid #000;">
                <td style="padding-top: 8px;">JUMLAH TUKIN DIBAYARKAN (NET)</td>
                <td class="text-right" style="padding-top: 8px; color: #003366;">: Rp {{ number_format($nettoAmount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p style="font-size: 9px; color: #666; font-style: italic; line-height: 1.5;">
            * Dokumen resmi ini dihasilkan oleh sistem SIPEGA BPMP Provinsi Kalimantan Timur (Permendikdasmen No. 14 Tahun 2025).<br>
            @if(!empty($isApproved))
            * <strong>STATUS SLIP: RESMI (TELAH DI-APPROVE FINAL OLEH BAGIAN KEPEGAWAIAN / ADMIN).</strong><br>
            @else
            * <strong>STATUS SLIP: DRAFT PRATINJAU INTERNAL (BELUM DI-APPROVE FINAL).</strong><br>
            @endif
            * Waktu cetak: {{ now()->translatedFormat('d F Y H:i') }} WITA
        </p>

        <div class="signature">
            <p>Samarinda, {{ now()->translatedFormat('d F Y') }}</p>
            <p>Bendahara Pengeluaran,</p>
            <div class="signature-name">
                Sulistyo Rahayu, S.Pd.
            </div>
            <p>NIP 19760929 200312 2 001</p>
        </div>
    </div>
</body>
</html>
