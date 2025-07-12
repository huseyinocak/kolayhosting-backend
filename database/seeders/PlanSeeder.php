<?php

namespace Database\Seeders;

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
        $webHostingCategory = Category::where('slug', 'web-hosting')->first()->id;
        $vpsHostingCategory = Category::where('slug', 'vps-hosting')->first()->id;
        $wordpressHostingCategory = Category::where('slug', 'wordpress-hosting')->first()->id;

        $hostinger = Provider::where('slug', 'hostinger')->first()->id;
        $bluehost = Provider::where('slug', 'bluehost')->first()->id;
        $siteground = Provider::where('slug', 'siteground')->first()->id;
        $a2hosting = Provider::where('slug', 'a2-hosting')->first()->id;
        $dreamhost = Provider::where('slug', 'dreamhost')->first()->id;

        $plans = [
            // Hostinger Plans
            [
                'provider_id' => $hostinger,
                'category_id' => $webHostingCategory,
                'name' => 'Single Web Hosting',
                'price' => 1.99,
                'currency' => 'USD',
                'renewal_price' => 3.99,
                'discount_percentage' => 50.13,
                'features_summary' => '1 Web Sitesi, 50 GB SSD, 100 GB Bant Genişliği',
                'link' => 'https://www.hostinger.com/single-web-hosting',
                'status' => 'active',
            ],
            [
                'provider_id' => $hostinger,
                'category_id' => $webHostingCategory,
                'name' => 'Premium Web Hosting',
                'price' => 2.99,
                'currency' => 'USD',
                'renewal_price' => 6.99,
                'discount_percentage' => 57.22,
                'features_summary' => '100 Web Sitesi, 100 GB SSD, Sınırsız Bant Genişliği, Ücretsiz Alan Adı',
                'link' => 'https://www.hostinger.com/premium-web-hosting',
                'status' => 'active',
            ],
            [
                'provider_id' => $hostinger,
                'category_id' => $vpsHostingCategory,
                'name' => 'VPS Plan 1',
                'price' => 3.99,
                'currency' => 'USD',
                'renewal_price' => 8.99,
                'discount_percentage' => 55.62,
                'features_summary' => '1 vCPU, 1 GB RAM, 20 GB SSD, 1 TB Bant Genişliği',
                'link' => 'https://www.hostinger.com/vps-hosting',
                'status' => 'active',
            ],

            // Bluehost Plans
            [
                'provider_id' => $bluehost,
                'category_id' => $wordpressHostingCategory,
                'name' => 'Basic WordPress',
                'price' => 2.95,
                'currency' => 'USD',
                'renewal_price' => 9.99,
                'discount_percentage' => 70.47,
                'features_summary' => '1 Web Sitesi, 50 GB SSD, Ücretsiz SSL, Ücretsiz Alan Adı',
                'link' => 'https://www.bluehost.com/wordpress-hosting',
                'status' => 'active',
            ],
            [
                'provider_id' => $bluehost,
                'category_id' => $webHostingCategory,
                'name' => 'Choice Plus',
                'price' => 5.45,
                'currency' => 'USD',
                'renewal_price' => 18.99,
                'discount_percentage' => 71.20,
                'features_summary' => 'Sınırsız Web Sitesi, Sınırsız SSD, Ücretsiz Alan Adı, Ücretsiz Otomatik Yedekleme',
                'link' => 'https://www.bluehost.com/shared-hosting',
                'status' => 'active',
            ],

            // SiteGround Plans
            [
                'provider_id' => $siteground,
                'category_id' => $webHostingCategory,
                'name' => 'StartUp',
                'price' => 3.99,
                'currency' => 'USD',
                'renewal_price' => 17.99,
                'discount_percentage' => 77.82,
                'features_summary' => '1 Web Sitesi, 10 GB Web Alanı, 10.000 Aylık Ziyaretçi',
                'link' => 'https://www.siteground.com/web-hosting.htm',
                'status' => 'active',
            ],
            [
                'provider_id' => $siteground,
                'category_id' => $wordpressHostingCategory,
                'name' => 'GrowBig',
                'price' => 6.69,
                'currency' => 'USD',
                'renewal_price' => 29.99,
                'discount_percentage' => 77.69,
                'features_summary' => 'Sınırsız Web Sitesi, 20 GB Web Alanı, 25.000 Aylık Ziyaretçi, Ücretsiz Site Transferi',
                'link' => 'https://www.siteground.com/wordpress-hosting.htm',
                'status' => 'active',
            ],

            // A2 Hosting Plans
            [
                'provider_id' => $a2hosting,
                'category_id' => $webHostingCategory,
                'name' => 'Lite Shared Hosting',
                'price' => 2.99,
                'currency' => 'USD',
                'renewal_price' => 10.99,
                'discount_percentage' => 72.79,
                'features_summary' => '1 Web Sitesi, Sınırsız SSD, Sınırsız Transfer, Ücretsiz SSL',
                'link' => 'https://www.a2hosting.com/shared-hosting',
                'status' => 'active',
            ],

            // DreamHost Plans
            [
                'provider_id' => $dreamhost,
                'category_id' => $webHostingCategory,
                'name' => 'Shared Starter',
                'price' => 2.59,
                'currency' => 'USD',
                'renewal_price' => 5.99,
                'discount_percentage' => 56.76,
                'features_summary' => '1 Web Sitesi, Sınırsız Trafik, Hızlı SSD Depolama, Ücretsiz SSL',
                'link' => 'https://www.dreamhost.com/hosting/shared/',
                'status' => 'active',
            ],
        ];

        foreach ($plans as $planData) {
            Plan::create([
                'provider_id' => $planData['provider_id'],
                'category_id' => $planData['category_id'],
                'name' => $planData['name'],
                'slug' => Str::slug($planData['name'] . '-' . $planData['provider_id']), // Benzersiz slug için provider_id ekledik
                'price' => $planData['price'],
                'currency' => $planData['currency'],
                'renewal_price' => $planData['renewal_price'],
                'discount_percentage' => $planData['discount_percentage'],
                'features_summary' => $planData['features_summary'],
                'link' => $planData['link'],
                'status' => $planData['status'],
            ]);
        }
    }
}
