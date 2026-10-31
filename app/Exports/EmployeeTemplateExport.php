<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EmployeeTemplateExport extends DefaultValueBinder implements 
    FromArray, 
    WithHeadings, 
    WithColumnFormatting, 
    ShouldAutoSize, 
    WithStyles, 
    WithCustomValueBinder
{
    protected bool $withData;

    public function __construct(bool $withData = false)
    {
        $this->withData = $withData;
    }

    /**
     * Memaksa kolom NIP (Kolom B) agar selalu terikat secara eksplisit sebagai STRING / TEKS.
     * Mencegah Microsoft Excel mengubah NIP 18 digit menjadi notasi ilmiah / eksponensial (E+).
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

    public function array(): array
    {
        if ($this->withData) {
            $users = \App\Models\User::where('is_active', true)->orderBy('name')->get();
            $data = [];
            foreach ($users as $u) {
                $nipStr = !empty($u->nip) ? (string)$u->nip : '';
                $data[] = [
                    $u->name,
                    $nipStr,
                    $u->position ?? '',
                    $u->pangkat_golongan ?? $u->golongan ?? '',
                    $u->grade ?? '',
                    $u->role ?? 'Pegawai',
                    $u->email ?? '',
                ];
            }
            return $data;
        }

        return [
            [
                'Ahmad Fauzi, S.Pd',
                '198705122011011002',
                'Widyaprada Ahli Muda',
                'Penata, III/c',
                '9',
                'Pegawai',
                'ahmad.fauzi@bpmpkaltim.id'
            ],
            [
                'Siti Rahmah, M.Pd',
                '199003152014022003',
                'Pengembang Penilaian Pendidikan',
                'Penata Muda Tingkat I, III/b',
                '8',
                'Pegawai',
                'siti.rahmah@bpmpkaltim.id'
            ],
            [
                'Bambang Hidayat, S.Kom',
                '199508202020121004',
                'Pranata Komputer Ahli Pertama',
                'Penata Muda, III/a',
                '8',
                'Pegawai',
                'bambang.hidayat@bpmpkaltim.id'
            ],
            [
                'Dewi Lestari, S.Pd',
                '199201012022212001',
                'Guru Ahli Pertama',
                'Golongan IX',
                '9',
                'Pegawai',
                'dewi.lestari@bpmpkaltim.id'
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
            'B' => NumberFormat::FORMAT_TEXT, // NIP format Text eksplisit (@)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Paksa seluruh range kolom B agar berformat TEXT murni
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
