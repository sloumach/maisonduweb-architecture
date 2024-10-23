<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('roles')->delete();

        $roles = [
            ['name' => 'admin', 'description' => 'Administrator'],
            ['name' => 'vendor', 'description' => 'Vendor'],
            ['name' => 'customer', 'description' => 'Customer']
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role['name'],
                'description' => $role['description'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
