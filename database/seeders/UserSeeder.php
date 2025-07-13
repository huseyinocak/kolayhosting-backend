<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin kullanıcı
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('124312'),
            'role' => 'admin',
        ]);

        // Normal kullanıcı
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('124312'),
            'role' => 'user',
        ]);

        // Başka bir normal kullanıcı (Review testleri için)
        User::create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => Hash::make('124312'),
            'role' => 'user',
        ]);
    }
}
