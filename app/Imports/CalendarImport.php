<?php

namespace App\Imports;

use App\Models\CalendarEvent;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class CalendarImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Headers Excel: date, type, description
        if (!isset($row['date'])) return null;

        return new CalendarEvent([
            'date'        => Carbon::parse($row['date'])->format('Y-m-d'),
            'type'        => $row['type'] ?? 'Holiday',
            'description' => $row['description'] ?? 'SIPEGA Import'
        ]);
    }
}
