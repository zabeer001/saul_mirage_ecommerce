<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'type' => 'Products'],
            ['name' => 'Clothing', 'type' => 'Fashion'],
            ['name' => 'Home & Kitchen', 'type' => 'Products'],
            ['name' => 'Beauty & Personal Care', 'type' => 'Fashion'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'name' => $cat['name'],
                'description' => 'Explore our wide selection of ' . Str::lower($cat['name']) . '.',
                'type' => $cat['type'],
            ]);
        }
    }
}
