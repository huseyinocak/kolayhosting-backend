<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase; // Her test çalıştığında veritabanını yeniler

    /**
     * Kullanıcı kayıt testini yapar.
     *
     * @return void
     */
    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201) // 201 Created döndüğünü kontrol et
                 ->assertJsonStructure([
                     'message',
                     'user' => ['id', 'name', 'email', 'role'],
                     'access_token',
                     'token_type'
                 ])
                 ->assertJson([
                     'message' => 'Kayıt başarılı.',
                     'user' => ['email' => 'test@example.com', 'role' => 'user']
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'user'
        ]);
    }

    /**
     * Kullanıcı giriş testini yapar.
     *
     * @return void
     */
    public function test_user_can_login(): void
    {
        // Test için bir kullanıcı oluşturalım
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('password'),
            'role' => 'user'
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200) // 200 OK döndüğünü kontrol et
                 ->assertJsonStructure([
                     'message',
                     'user' => ['id', 'name', 'email', 'role'],
                     'access_token',
                     'token_type'
                 ])
                 ->assertJson([
                     'message' => 'Giriş başarılı.',
                     'user' => ['email' => 'login@example.com']
                 ]);
    }

    /**
     * Geçersiz giriş bilgileriyle kullanıcı giriş testini yapar.
     *
     * @return void
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        // Test için bir kullanıcı oluşturalım
        User::factory()->create([
            'email' => 'invalid@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrong-password', // Yanlış şifre
        ]);

        $response->assertStatus(401) // 401 Unauthorized döndüğünü kontrol et
                 ->assertJson([
                     'message' => 'Geçersiz kimlik bilgileri.',
                     'status' => 401
                 ]);
    }

    /**
     * Kullanıcı çıkış testini yapar.
     *
     * @return void
     */
    public function test_user_can_logout(): void
    {
        // Test için bir kullanıcı oluşturalım ve giriş yapalım
        $user = User::factory()->create([
            'email' => 'logout@example.com',
            'password' => Hash::make('password'),
        ]);
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/logout');

        $response->assertStatus(200) // 200 OK döndüğünü kontrol et
                 ->assertJson([
                     'message' => 'Çıkış başarılı.'
                 ]);

        // Token'ın veritabanından silindiğini kontrol et
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test_token'
        ]);
    }

    /**
     * Korumalı bir rotaya yetkisiz erişim testini yapar.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/v1/user'); // Korumalı bir rota

        $response->assertStatus(401) // 401 Unauthorized döndüğünü kontrol et
                 ->assertJson([
                     'message' => 'Kimlik doğrulama başarısız. Geçerli bir token sağlamanız gerekiyor.',
                     'status' => 401
                 ]);
    }
}
