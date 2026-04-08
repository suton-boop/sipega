<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new User([
            'name'     => trim($row['nama']),
            'email'    => trim($row['email']),
            'password' => Hash::make($row['password'] ?? 'sipega123'),
            'role'     => $row['role'] ?? 'Pegawai',
            'nip'      => str_replace("'", "", trim($row['nip'])), // Buang tanda kutip pemaksa teks Excel
            'position' => trim($row['jabatan']),
            'golongan' => trim($row['golongan']),
            'grade'    => trim($row['kj']),
            'is_active'=> true,
        ]);
    }
}
