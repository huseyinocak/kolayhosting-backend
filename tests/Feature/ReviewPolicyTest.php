<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Provider;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReviewPolicyTest extends TestCase
{
    use RefreshDatabase; // Her test çalıştığında veritabanını yeniler

    protected User $adminUser;
    protected User $regularUser;
    protected User $anotherUser;
    protected Review $userReview;
    protected Review $anotherUserReview;
    protected Provider $provider;
    protected Plan $plan;

    /**
     * Testler başlamadan önce çalışır.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Test kullanıcılarını oluşturalım
        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
        $this->anotherUser = User::factory()->create(['role' => 'user']);

        // Test için sağlayıcı ve plan oluşturalım
        $this->provider = Provider::factory()->create();
        $this->plan = Plan::factory()->create([
            'provider_id' => $this->provider->id,
            'category_id' => \App\Models\Category::factory()->create()->id // Geçici bir kategori oluştur
        ]);

        // Regular user tarafından oluşturulan inceleme
        $this->userReview = Review::factory()->create([
            'user_id' => $this->regularUser->id,
            'provider_id' => $this->provider->id,
            'plan_id' => null,
            'is_approved' => true,
            'content' => 'Regular user tarafından yazılan inceleme.'
        ]);

        // Başka bir kullanıcı tarafından oluşturulan inceleme
        $this->anotherUserReview = Review::factory()->create([
            'user_id' => $this->anotherUser->id,
            'provider_id' => $this->provider->id,
            'plan_id' => null,
            'is_approved' => false, // Onaylanmamış bir inceleme
            'content' => 'Başka bir kullanıcı tarafından yazılan inceleme.'
        ]);
    }

    /**
     * Herhangi bir kimliği doğrulanmış kullanıcının inceleme oluşturabildiğini test eder.
     *
     * @return void
     */
    public function test_any_authenticated_user_can_create_review(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->postJson('/api/v1/reviews', [
                'provider_id' => $this->provider->id,
                'rating' => 5,
                'title' => 'Yeni İnceleme',
                'content' => 'Bu yeni bir inceleme içeriği.',
                'user_name' => 'Test Yazar',
                'is_approved' => false // Otomatik olarak false olabilir
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'Yeni İnceleme',
                    'user_name' => 'Test Yazar',
                    'is_approved' => false // Varsayılan olarak false döndüğünü varsayıyoruz
                ]
            ]);
        $this->assertDatabaseHas('reviews', ['title' => 'Yeni İnceleme', 'user_id' => $this->regularUser->id]);
    }

    /**
     * Admin kullanıcının herhangi bir incelemeyi güncelleyebildiğini test eder.
     *
     * @return void
     */
    public function test_admin_can_update_any_review(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson('/api/v1/reviews/' . $this->anotherUserReview->id, [
                'rating' => 3,
                'is_approved' => true,
                'content' => 'Admin tarafından güncellenmiş içerik.'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $this->anotherUserReview->id,
                    'rating' => 3,
                    'is_approved' => true,
                    'content' => 'Admin tarafından güncellenmiş içerik.'
                ]
            ]);
        $this->assertDatabaseHas('reviews', [
            'id' => $this->anotherUserReview->id,
            'rating' => 3,
            'is_approved' => true
        ]);
    }

    /**
     * Kullanıcının kendi incelemesini güncelleyebildiğini test eder.
     *
     * @return void
     */
    public function test_user_can_update_own_review(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->putJson('/api/v1/reviews/' . $this->userReview->id, [
                'rating' => 4,
                'title' => 'Kendi incelemem güncellendi.'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $this->userReview->id,
                    'rating' => 4,
                    'title' => 'Kendi incelemem güncellendi.'
                ]
            ]);
        $this->assertDatabaseHas('reviews', [
            'id' => $this->userReview->id,
            'rating' => 4,
            'title' => 'Kendi incelemem güncellendi.'
        ]);
    }

    /**
     * Kullanıcının başka bir kullanıcının incelemesini güncelleyemediğini test eder.
     *
     * @return void
     */
    public function test_user_cannot_update_another_users_review(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->putJson('/api/v1/reviews/' . $this->anotherUserReview->id, [
                'rating' => 1,
                'title' => 'Başkasının incelemesini güncelleyemem.'
            ]);

        $response->assertStatus(403) // 403 Forbidden döndüğünü kontrol et
            ->assertJson([
                'message' => 'Bu işlemi gerçekleştirmek için yetkiniz yok.',
                'status' => 403
            ]);
        $this->assertDatabaseHas('reviews', [ // İncelemenin değişmediğini kontrol et
            'id' => $this->anotherUserReview->id,
            'rating' => $this->anotherUserReview->rating,
            'title' => $this->anotherUserReview->title
        ]);
    }

    /**
     * Admin kullanıcının herhangi bir incelemeyi silebildiğini test eder.
     *
     * @return void
     */
    public function test_admin_can_delete_any_review(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson('/api/v1/reviews/' . $this->userReview->id);

        $response->assertStatus(204); // 204 No Content döndüğünü kontrol et
        $this->assertDatabaseMissing('reviews', ['id' => $this->userReview->id]);
    }

    /**
     * Kullanıcının kendi incelemesini silebildiğini test eder.
     *
     * @return void
     */
    public function test_user_can_delete_own_review(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->deleteJson('/api/v1/reviews/' . $this->userReview->id);

        $response->assertStatus(204); // 204 No Content döndüğünü kontrol et
        $this->assertDatabaseMissing('reviews', ['id' => $this->userReview->id]);
    }

    /**
     * Kullanıcının başka bir kullanıcının incelemesini silemediğini test eder.
     *
     * @return void
     */
    public function test_user_cannot_delete_another_users_review(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->deleteJson('/api/v1/reviews/' . $this->anotherUserReview->id);

        $response->assertStatus(403) // 403 Forbidden döndüğünü kontrol et
            ->assertJson([
                'message' => 'Bu işlemi gerçekleştirmek için yetkiniz yok.',
                'status' => 403
            ]);
        $this->assertDatabaseHas('reviews', ['id' => $this->anotherUserReview->id]); // Silinmediğini kontrol et
    }

    /**
     * Herhangi bir kimliği doğrulanmış kullanıcının incelemeleri listeleyebildiğini test eder.
     *
     * @return void
     */
    public function test_any_authenticated_user_can_view_any_reviews(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/v1/reviews');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /**
     * Herhangi bir kimliği doğrulanmış kullanıcının belirli bir incelemeyi görüntüleyebildiğini test eder.
     *
     * @return void
     */
    public function test_any_authenticated_user_can_view_a_review(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/v1/reviews/' . $this->userReview->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['id' => $this->userReview->id]
            ]);
    }

    /**
     * Normal kullanıcının onaylanmamış bir incelemenin içeriğini görmediğini test eder.
     * (Kendi incelemesi değilse)
     *
     * @return void
     */
    public function test_regular_user_does_not_see_unapproved_review_content_of_others(): void
    {
        // Başka bir kullanıcı tarafından oluşturulan ve onaylanmamış inceleme
        $unapprovedReview = Review::factory()->create([
            'user_id' => $this->anotherUser->id,
            'provider_id' => $this->provider->id,
            'is_approved' => false,
            'content' => 'Gizli içerik.'
        ]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/v1/reviews/' . $unapprovedReview->id);

        $response->assertStatus(200)
            ->assertJsonMissing(['content' => 'Gizli içerik.']); // İçeriğin gizlendiğini kontrol et
    }

    /**
     * Admin kullanıcının onaylanmamış bir incelemenin içeriğini görebildiğini test eder.
     *
     * @return void
     */
    public function test_admin_sees_unapproved_review_content(): void
    {
        // Başka bir kullanıcı tarafından oluşturulan ve onaylanmamış inceleme
        $unapprovedReview = Review::factory()->create([
            'user_id' => $this->anotherUser->id,
            'provider_id' => $this->provider->id,
            'is_approved' => false,
            'content' => 'Gizli içerik.'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/reviews/' . $unapprovedReview->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['content' => 'Gizli içerik.'] // Adminin içeriği gördüğünü kontrol et
            ]);
    }

    /**
     * Normal kullanıcının kendi onaylanmamış incelemesinin içeriğini görebildiğini test eder.
     *
     * @return void
     */
    public function test_user_sees_own_unapproved_review_content(): void
    {
        // Regular user tarafından oluşturulan ve onaylanmamış inceleme
        $ownUnapprovedReview = Review::factory()->create([
            'user_id' => $this->regularUser->id,
            'provider_id' => $this->provider->id,
            'is_approved' => false,
            'content' => 'Kendi gizli içeriğim.'
        ]);

        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/v1/reviews/' . $ownUnapprovedReview->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['content' => 'Kendi gizli içeriğim.'] // Kullanıcının kendi içeriğini gördüğünü kontrol et
            ]);
    }
}
