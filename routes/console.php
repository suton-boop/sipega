<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
// Otomatisasi Penilaian Seluruh Pegawai saat Jam 24:00 WITA (Membantu Kasubag saat Kesibukan Tinggi)
Schedule::command('sipega:auto-evaluate')
    ->dailyAt('23:59')
    ->timezone('Asia/Makassar');

// Tahap 5: Eksekusi otomatis Perhitungan Performa SIPEGA Jam 00:01 WITA
Schedule::command('sipega:calculate-performance')
    ->dailyAt('00:01')
    ->timezone('Asia/Makassar');
