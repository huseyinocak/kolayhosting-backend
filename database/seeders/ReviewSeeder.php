<?php

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $providers = Provider::all();
        $plans = Plan::all();

        if ($users->isEmpty() || $providers->isEmpty()) {
            $this->command->info('User veya Provider bulunamadı. Lütfen önce UserSeeder ve ProviderSeeder\'ı çalıştırın.');
            return;
        }

        for ($i = 0; $i < 50; $i++) { // 50 adet rastgele inceleme oluştur
            $user = $users->random();
            $provider = $providers->random();
            $plan = $plans->where('provider_id', $provider->id)->random(); // Sağlayıcının bir planını seç

            $status = fake()->randomElement(ReviewStatus::values());
            $publishedAt = ($status === ReviewStatus::APPROVED) ? fake()->dateTimeBetween('-1 year', 'now') : null;

            Review::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'provider_id' => $provider->id,
                    'plan_id' => $plan->id,
                    'title' => fake()->sentence(3),
                ],
                [
                    'user_name' => $user->name,
                    'rating' => fake()->numberBetween(1, 5),
                    'content' => fake()->paragraph(3),
                    'published_at' => $publishedAt,
                    'status' => $status,
                ]
            );
        }

        // Misafir yorumları için
        for ($i = 0; $i < 10; $i++) {
            $provider = $providers->random();
            $plan = $plans->where('provider_id', $provider->id)->random();

            $status = fake()->randomElement(ReviewStatus::values());
            $publishedAt = ($status === ReviewStatus::APPROVED) ? fake()->dateTimeBetween('-1 year', 'now') : null;

            Review::firstOrCreate(
                [
                    'user_id' => null, // Misafir yorumu
                    'user_name' => fake()->name(),
                    'provider_id' => $provider->id,
                    'plan_id' => $plan->id,
                    'title' => fake()->sentence(3),
                ],
                [
                    'rating' => fake()->numberBetween(1, 5),
                    'content' => fake()->paragraph(3),
                    'published_at' => $publishedAt,
                    'status' => $status,
                ]
            );
        }

    }
}
