<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provider;
use Illuminate\Support\Str;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'name' => 'Hostinger',
                'logo_url' => 'https://placehold.co/150x50/F8F8F8/000000?text=Hostinger',
                'website_url' => 'https://www.hostinger.com/',
                'description' => 'Uygun fiyatlı ve performanslı hosting çözümleri sunar.',
                'average_rating' => 4.7,
            ],
            [
                'name' => 'Bluehost',
                'logo_url' => 'https://placehold.co/150x50/F8F8F8/000000?text=Bluehost',
                'website_url' => 'https://www.bluehost.com/',
                'description' => 'WordPress için önerilen popüler bir hosting sağlayıcısı.',
                'average_rating' => 4.5,
            ],
            [
                'name' => 'SiteGround',
                'logo_url' => 'https://placehold.co/150x50/F8F8F8/000000?text=SiteGround',
                'website_url' => 'https://www.siteground.com/',
                'description' => 'Hızlı ve güvenli hosting hizmetleri sunar.',
                'average_rating' => 4.8,
            ],
            [
                'name' => 'A2 Hosting',
                'logo_url' => 'https://placehold.co/150x50/F8F8F8/000000?text=A2+Hosting',
                'website_url' => 'https://www.a2hosting.com/',
                'description' => 'Yüksek hızlı ve geliştirici dostu hosting çözümleri.',
                'average_rating' => 4.6,
            ],
            [
                'name' => 'DreamHost',
                'logo_url' => 'https://placehold.co/150x50/F8F8F8/000000?text=DreamHost',
                'website_url' => 'https://www.dreamhost.com/',
                'description' => 'Sınırsız bant genişliği ve depolama sunan güvenilir hosting.',
                'average_rating' => 4.4,
            ],
        ];

        foreach ($providers as $providerData) {
            Provider::create([
                'name' => $providerData['name'],
                'slug' => Str::slug($providerData['name']),
                'logo_url' => $providerData['logo_url'],
                'website_url' => $providerData['website_url'],
                'description' => $providerData['description'],
                'average_rating' => $providerData['average_rating'],
            ]);
        }
    }
}
