<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Örnek planları ve özellikleri alalım
        $hostingerSingleWebHosting = Plan::where('slug', 'single-web-hosting-1')->first();
        $hostingerPremiumWebHosting = Plan::where('slug', 'premium-web-hosting-1')->first();
        $hostingerVpsPlan1 = Plan::where('slug', 'vps-plan-1-1')->first();
        $bluehostBasicWordPress = Plan::where('slug', 'basic-wordpress-2')->first();
        $bluehostChoicePlus = Plan::where('slug', 'choice-plus-2')->first();
        $sitegroundStartUp = Plan::where('slug', 'startup-3')->first();
        $sitegroundGrowBig = Plan::where('slug', 'growbig-3')->first();
        $a2HostingLite = Plan::where('slug', 'lite-shared-hosting-4')->first();
        $dreamhostSharedStarter = Plan::where('slug', 'shared-starter-5')->first();


        $diskSpace = Feature::where('name', 'Disk Alanı')->first()->id;
        $bandwidth = Feature::where('name', 'Bant Genişliği')->first()->id;
        $freeSsl = Feature::where('name', 'Ücretsiz SSL')->first()->id;
        $freeDomain = Feature::where('name', 'Ücretsiz Alan Adı')->first()->id;
        $emailAccounts = Feature::where('name', 'E-posta Hesapları')->first()->id;
        $databaseCount = Feature::where('name', 'Veritabanı Sayısı')->first()->id;
        $backup = Feature::where('name', 'Yedekleme')->first()->id;
        $controlPanel = Feature::where('name', 'Kontrol Paneli')->first()->id;
        $websiteCount = Feature::where('name', 'Web Sitesi Sayısı')->first()->id;
        $cpuCores = Feature::where('name', 'CPU Çekirdeği')->first()->id;
        $ram = Feature::where('name', 'RAM')->first()->id;


        // Hostinger Single Web Hosting Özellikleri
        if ($hostingerSingleWebHosting) {
            $hostingerSingleWebHosting->features()->attach([
                $diskSpace => ['value' => '50'],
                $bandwidth => ['value' => '100'],
                $freeSsl => ['value' => 'Evet'],
                $emailAccounts => ['value' => '1'],
                $databaseCount => ['value' => '1'],
                $websiteCount => ['value' => '1'],
            ]);
        }

        // Hostinger Premium Web Hosting Özellikleri
        if ($hostingerPremiumWebHosting) {
            $hostingerPremiumWebHosting->features()->attach([
                $diskSpace => ['value' => '100'],
                $bandwidth => ['value' => 'Sınırsız'],
                $freeSsl => ['value' => 'Evet'],
                $freeDomain => ['value' => 'Evet'],
                $emailAccounts => ['value' => 'Sınırsız'],
                $databaseCount => ['value' => 'Sınırsız'],
                $websiteCount => ['value' => '100'],
                $backup => ['value' => 'Haftalık'],
            ]);
        }

        // Hostinger VPS Plan 1 Özellikleri
        if ($hostingerVpsPlan1) {
            $hostingerVpsPlan1->features()->attach([
                $cpuCores => ['value' => '1'],
                $ram => ['value' => '1'],
                $diskSpace => ['value' => '20'],
                $bandwidth => ['value' => '1000'],
                $controlPanel => ['value' => 'Opsiyonel'],
            ]);
        }

        // Bluehost Basic WordPress Özellikleri
        if ($bluehostBasicWordPress) {
            $bluehostBasicWordPress->features()->attach([
                $diskSpace => ['value' => '50'],
                $freeSsl => ['value' => 'Evet'],
                $freeDomain => ['value' => 'Evet'],
                $websiteCount => ['value' => '1'],
            ]);
        }

        // Bluehost Choice Plus Özellikleri
        if ($bluehostChoicePlus) {
            $bluehostChoicePlus->features()->attach([
                $diskSpace => ['value' => 'Sınırsız'],
                $bandwidth => ['value' => 'Sınırsız'],
                $freeSsl => ['value' => 'Evet'],
                $freeDomain => ['value' => 'Evet'],
                $websiteCount => ['value' => 'Sınırsız'],
                $backup => ['value' => 'Evet'],
            ]);
        }

        // SiteGround StartUp Özellikleri
        if ($sitegroundStartUp) {
            $sitegroundStartUp->features()->attach([
                $diskSpace => ['value' => '10'],
                $bandwidth => ['value' => 'Sınırsız'],
                $freeSsl => ['value' => 'Evet'],
                $websiteCount => ['value' => '1'],
                $controlPanel => ['value' => 'Site Tools'],
            ]);
        }

        // SiteGround GrowBig Özellikleri
        if ($sitegroundGrowBig) {
            $sitegroundGrowBig->features()->attach([
                $diskSpace => ['value' => '20'],
                $bandwidth => ['value' => 'Sınırsız'],
                $freeSsl => ['value' => 'Evet'],
                $websiteCount => ['value' => 'Sınırsız'],
                $backup => ['value' => 'Günlük'],
                $controlPanel => ['value' => 'Site Tools'],
            ]);
        }

        // A2 Hosting Lite Shared Hosting Özellikleri
        if ($a2HostingLite) {
            $a2HostingLite->features()->attach([
                $diskSpace => ['value' => 'Sınırsız'],
                $bandwidth => ['value' => 'Sınırsız'],
                $freeSsl => ['value' => 'Evet'],
                $websiteCount => ['value' => '1'],
                $controlPanel => ['value' => 'cPanel'],
            ]);
        }

        // DreamHost Shared Starter Özellikleri
        if ($dreamhostSharedStarter) {
            $dreamhostSharedStarter->features()->attach([
                $diskSpace => ['value' => 'Sınırsız'],
                $bandwidth => ['value' => 'Sınırsız'],
                $freeSsl => ['value' => 'Evet'],
                $websiteCount => ['value' => '1'],
            ]);
        }
    }
}
