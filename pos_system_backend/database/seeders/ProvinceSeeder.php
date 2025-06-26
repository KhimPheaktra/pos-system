<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
            DB::table('provinces')->insert([
                [
                    'id' => 1,
                    'name' => 'Phnom Penh',
                    'name_in_khmer' => 'ភ្នំពេញ',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(), 
                ],
                [
                    'id' => 2,
                    'name' => 'Banteay Meanchey',
                    'name_in_khmer' => 'បន្ទាយមានជ័យ',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 3,
                    'name' => 'Battambang',
                    'name_in_khmer' => 'បាត់ដំបង',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 4,
                    'name' => 'Kampong Cham',
                    'name_in_khmer' => 'កំពង់ចាម',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 5,
                    'name' => 'Kampong Chhnang',
                    'name_in_khmer' => 'កំពង់ឆ្នាំង',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 6,
                    'name' => 'Kampong Speu',
                    'name_in_khmer' => 'កំពង់ស្ពឺ',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 7,
                    'name' => 'Kampong Thom',
                    'name_in_khmer' => 'កំពង់ធំ',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 8,
                    'name' => 'Kampot',
                    'name_in_khmer' => 'កំពត',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 9,
                    'name' => 'Kandal',
                    'name_in_khmer' => 'កណ្ដាល',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 10,
                    'name' => 'Kep',
                    'name_in_khmer' => 'កែប',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 11,
                    'name' => 'Koh Kong',
                    'name_in_khmer' => 'កោះកុង',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 12,
                    'name' => 'Kratié',
                    'name_in_khmer' => 'ក្រចេះ',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 13,
                    'name' => 'Mondulkiri',
                    'name_in_khmer' => 'មណ្ឌលគិរី',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 14,
                    'name' => 'Oddar Meanchey',
                    'name_in_khmer' => 'ឧត្ដរមានជ័យ',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 15,
                    'name' => 'Pailin',
                    'name_in_khmer' => 'ប៉ៃលិន',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 16,
                    'name' => 'Preah Sihanouk',
                    'name_in_khmer' => 'ព្រះសីហនុ',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 17,
                    'name' => 'Preah Vihear',
                    'name_in_khmer' => 'ព្រះវិហារ',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 18,
                    'name' => 'Prey Veng',
                    'name_in_khmer' => 'ព្រៃវែង',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 19,
                    'name' => 'Pursat',
                    'name_in_khmer' => 'ពោធិ៍សាត់',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 20,
                    'name' => 'Ratanakiri',
                    'name_in_khmer' => 'រតនគិរី',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 21,
                    'name' => 'Siem Reap',
                    'name_in_khmer' => 'សៀមរាប',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 22,
                    'name' => 'Stung Treng',
                    'name_in_khmer' => 'ស្ទឹងត្រែង',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 23,
                    'name' => 'Svay Rieng',
                    'name_in_khmer' => 'ស្វាយរៀង',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 24,
                    'name' => 'Takéo',
                    'name_in_khmer' => 'តាកែវ',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 25,
                    'name' => 'Tboung Khmum',
                    'name_in_khmer' => 'ត្បូងឃ្មុំ',
                    'status' => 'ACT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

