<?php

namespace Database\Seeders;

use App\Enums\FeatureType;
use App\Models\Feature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            ['name' => 'Depolama Alanı', 'unit' => 'GB', 'type' => FeatureType::NUMERIC],
            ['name' => 'Bant Genişliği', 'unit' => 'GB', 'type' => FeatureType::NUMERIC],
            ['name' => 'CPU Çekirdekleri', 'unit' => 'Çekirdek', 'type' => FeatureType::NUMERIC],
            ['name' => 'RAM', 'unit' => 'GB', 'type' => FeatureType::TEXT],
            ['name' => 'Web Sitesi Sayısı', 'unit' => 'Adet', 'type' => FeatureType::NUMERIC],
            ['name' => 'SSL Sertifikası', 'unit' => null, 'type' => FeatureType::BOOLEAN],
            ['name' => 'Ücretsiz Alan Adı', 'unit' => null, 'type' => FeatureType::BOOLEAN],
            ['name' => 'E-posta Hesapları', 'unit' => 'Adet', 'type' => FeatureType::NUMERIC],
            ['name' => 'Veritabanları', 'unit' => 'Adet', 'type' => FeatureType::NUMERIC],
            ['name' => 'Yedekleme', 'unit' => null, 'type' => FeatureType::BOOLEAN],
            ['name' => 'Kontrol Paneli', 'unit' => null, 'type' => FeatureType::TEXT],
            ['name' => 'Destek', 'unit' => null, 'type' => FeatureType::TEXT],
            ['name' => 'LiteSpeed Web Server', 'unit' => null, 'type' => FeatureType::BOOLEAN],
            ['name' => 'Otomatik WordPress Kurulumu', 'unit' => null, 'type' => FeatureType::BOOLEAN],
        ];

        foreach ($features as $featureData) {
            Feature::firstOrCreate(
                ['name' => $featureData['name']],
                $featureData
            );
        }
    }
}
