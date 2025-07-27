<?php

namespace Database\Seeders;

use App\Enums\UserRole;
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
        User::firstOrCreate(
            ['email' => 'admin@kolayhosting.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('124312'), // Güvenli bir şifre kullanın
                'role' => UserRole::ADMIN,
                'email_verified_at' => now(),
                'is_onboarded' => true,
                'is_premium' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@kolayhosting.com'],
            [
                'name' => 'Normal User',
                'password' => Hash::make('124312'), // Güvenli bir şifre kullanın
                'role' => UserRole::USER,
                'email_verified_at' => now(),
                'is_onboarded' => true,
                'is_premium' => false,
            ]
        );

        // 10 adet rastgele kullanıcı oluştur
        User::factory(10)->create();
    }
}
