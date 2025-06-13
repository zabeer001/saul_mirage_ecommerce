<?php

namespace Database\Seeders;

use App\Helpers\HelperMethods;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $promoCodes = PromoCode::all();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please seed the products table first.');
            return;
        }

        if ($promoCodes->isEmpty()) {
            $this->command->warn('No promo codes found. Please seed the promocodes table first.');
            return;
        }

        for ($i = 1; $i <= 5; $i++) { // create multiple orders, adjust as needed
            $selectedPromo = $promoCodes->random();

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
                'items'           =>  rand(1, 5),
                'shipping_price'  => 50.00,
                'order_summary'   => 'Subtotal: $100.00 | Tax: $15.00 | Total: $165.00',
                'payment_method'  => 'cash_on_delivery',
                'payment_status'  => 'unpaid',
                'promocode_id'    => $selectedPromo->id,
                'total'     => 165.00,
            ]);

            // Prepare sync data: product_id => ['quantity' => X]
            $randomProducts = $products->random(rand(1, 3));
            $syncData = [];

            foreach ($randomProducts as $product) {
                $syncData[$product->id] = ['quantity' => rand(1, 5)];
            }

            $order->products()->sync($syncData);
        }
    }
}
