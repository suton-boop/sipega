<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class GugusMutuUserSeeder extends Seeder
{
    public function run(): void
    {
        $gugusList = [
            'GM 1 - PAUD & Kesetaraan',
            'GM 2 - Sekolah Dasar (SD)',
            'GM 3 - SMP',
            'GM 4 - SMA, SMK & SLB',
            'GM 5 - Tata Usaha & Kemitraan'
        ];

        $positions = [
            'Widyaprada Ahli Madya',
            'Widyaprada Ahli Muda',
            'Widyaprada Ahli Pertama',
            'Pengembang Penilaian Pendidikan Ahli Madya',
            'Pengembang Penilaian Pendidikan Ahli Muda',
            'Pranata Komputer Ahli Muda',
            'Analis Kebijakan Ahli Muda',
            'Pengadministrasi Perkantoran',
            'Pengolah Data dan Informasi'
        ];

        $golonganList = ['IV/b', 'IV/a', 'III/d', 'III/c', 'III/b', 'III/a', 'II/c'];

        $users = User::where('role', 'Pegawai')->get();
        foreach ($users as $idx => $user) {
            $user->update([
                'gugus_mutu' => $gugusList[$idx % count($gugusList)],
                'position' => $positions[$idx % count($positions)],
                'golongan' => $golonganList[$idx % count($golonganList)],
                'grade' => (string)(7 + ($idx % 6)),
            ]);
        }
    }
}
