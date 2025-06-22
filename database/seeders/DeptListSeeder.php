<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeptListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('dept_lists')->insert([
            [
                'dept_name' => 'IT',
                'dept_code' => 'ITD',
                'user_hod_id' => 1
            ],
            [
                'dept_name' => 'ENG',
                'dept_code' => 'Engineering',
                'user_hod_id' => 1
            ],
            [
                'dept_name' => 'HR',
                'dept_code' => 'HRD',
                'user_hod_id' => 1
            ],
        ]);
    }
}
