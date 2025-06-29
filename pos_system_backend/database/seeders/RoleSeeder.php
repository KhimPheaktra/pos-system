<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'SuperAdmin','created_by' => 0,'updated_by' => null],
            ['name' => 'Admin','created_by' => 1,'updated_by' => null],
            ['name' => 'User','created_by' => 1,'updated_by' => null],
        ]);
    }
}
