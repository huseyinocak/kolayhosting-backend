<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Paylaşımlı Hosting', 'description' => 'Birden fazla web sitesinin aynı sunucuyu paylaştığı hosting türü.'],
            ['name' => 'VPS Hosting', 'description' => 'Sanal özel sunucu hostingi, daha fazla kontrol ve esneklik sunar.'],
            ['name' => 'Dedicated Server', 'description' => 'Tamamen size ait fiziksel bir sunucu hostingi.'],
            ['name' => 'Cloud Hosting', 'description' => 'Kaynakların birden fazla sunucu arasında dağıtıldığı ölçeklenebilir hosting.'],
            ['name' => 'WordPress Hosting', 'description' => 'WordPress siteleri için optimize edilmiş hosting çözümleri.'],
            ['name' => 'E-ticaret Hosting', 'description' => 'Online mağazalar için özel olarak tasarlanmış hosting.'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                [
                    'slug' => Str::slug($categoryData['name']),
                    'description' => $categoryData['description']
                ]
            );
        }
    }
}
