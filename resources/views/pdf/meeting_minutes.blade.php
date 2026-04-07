<!DOCTYPE html>
<html>
<head>
    <title>Notulensi - {{ $meeting->title }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; }
        .header { text-align: center; border-bottom: 2px solid #FF8C00; padding-bottom: 15px; margin-bottom: 30px; }
        .title { font-size: 20px; font-weight: 200; text-transform: uppercase; color: #003366; }
        .meta { font-size: 11px; color: #555; text-transform: uppercase; letter-spacing: 1px; }
        .agenda { background: #fdfdfd; padding: 15px; border-left: 5px solid #003366; margin-bottom: 20px; font-size: 13px; }
        .minutes { font-size: 14px; color: #333; white-space: pre-wrap; margin-top: 10px; }
        .section-title { font-size: 12px; font-weight: 200; color: #FF8C00; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; }
        .footer { margin-top: 50px; font-size: 10px; text-align: right; color: #aaa; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">NOTULENSI RAPAT SIPEGA</div>
        <div class="meta">{{ $meeting->title }}</div>
        <div class="meta">WITA: {{ $meeting->start_time }} | {{ \Carbon\Carbon::parse($meeting->date)->format('d F Y') }}</div>
    </div>

    <div class="section-title">Agenda Pembahasan</div>
    <div class="agenda">
        {{ $meeting->agenda }}
    </div>

    <div class="section-title">Hasil Keputusan / Notulensi</div>
    <div class="minutes">
        {{ $meeting->minutes_text }}
    </div>

    <div class="footer">
        Dicetak otomatis oleh Sistem SIPEGA pada {{ now('Asia/Makassar')->format('d/m/Y H:i:s') }} WITA
    </div>
</body>
</html>
