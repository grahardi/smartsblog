<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@smarts.id'],
            [
                'name' => 'Admin Smarts',
                'password' => Hash::make('password-ganti-ini'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
