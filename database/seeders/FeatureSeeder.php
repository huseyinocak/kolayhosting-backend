<?php

namespace Database\Seeders;

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
            ['name' => 'Disk Alanı', 'unit' => 'GB', 'type' => 'numeric'],
            ['name' => 'Bant Genişliği', 'unit' => 'GB', 'type' => 'numeric'],
            ['name' => 'Ücretsiz SSL', 'unit' => null, 'type' => 'boolean'],
            ['name' => 'Ücretsiz Alan Adı', 'unit' => null, 'type' => 'boolean'],
            ['name' => 'E-posta Hesapları', 'unit' => 'Adet', 'type' => 'numeric'],
            ['name' => 'Veritabanı Sayısı', 'unit' => 'Adet', 'type' => 'numeric'],
            ['name' => 'Yedekleme', 'unit' => null, 'type' => 'boolean'],
            ['name' => 'Kontrol Paneli', 'unit' => null, 'type' => 'text'],
            ['name' => 'Web Sitesi Sayısı', 'unit' => 'Adet', 'type' => 'numeric'],
            ['name' => 'CPU Çekirdeği', 'unit' => 'Adet', 'type' => 'numeric'],
            ['name' => 'RAM', 'unit' => 'GB', 'type' => 'numeric'],
        ];

        foreach ($features as $featureData) {
            Feature::create($featureData);
        }
    }
}
