<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EmployeeTemplateExport implements FromArray, WithHeadings, WithColumnFormatting, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            [
                'Ahmad Fauzi, S.Pd',
                '198705122011011002',
                'Widyaprada Ahli Muda',
                'III/c',
                '9',
                'Pegawai',
                'ahmad.fauzi@bpmpkaltim.id'
            ],
            [
                'Siti Rahmah, M.Pd',
                '199003152014022003',
                'Pengembang Penilaian Pendidikan',
                'III/b',
                '8',
                'Pegawai',
                'siti.rahmah@bpmpkaltim.id'
            ],
            [
                'Bambang Hidayat, S.Kom',
                '199508202020121004',
                'Pranata Komputer Ahli Pertama',
                'III/a',
                '8',
                'Pegawai',
                'bambang.hidayat@bpmpkaltim.id'
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIP',
            'Jabatan',
            'Golongan',
            'KJ',
            'Role',
            'Email'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT, // NIP format Text agar tidak berubah jadi eksponensial (E+)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF003366']
                ]
            ],
        ];
    }
}
