<?php

namespace Database\Seeders;

use App\Helpers\HelperMethods;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all(); // all 3 products

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please seed the products table first.');
            return;
        }

        for ($i = 1; $i <= 5; $i++) {
            $order = Order::create([
                'uniq_id'         => HelperMethods::generateUniqueId(),
                'full_name'       => "Customer {$i}",
                'last_name'       => "Smith",
                'email'           => "customer{$i}@example.com",
                'phone'           => "0170000000{$i}",
                'full_address'    => "House {$i}, Road {$i}, City",
                'city'            => "City {$i}",
                'state'           => "State {$i}",
                'postal_code'     => "120{$i}",
                'country'         => "Bangladesh",
                'type'            => 'online',
                'status'          => 'pending',
                'shipping_method' => 'standard',
                'shipping_price'  => 50.00,
                'order_summary' => 'Subtotal: $100.00 | Tax: $15.00 | Total: $165.00',
                'payment_method'  => 'cash_on_delivery',
                'payment_status'  => 'unpaid',
            ]);

            // Prepare sync data: product_id => ['quantity' => X]
            $randomProducts = $products->random(rand(1, 3));
            $syncData = [];

            foreach ($randomProducts as $product) {
                $syncData[$product->id] = ['quantity' => rand(1, 5)];
            }

            // Sync instead of attach
            $order->products()->sync($syncData);
        }
    }
}
