<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Custom Seeder SIPEGA untuk menghasilkan 20 data Pegawai random
        $colors = ['Biru', 'Hijau', 'Kuning', 'Merah'];
        
        for ($i = 1; $i <= 20; $i++) {
            $score = rand(40, 100);
            
            if ($score >= 91) $color = 'Biru';
            elseif ($score >= 76) $color = 'Hijau';
            elseif ($score >= 61) $color = 'Kuning';
            else $color = 'Merah';

            User::create([
                'name' => 'Pegawai Dummy ' . $i,
                'email' => 'pegawai' . $i . '@sipega.com',
                'password' => bcrypt('password'), // password standar "password"
                'role' => 'Pegawai',
                'nip' => '1980' . rand(100000, 999999) . rand(1000, 9999) . '100' . rand(1, 9),
                'performance_score' => $score,
                'performance_color' => $color,
            ]);
        }
    }
}
