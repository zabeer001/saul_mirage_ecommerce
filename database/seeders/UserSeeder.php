<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'binzabirtareq@gmail.com',
            'password' => Hash::make('12345678'), // secure password
            'role' => 'admin',
        ]);
        User::create([
            'name' => 'Admin User',
            'email' => 'shishir@gmail.com',
            'password' => Hash::make('12345678'), // secure password
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
    }
}
