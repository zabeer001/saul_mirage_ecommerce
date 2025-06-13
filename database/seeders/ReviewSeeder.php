<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::take(3)->get();
        $users = User::take(2)->get();

        foreach ($products as $product) {
            foreach ($users as $user) {
                Review::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'comment' => fake()->sentence(),
                    'rating' => rand(1, 5),
                ]);
            }
        }
    }
}
