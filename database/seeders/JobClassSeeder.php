<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Official Tukin Base Amounts - Permendikbudristek 14/2022
     */
    public function run(): void
    {
        $classes = [
            ['class_name' => '17', 'base_amount' => 33240000],
            ['class_name' => '16', 'base_amount' => 25187000],
            ['class_name' => '15', 'base_amount' => 19280000],
            ['class_name' => '14', 'ins_name' => '17064000', 'base_amount' => 17064000],
            ['class_name' => '13', 'base_amount' => 10936000],
            ['class_name' => '12', 'base_amount' => 9896000],
            ['class_name' => '11', 'base_amount' => 8757000],
            ['class_name' => '10', 'base_amount' => 5979000],
            ['class_name' => '9',  'base_amount' => 5079000],
            ['class_name' => '8',  'base_amount' => 4595000],
            ['class_name' => '7',  'base_amount' => 3915000],
            ['class_name' => '6',  'base_amount' => 3510000],
            ['class_name' => '5',  'base_amount' => 3135000],
            ['class_name' => '4',  'base_amount' => 2812000],
            ['class_name' => '3',  'base_amount' => 2498000],
            ['class_name' => '2',  'base_amount' => 2331000],
            ['class_name' => '1',  'base_amount' => 2112000],
        ];

        foreach ($classes as $class) {
            DB::table('job_classes')->updateOrInsert(
                ['class_name' => $class['class_name']],
                ['base_amount' => $class['base_amount'], 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
