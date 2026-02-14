<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@demo.com',
                'password' => '00000000',
                'role' => 'admin',
            ],
            [
                'name' => 'User',
                'email' => 'user@demo.com',
                'password' => '00000000',
                'role' => 'customer',
            ]
        ];

        foreach ($users as $user) {
            \App\Models\User::create($user);
        }
    }
}
