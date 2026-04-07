<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CalendarEvent;
use Carbon\Carbon;

class CalendarEventSeeder extends Seeder
{
    /**
     * SIPEGA: Data Hari Libur Nasional & Cuti Bersama Pemerintah 2026
     */
    public function run(): void
    {
        $holidays = [
            ['2026-01-01', 'Holiday', 'Tahun Baru 2026 Masehi'],
            ['2026-02-17', 'Holiday', 'Isra Mi\'raj Nabi Muhammad SAW'],
            ['2026-02-18', 'Holiday', 'Tahun Baru Imlek 2577 Kongzili'],
            ['2026-03-20', 'Holiday', 'Hari Suci Nyepi (Tahun Baru Saka 1948)'],
            ['2026-03-21', 'Holiday', 'Hari Raya Idul Fitri 1447 Hijriah (Estimasi)'],
            ['2026-03-22', 'Holiday', 'Hari Raya Idul Fitri 1447 Hijriah (Day 2)'],
            ['2026-03-23', 'Shared Leave', 'Cuti Bersama Hari Raya Idul Fitri'],
            ['2026-03-24', 'Shared Leave', 'Cuti Bersama Hari Raya Idul Fitri'],
            ['2026-04-03', 'Holiday', 'Wafat Yesus Kristus (Jumat Agung)'],
            ['2026-04-05', 'Holiday', 'Hari Raya Paskah'],
            ['2026-05-01', 'Holiday', 'Hari Buruh Internasional'],
            ['2026-05-14', 'Holiday', 'Kenaikan Yesus Kristus'],
            ['2026-05-27', 'Holiday', 'Hari Raya Idul Adha 1447 Hijriah'],
            ['2026-05-31', 'Holiday', 'Hari Raya Waisak 2570 BE'],
            ['2026-06-01', 'Holiday', 'Hari Lahir Pancasila'],
            ['2026-06-16', 'Holiday', 'Tahun Baru Islam 1448 Hijriah'],
            ['2026-08-17', 'Holiday', 'Hari Kemerdekaan Republik Indonesia'],
            ['2026-08-25', 'Holiday', 'Maulid Nabi Muhammad SAW'],
            ['2026-12-25', 'Holiday', 'Hari Raya Natal'],
            ['2026-12-26', 'Shared Leave', 'Cuti Bersama Hari Raya Natal'],
        ];

        foreach ($holidays as $h) {
            CalendarEvent::updateOrCreate(
                ['date' => $h[0]],
                ['type' => $h[1], 'description' => $h[2]]
            );
        }
    }
}
