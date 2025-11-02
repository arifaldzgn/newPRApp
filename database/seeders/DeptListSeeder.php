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
                'dept_code' => 'ITD',
                'dept_name' => 'Information Technology',
                'user_hod_id' => 2
            ],
            [
                'dept_code' => 'PRD',
                'dept_name' => 'Purchasing Dept',
                'user_hod_id' => 3
            ],
            // [
            //     'dept_code' => 'HRD',
            //     'dept_name' => 'Human Resources',
            //     'user_hod_id' => 1
            // ],
            // [
            //     'dept_code' => 'PRD',
            //     'dept_name' => 'Purchasing',
            //     'user_hod_id' => 1
            // ],
            // [
            //     'dept_code' => 'PRC',
            //     'dept_name' => 'Procurement',
            //     'user_hod_id' => 1
            // ],
            // [
            //     'dept_code' => 'FIN',
            //     'dept_name' => 'Finance',
            //     'user_hod_id' => 1
            // ],
            // [
            //     'dept_code' => 'QAD',
            //     'dept_name' => 'Quality Assurance',
            //     'user_hod_id' => 1
            // ],
            // [
            //     'dept_code' => 'MKD',
            //     'dept_name' => 'Marketing',
            //     'user_hod_id' => 1
            // ],
            // [
            //     'dept_code' => 'DSGN',
            //     'dept_name' => 'Design R&D',
            //     'user_hod_id' => 1
            // ],
            // [
            //     'dept_code' => 'VF',
            //     'dept_name' => 'Vacuum Forming',
            //     'user_hod_id' => 1
            // ],
        ]);
    }
}
