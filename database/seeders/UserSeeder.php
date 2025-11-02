<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@etowa.com',
                'badge_no' => '10000',
                'role' => 'admin',
                'dept_id' => 1, 
                'email_verified_at' => now(),
                'is_active' => 1,
                'password' => Hash::make('12345'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Andi S',
                'email' => 'andi@etowa.com',
                'badge_no' => '10001',
                'role' => 'hod',
                'dept_id' => 1, 
                'email_verified_at' => now(),
                'is_active' => 1,
                'password' => Hash::make('12345'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Purchase Dept',
                'email' => 'purchasing@etowa.com',
                'badge_no' => '10002',
                'role' => 'purchasing',
                'dept_id' => 2, 
                'email_verified_at' => now(),
                'is_active' => 1,
                'password' => Hash::make('12345'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // [
            //     'deptList_id' => 1,
            //     'email' => 'hod@hod.com',
            //     'name' => 'Andi S',
            //     'badge_no' => 'hod',
            //     'role' => 'hod',
            //     'password' => Hash::make('hod123'),
            // ],
            // [
            //     'deptList_id' => 1,
            //     'email' => 'securityetowa@gmail.com',
            //     'name' => 'security',
            //     'badge_no' => 'security',
            //     'role' => 'security',
            //     'password' => Hash::make('security123'),
            // ],
        ]);
    }
}
