<?php

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Models\Plan;
use App\Models\Provider;
use App\Models\Review;
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
        $hostinger = Provider::where('slug', 'hostinger')->first();
        $bluehost = Provider::where('slug', 'bluehost')->first();
        $siteground = Provider::where('slug', 'siteground')->first();

        $hostingerPremiumPlan = Plan::where('slug', 'premium-web-hosting-1')->first();
        $bluehostBasicWordPressPlan = Plan::where('slug', 'basic-wordpress-2')->first();
        $sitegroundStartUpPlan = Plan::where('slug', 'startup-3')->first();

        $reviews = [
            [
                'provider_id' => $hostinger->id ?? null,
                'plan_id' => $hostingerPremiumPlan->id ?? null,
                'user_name' => 'Ahmet Yılmaz',
                'rating' => 5,
                'title' => 'Hostinger Premium çok iyi!',
                'content' => 'Hostinger Premium Web Hosting planından çok memnunum. Hız ve destek mükemmel.',
                'published_at' => Carbon::now()->subDays(10),
                'status' => ReviewStatus::PENDING, // Varsayılan olarak PENDING
            ],
            [
                'provider_id' => $bluehost->id ?? null,
                'plan_id' => $bluehostBasicWordPressPlan->id ?? null,
                'user_name' => 'Ayşe Demir',
                'rating' => 4,
                'title' => 'Bluehost WordPress için ideal',
                'content' => 'WordPress sitem için Bluehost Basic planını kullanıyorum. Kurulumu kolay ve performansı tatmin edici.',
                'published_at' => Carbon::now()->subDays(15),
                'status' => ReviewStatus::PENDING, // Varsayılan olarak PENDING
            ],
            [
                'provider_id' => $siteground->id ?? null,
                'plan_id' => $sitegroundStartUpPlan->id ?? null,
                'user_name' => 'Mehmet Can',
                'rating' => 5,
                'title' => 'SiteGround Hızı Harika',
                'content' => 'SiteGround StartUp planı ile sitem çok hızlı çalışıyor. Destek ekibi de çok yardımcı.',
                'published_at' => Carbon::now()->subDays(5),
                'status' => ReviewStatus::PENDING, // Varsayılan olarak PENDING
            ],
            [
                'provider_id' => $hostinger->id ?? null,
                'plan_id' => null, // Sadece sağlayıcıya genel bir yorum
                'user_name' => 'Zeynep Kaya',
                'rating' => 4,
                'title' => 'Hostinger Genel Değerlendirme',
                'content' => 'Hostinger genel olarak iyi bir deneyim sunuyor, fiyatları da oldukça uygun.',
                'published_at' => Carbon::now()->subDays(20),
                'status' => ReviewStatus::PENDING, // Varsayılan olarak PENDING
            ],
            [
                'provider_id' => null, // Sadece plana özel bir yorum
                'plan_id' => $hostingerPremiumPlan->id ?? null,
                'user_name' => 'Emre Aktaş',
                'rating' => 3,
                'title' => 'Hostinger Premium Yenileme Fiyatı',
                'content' => 'İlk başta çok iyiydi ama yenileme fiyatları biraz yüksek geldi.',
                'published_at' => Carbon::now()->subDays(8),
                'status' => ReviewStatus::PENDING, // Varsayılan olarak PENDING
            ],
        ];
        foreach ($reviews as $reviewData) {
            Review::create($reviewData);
        }

    }
}
