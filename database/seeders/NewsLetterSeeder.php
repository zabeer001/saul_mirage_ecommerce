<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewsLetterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $emails = [
            'binzabirtareq@gmail.com',
            'shishir.bdcalling@gmail.com',
        ];

        foreach ($emails as $email) {
            DB::table('news_letters')->insert([
                'email' => $email,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
