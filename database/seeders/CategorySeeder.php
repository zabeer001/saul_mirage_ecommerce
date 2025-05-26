<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categories = [
            'Electronics',
            'Clothing',
            'Home & Kitchen',
            'Beauty & Personal Care',
            'Books',
            'Sports & Outdoors',
            'Toys & Games',
            'Automotive',
        ];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}
