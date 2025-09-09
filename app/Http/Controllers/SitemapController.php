<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Provider;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        // Temel URL'yi alın
        $baseUrl = "https://www.kolayhosting.com.tr/"; // .env dosyasındaki APP_URL'yi kullanır

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Ana Sayfa
        $xml .= '<url>';
        $xml .= '<loc>' . $baseUrl . '/</loc>';
        $xml .= '<lastmod>' . now()->format('Y-m-d') . '</lastmod>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '</url>';

        // Statik Sayfalar (Frontend'inizdeki ana sayfalar)
        $staticPages = [
            '/plans',
            '/providers',
            '/categories',
            '/features',
            '/compare',
            '/privacy-policy',
            '/terms-of-service',
            '/cookie-policy',
            // Diğer statik sayfalarınız varsa buraya ekleyin
        ];

        foreach ($staticPages as $page) {
            $xml .= '<url>';
            $xml .= '<loc>' . $baseUrl . $page . '</loc>';
            $xml .= '<lastmod>' . now()->format('Y-m-d') . '</lastmod>';
            $xml .= '<priority>0.9</priority>'; // Önceliği ayarlayın
            $xml .= '</url>';
        }

        // Dinamik Plan Detay Sayfaları
        $plans = Plan::all(); // Tüm planları çekin, gerektiğinde paginate veya limit kullanabilirsiniz
        foreach ($plans as $plan) {
            $xml .= '<url>';
            $xml .= '<loc>' . $baseUrl . '/plans/' . $plan->id . '</loc>'; // veya $plan->slug
            $xml .= '<lastmod>' . $plan->updated_at->format('Y-m-d') . '</lastmod>'; // Son güncelleme tarihini kullanın
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        // Dinamik Sağlayıcı Detay Sayfaları
        $providers = Provider::all(); // Tüm sağlayıcıları çekin
        foreach ($providers as $provider) {
            $xml .= '<url>';
            $xml .= '<loc>' . $baseUrl . '/providers/' . $provider->id . '</loc>'; // veya $provider->slug
            $xml .= '<lastmod>' . $provider->updated_at->format('Y-m-d') . '</lastmod>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        // Dinamik Kategoriye Ait Planlar Sayfaları (Eğer kategori detay sayfalarınız varsa)
        $categories = Category::all(); // Tüm kategorileri çekin
        foreach ($categories as $category) {
            $xml .= '<url>';
            $xml .= '<loc>' . $baseUrl . '/categories/' . $category->id . '</loc>'; // veya $category->slug
            $xml .= '<lastmod>' . $category->updated_at->format('Y-m-d') . '</lastmod>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        // XML yanıtını döndürün
        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
