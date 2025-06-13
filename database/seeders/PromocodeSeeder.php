<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Promocode;

class PromocodeSeeder extends Seeder
{
    public function run(): void
    {
        $promocodes = [
            [
                'name'         => 'WELCOME10',
                'description'  => 'Get 10% off on your first order',
                'type'         => 'percentage', // or 'fixed'
                'status'       => 'active',
                'usage_limit'  => 100,
                'amount'       => 10,
            ],
            [
                'name'         => 'FREESHIP',
                'description'  => 'Free shipping on orders over $50',
                'type'         => 'fixed',
                'status'       => 'active',
                'usage_limit'  => 200,
                'amount'       => 50,
            ],
            [
                'name'         => 'SUMMER25',
                'description'  => '25% off during summer sale',
                'type'         => 'percentage',
                'status'       => 'inactive',
                'usage_limit'  => 50,
                'amount'       => 25,
            ]
        ];

        foreach ($promocodes as $code) {
            Promocode::create($code);
        }
    }
}
