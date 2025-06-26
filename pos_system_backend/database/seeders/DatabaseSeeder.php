<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(ProvinceSeeder::class);
        // \App\Models\User::factory(10)->create();

            DB::table('users')->insert([
        [ 
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('superadmin@123'),
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [ 
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin@123'),
            'role_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [ 
            'name' => 'Pheak tra',
            'email' => 'tra@gmail.com',
            'password' => bcrypt('tra@123'),
            'role_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);

    // For Access Client page 
        DB::table('user_clients')->insert([
        [ 
            'name' => 'Admin Client',
            'email' => 'adminclient@gmail.com',
            'password' => bcrypt('adminclient@123'),
            'role_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [ 
            'name' => 'Tra',
            'email' => 'tra123@gmail.com',
            'password' => bcrypt('tra@123'),
            'role_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

        // Gender
        DB::table('genders')->insert([
            ['name' => 'Male', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Female', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Order Type
        DB::table('order_type')->insert([
        ['order_type' => 'Take Away', 'note' => 'Customer picks up order'],
        ['order_type' => 'Delivery', 'note' => 'Deliver to customer address'],
    ]);
    }
}
