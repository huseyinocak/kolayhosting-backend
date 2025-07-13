<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryPolicyTest extends TestCase
{
    use RefreshDatabase; // Her test çalıştığında veritabanını yeniler

    protected User $adminUser;
    protected User $regularUser;
    protected Category $category;

    /**
     * Testler başlamadan önce çalışır.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Admin kullanıcı oluşturalım
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        // Normal kullanıcı oluşturalım
        $this->regularUser = User::factory()->create(['role' => 'user']);
        // Bir kategori oluşturalım
        $this->category = Category::factory()->create();
    }

    /**
     * Admin kullanıcının kategori oluşturabildiğini test eder.
     *
     * @return void
     */
    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/v1/categories', [
                'name' => 'New Admin Category',
                'description' => 'Admin tarafından oluşturuldu.'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => ['name' => 'New Admin Category']
            ]);
        $this->assertDatabaseHas('categories', ['name' => 'New Admin Category']);
    }

    /**
     * Normal kullanıcının kategori oluşturamadığını test eder.
     *
     * @return void
     */
    public function test_regular_user_cannot_create_category(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->postJson('/api/v1/categories', [
                'name' => 'New User Category',
                'description' => 'User tarafından oluşturuldu.'
            ]);

        $response->assertStatus(403) // 403 Forbidden döndüğünü kontrol et
            ->assertJson([
                'message' => 'Bu işlemi gerçekleştirmek için yetkiniz yok.',
                'status' => 403
            ]);
        $this->assertDatabaseMissing('categories', ['name' => 'New User Category']);
    }

    /**
     * Admin kullanıcının kategori güncelleyebildiğini test eder.
     *
     * @return void
     */
    public function test_admin_can_update_category(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson('/api/v1/categories/' . $this->category->id, [
                'name' => 'Updated Admin Category',
                'description' => 'Admin tarafından güncellendi.'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['name' => 'Updated Admin Category']
            ]);
        $this->assertDatabaseHas('categories', ['id' => $this->category->id, 'name' => 'Updated Admin Category']);
    }

    /**
     * Normal kullanıcının kategori güncelleyemediğini test eder.
     *
     * @return void
     */
    public function test_regular_user_cannot_update_category(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->putJson('/api/v1/categories/' . $this->category->id, [
                'name' => 'Updated User Category',
                'description' => 'User tarafından güncellendi.'
            ]);

        $response->assertStatus(403) // 403 Forbidden döndüğünü kontrol et
            ->assertJson([
                'message' => 'Bu işlemi gerçekleştirmek için yetkiniz yok.',
                'status' => 403
            ]);
        $this->assertDatabaseMissing('categories', ['name' => 'Updated User Category']);
    }

    /**
     * Admin kullanıcının kategori silebildiğini test eder.
     *
     * @return void
     */
    public function test_admin_can_delete_category(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson('/api/v1/categories/' . $this->category->id);

        $response->assertStatus(204); // 204 No Content döndüğünü kontrol et
        $this->assertDatabaseMissing('categories', ['id' => $this->category->id]);
    }

    /**
     * Normal kullanıcının kategori silemediğini test eder.
     *
     * @return void
     */
    public function test_regular_user_cannot_delete_category(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->deleteJson('/api/v1/categories/' . $this->category->id);

        $response->assertStatus(403) // 403 Forbidden döndüğünü kontrol et
            ->assertJson([
                'message' => 'Bu işlemi gerçekleştirmek için yetkiniz yok.',
                'status' => 403
            ]);
        $this->assertDatabaseHas('categories', ['id' => $this->category->id]); // Silinmediğini kontrol et
    }

    /**
     * Herhangi bir kullanıcının kategorileri listeleyebildiğini test eder.
     *
     * @return void
     */
    public function test_any_authenticated_user_can_view_any_categories(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /**
     * Herhangi bir kullanıcının belirli bir kategoriyi görüntüleyebildiğini test eder.
     *
     * @return void
     */
    public function test_any_authenticated_user_can_view_a_category(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/v1/categories/' . $this->category->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['id' => $this->category->id]
            ]);
    }
}
