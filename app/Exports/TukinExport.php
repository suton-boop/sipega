<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TukinExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return User::all();
    }

    public function headings(): array
    {
        return [
            'Nama Pegawai',
            'NIP',
            'Grade',
            'Tukin Base (Rp)',
            'Potongan Absen (Rp)',
            'Potongan Kinerja (Rp)',
            'Tukin Diterima (Rp)',
            'Status Performa',
        ];
    }

    public function map($user): array
    {
        $tukin = $user->calculateMonthlyTukin();

        return [
            $user->name,
            $user->nip,
            $user->grade,
            number_format($tukin['base_tukin'], 0, ',', '.'),
            number_format($tukin['attendance_penalty_amount'], 0, ',', '.'),
            number_format($tukin['performance_penalty_amount'], 0, ',', '.'),
            number_format($tukin['net_tukin'], 0, ',', '.'),
            $user->performance_color . ' (' . $user->performance_score . ')',
        ];
    }
}
