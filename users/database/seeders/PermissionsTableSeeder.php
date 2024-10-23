<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('permissions')->delete();

        $permissions = [
            ['name' => 'create_product', 'description' => 'Create a product'],
            ['name' => 'edit_product', 'description' => 'Edit a product'],
            ['name' => 'delete_product', 'description' => 'Delete a product'],
            ['name' => 'view_orders', 'description' => 'View all orders'],
            ['name' => 'manage_users', 'description' => 'Manage users']
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'description' => $permission['description'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
