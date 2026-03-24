<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Usuario Test', 'email' => 'test@example.com', 'password' => Hash::make('password')],
            ['name' => 'Admin Test', 'email' => 'admin@example.com', 'password' => Hash::make('password')],
            ['name' => 'Operario Test', 'email' => 'operator@example.com', 'password' => Hash::make('password')],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
