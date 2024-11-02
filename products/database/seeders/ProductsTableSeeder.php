<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Faker\Factory as Faker;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 500) as $index) {
            Product::create([
                'name' => $faker->words(3, true),  // Génère une chaîne de 3 mots
                'description' => $faker->sentence(),  // Génère une phrase
                'price' => $faker->randomFloat(2, 5, 100),  // Génère un flottant entre 5 et 100
                'quantity' => 100,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
