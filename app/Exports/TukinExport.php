<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TukinExport extends DefaultValueBinder implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithColumnFormatting, 
    ShouldAutoSize, 
    WithStyles, 
    WithCustomValueBinder
{
    /**
     * Memaksa kolom NIP (Kolom B) agar selalu terikat secara eksplisit sebagai STRING / TEKS.
     */
    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'B' && $cell->getRow() > 1) {
            $cleanNip = (string)($value ?? '');
            $cell->setValueExplicit($cleanNip, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

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
            (string)($user->nip ?? ''),
            $user->grade,
            number_format($tukin['base_tukin'], 0, ',', '.'),
            number_format($tukin['attendance_penalty_amount'], 0, ',', '.'),
            number_format($tukin['performance_penalty_amount'], 0, ',', '.'),
            number_format($tukin['net_tukin'], 0, ',', '.'),
            $user->performance_color . ' (' . $user->performance_score . ')',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

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
