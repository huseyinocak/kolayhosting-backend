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
            ['name' => 'Web Hosting', 'description' => 'Küçük ve orta ölçekli web siteleri için genel hosting çözümleri.'],
            ['name' => 'VPS Hosting', 'description' => 'Daha fazla kontrol ve kaynak gerektiren siteler için sanal özel sunucu hosting.'],
            ['name' => 'Dedicated Hosting', 'description' => 'Yüksek performans ve tam kontrol sağlayan özel sunucu hosting.'],
            ['name' => 'Cloud Hosting', 'description' => 'Ölçeklenebilir ve esnek bulut tabanlı hosting çözümleri.'],
            ['name' => 'WordPress Hosting', 'description' => 'WordPress siteleri için optimize edilmiş hosting.'],
        ];

        foreach ($categories as $categoryData) {
            Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'description' => $categoryData['description'],
            ]);
        }
    }
}
