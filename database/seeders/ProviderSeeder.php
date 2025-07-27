<?php

namespace Database\Seeders;

use App\Enums\PlanStatus;
use App\Models\Category;
use App\Models\Plan;
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
                'description' => 'Uygun fiyatlı ve hızlı hosting çözümleri sunar.',
                'website_url' => 'https://www.hostinger.com.tr/',
                'affiliate_url' => 'https://www.hostinger.com.tr/affiliate-link-hostinger',
                'logo_url' => 'provider_logos/hostinger_logo.png', // Örnek placeholder
            ],
            [
                'name' => 'Natro',
                'description' => 'Türkiye merkezli köklü hosting firması.',
                'website_url' => 'https://www.natro.com/',
                'affiliate_url' => 'https://www.natro.com/affiliate-link-natro',
                'logo_url' => 'provider_logos/natro_logo.png', // Örnek placeholder
            ],
            [
                'name' => 'Godaddy',
                'description' => 'Alan adı ve hosting hizmetlerinde dünya lideri.',
                'website_url' => 'https://tr.godaddy.com/',
                'affiliate_url' => 'https://tr.godaddy.com/affiliate-link-godaddy',
                'logo_url' => 'provider_logos/godaddy_logo.png', // Örnek placeholder
            ],
            [
                'name' => 'Turhost',
                'description' => 'Güvenilir ve performanslı hosting hizmetleri.',
                'website_url' => 'https://www.turhost.com/',
                'affiliate_url' => 'https://www.turhost.com/affiliate-link-turhost',
                'logo_url' => 'provider_logos/turhost_logo.png', // Örnek placeholder
            ],
            [
                'name' => 'Güzel Hosting',
                'description' => 'Uygun fiyatlı ve kaliteli hosting çözümleri.',
                'website_url' => 'https://www.guzelhosting.com/',
                'affiliate_url' => 'https://www.guzelhosting.com/affiliate-link-guzelhosting',
                'logo_url' => 'provider_logos/guzelhosting_logo.png', // Örnek placeholder
            ],
        ];

        foreach ($providers as $providerData) {
            Provider::firstOrCreate(
                ['name' => $providerData['name']],
                array_merge($providerData, ['slug' => Str::slug($providerData['name'])])
            );
        }
    }
}
