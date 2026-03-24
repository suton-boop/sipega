<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
// Tahap 5: Eksekusi otomatis Perhitungan Performa SIPEGA Jam 00:01 WITA
Schedule::command('sipega:calculate-performance')
    ->dailyAt('00:01')
    ->timezone('Asia/Makassar');
