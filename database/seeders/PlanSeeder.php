<?php

namespace Database\Seeders;

use App\Enums\PlanStatus;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Provider;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = Provider::all();
        $categories = Category::all();

        if ($providers->isEmpty() || $categories->isEmpty()) {
            $this->command->info('Provider veya Category bulunamadı. Lütfen önce ProviderSeeder ve CategorySeeder\'ı çalıştırın.');
            return;
        }

        for ($i = 0; $i < 20; $i++) { // 20 adet rastgele plan oluştur
            $provider = $providers->random();
            $category = $categories->random();
            $name = ucfirst(fake()->word()) . ' ' . ucfirst(fake()->word()) . ' Plan';

            Plan::firstOrCreate(
                [
                    'name' => $name,
                    'provider_id' => $provider->id,
                ],
                [
                    'category_id' => $category->id,
                    'slug' => Str::slug($name . '-' . $provider->id),
                    'price' => fake()->randomFloat(2, 5, 100),
                    'currency' => 'USD',
                    'renewal_price' => fake()->randomFloat(2, 10, 150),
                    'discount_percentage' => fake()->boolean(30) ? fake()->randomFloat(2, 5, 50) : null,
                    'features_summary' => fake()->sentence(10),
                    'link' => fake()->url(),
                    'status' => fake()->randomElement(PlanStatus::values()),
                    'affiliate_url' => fake()->boolean(70) ? fake()->url() : null,
                ]
            );
        }
    }
}
