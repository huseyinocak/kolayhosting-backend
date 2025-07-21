<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    /**
     * Gemini API'den sohbet yanıtı alır.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = $request->input('message');
        $geminiApiKey = env('GEMINI_API_KEY');

        if (empty($geminiApiKey)) {
            Log::error('GEMINI_API_KEY .env dosyasında tanımlı değil.');
            return response()->json(['error' => 'AI servisi yapılandırılmamış.'], 500);
        }

        $lowerCaseMessage = strtolower($userMessage);
        $context = "";
        $isComparisonRequest = false;
        $comparisonPlans = collect(); // Boş bir Eloquent Collection olarak başlatıldı

        // --- Niyet Tespiti ve Varlık Çıkarımı ---
        // Kullanıcının mesajında karşılaştırma anahtar kelimeleri geçiyorsa
        if (str_contains($lowerCaseMessage, 'karşılaştır') || str_contains($lowerCaseMessage, 'fark ne') || str_contains($lowerCaseMessage, 'hangisi iyi')) {
            $isComparisonRequest = true;

            // Mesajdan olası plan adlarını veya sağlayıcı adlarını çıkarmaya çalış
            preg_match_all('/"([^"]+)"/', $userMessage, $matches); // Tırnak içindeki ifadeleri yakala
            $potentialNames = $matches[1];

            // Veya doğrudan kelimeleri kontrol et (daha az kesin)
            $keywordsForPlans = ['plan', 'hosting', 'sunucu'];
            foreach ($keywordsForPlans as $keyword) {
                if (str_contains($lowerCaseMessage, $keyword)) {
                    // Eğer kullanıcı "Hostinger planlarını karşılaştır" derse, Hostinger'ın planlarını çek
                    if (str_contains($lowerCaseMessage, 'hostinger')) {
                        $provider = Provider::where('name', 'like', '%hostinger%')->first();
                        if ($provider) {
                            $comparisonPlans = $provider->plans()->limit(2)->get(); // En fazla 2 plan
                            break;
                        }
                    }
                    // Genel olarak "planları karşılaştır" denirse, en popüler 2 planı çek
                    if ($comparisonPlans->isEmpty()) {
                        $comparisonPlans = Plan::orderBy('price', 'asc')->limit(2)->get(); // Örnek olarak en ucuz 2 plan
                    }
                    break;
                }
            }

            // Çıkarılan isimlere göre veritabanından planları çek
            if (!empty($potentialNames)) {
                $fetchedPlans = Plan::whereIn('name', $potentialNames)
                    ->with(['provider', 'category'])
                    ->limit(2) // Maksimum 2 plan karşılaştırma için
                    ->get();
                if ($fetchedPlans->isNotEmpty()) {
                    $comparisonPlans = $fetchedPlans;
                }
            }

            // Eğer hala plan bulunamadıysa ve karşılaştırma isteği varsa, genel bir karşılaştırma bağlamı ekle
            if ($comparisonPlans->isEmpty()) {
                $context .= "Kullanıcı genel bir hosting planı karşılaştırması istiyor. KolayHosting, farklı sağlayıcılardan paylaşımlı, VPS, özel sunucu gibi çeşitli planları özelliklerine, fiyatlarına, performanslarına ve kullanıcı yorumlarına göre karşılaştırmanıza olanak tanır. Genellikle depolama (SSD/NVMe), bant genişliği, CPU/RAM, ücretsiz SSL, alan adı, e-posta hesapları ve 7/24 destek gibi özellikler karşılaştırılır.\n";
            } else {
                // Bulunan planları bağlama ekle
                $context .= "İşte karşılaştırmak istediğiniz planlar hakkında bilgiler:\n";
                foreach ($comparisonPlans as $plan) {
                    $planSummary = $plan->features_summary ?? 'Özet bilgi bulunmamaktadır.';
                    $context .= "- Plan Adı: " . $plan->name . ", Sağlayıcı: " . ($plan->provider->name ?? 'Bilinmiyor') . ", Fiyat: " . $plan->price . " " . $plan->currency . ", Özellikler Özeti: " . substr($planSummary, 0, 100) . "...\n";
                }
            }
        }


        // --- Normal Bağlam Sağlama (Karşılaştırma İsteği Değilse) ---
        if (!$isComparisonRequest) {
            // Aranabilir alanların tanımları (Modellerinizdeki fillable'a göre güncellendi)
            $searchableFields = [
                'plans' => [
                    'name',
                    'features_summary'
                ],
                'providers' => [
                    'name',
                    'description'
                ],
                'categories' => [
                    'name',
                    'description'
                ],
                'features' => [
                    'name'
                ]
            ];

            // Ortak anahtar kelime grupları ve eş anlamlıları
            $keywords = [
                'speed' => ['hız', 'performans', 'ssd', 'nvme', 'hızlı'],
                'price' => ['ucuz', 'ekonomik', 'bütçe', 'indirimli', 'fiyat'],
                'wordpress' => ['wordpress', 'wp', 'blog', 'site'],
                'ecommerce' => ['e-ticaret', 'eticaret', 'mağaza', 'shop', 'online satış'],
                'support' => ['destek', 'yardım', '7/24', 'müşteri hizmetleri', 'canlı destek'],
                'security' => ['güvenlik', 'ssl', 'koruma', 'firewall', 'yedekleme'],
                'domain' => ['alan adı', 'domain', 'alanadı'],
                'email' => ['e-posta', 'email', 'mail'],
                'cpanel' => ['cpanel', 'panel', 'kontrol paneli'],
                'vps' => ['vps', 'sanal sunucu'],
                'dedicated' => ['dedicated', 'özel sunucu'],
                'shared' => ['shared', 'paylaşımlı'],
                'hosting' => ['hosting', 'barındırma', 'sunucu'],
                'comparison' => ['karşılaştır', 'karşılaştırma', 'fark', 'hangisi iyi'],
            ];

            // Genel sorgu fonksiyonu
            $buildSmartQuery = function ($model, $fields, $message) use ($keywords) {
                $query = $model::query();
                $fullTextSearchFields = [];
                foreach ($fields as $field) {
                    $fullTextSearchFields[] = $field;
                }

                if (!empty($fullTextSearchFields)) {
                    $query->where(function ($q) use ($fullTextSearchFields, $message) {
                        $q->whereFullText($fullTextSearchFields, $message);
                    });
                }

                $query->orWhere(function ($q) use ($fields, $message, $keywords) {
                    foreach ($fields as $field) {
                        $q->orWhere($field, 'like', '%' . $message . '%');
                    }
                    foreach ($keywords as $group => $synonyms) {
                        foreach ($synonyms as $synonym) {
                            if (str_contains($message, $synonym)) {
                                foreach ($fields as $field) {
                                    $q->orWhere($field, 'like', '%' . $synonym . '%');
                                }
                            }
                        }
                    }
                });
                return $query;
            };

            // 1. Plan Araması
            $planQuery = $buildSmartQuery(Plan::class, $searchableFields['plans'], $lowerCaseMessage);
            $plans = $planQuery->with(['provider', 'category'])->limit(2)->get();

            if ($plans->isNotEmpty()) {
                $context .= "Kullanıcı belirli hosting planları hakkında bilgi istiyor. İşte bulabildiğim bazı ilgili planlar:\n";
                foreach ($plans as $plan) {
                    $planSummary = $plan->features_summary ?? 'Özet bilgi bulunmamaktadır.';
                    $context .= "- Plan Adı: " . $plan->name . ", Sağlayıcı: " . ($plan->provider->name ?? 'Bilinmiyor') . ", Kategori: " . ($plan->category->name ?? 'Bilinmiyor') . ", Fiyat: " . $plan->price . " " . $plan->currency . ", Özellikler Özeti: " . substr($planSummary, 0, 100) . "...\n";
                }
            }

            // 2. Sağlayıcı Araması
            $providerQuery = $buildSmartQuery(Provider::class, $searchableFields['providers'], $lowerCaseMessage);
            $providers = $providerQuery->withCount('reviews')->limit(1)->get();

            if ($providers->isNotEmpty()) {
                $context .= "Kullanıcı belirli bir hosting sağlayıcısı hakkında bilgi istiyor. İşte bulabildiğim ilgili sağlayıcılar:\n";
                foreach ($providers as $provider) {
                    $context .= "- Sağlayıcı Adı: " . $provider->name . ", Açıklama: " . substr($provider->description, 0, 150) . "..., Ortalama Derecelendirme: " . ($provider->average_rating ?? 'Yok') . "/5 (" . ($provider->reviews_count ?? '0') . " yorum)\n";
                }
            }

            // 3. Kategori Araması
            $categoryQuery = $buildSmartQuery(Category::class, $searchableFields['categories'], $lowerCaseMessage);
            $categories = $categoryQuery->withCount('plans')->limit(1)->get();

            if ($categories->isNotEmpty()) {
                $context .= "Kullanıcı belirli bir hosting kategorisi hakkında bilgi istiyor. İşte bulabildiğim ilgili kategoriler:\n";
                foreach ($categories as $category) {
                    $context .= "- Kategori Adı: " . $category->name . ", Açıklama: " . substr($category->description, 0, 100) . "...\n";
                }
            }

            // 4. Özellik Araması
            $featureQuery = $buildSmartQuery(Feature::class, $searchableFields['features'], $lowerCaseMessage);
            $features = $featureQuery->limit(2)->get();

            if ($features->isNotEmpty()) {
                $context .= "Kullanıcı belirli hosting özellikleri hakkında bilgi istiyor. İşte bulabildiğim ilgili özellikler:\n";
                foreach ($features as $feature) {
                    $featureSummary = $feature->name;
                    $context .= "- Özellik Adı: " . $feature->name . ", Açıklama: " . substr($featureSummary, 0, 100) . "...\n";
                }
            }
        }


        // Yapay zekaya gönderilecek nihai prompt (Sistem Mesajı olarak güncellendi)
        $systemMessage = "Sen KolayHosting web sitesinin yapay zeka asistanısın. Görevin, kullanıcılara web hosting planları, sağlayıcıları, özellikleri ve karşılaştırma konularında doğru, faydalı ve tarafsız bilgiler sunmaktır. Yanıtların kısa, öz ve anlaşılır olmalı. Teknik terimleri basitçe açıkla. KolayHosting'in ana amacının hosting firmalarını ve planlarını karşılaştırmak olduğunu vurgula. Sadece web hosting ile ilgili sorulara yanıt ver. Hosting dışı konularda soru gelirse, kibarca sadece hosting ile ilgili konularda yardımcı olabileceğini belirt. Kullanıcının sorusuna en uygun bilgiyi sağlamaya odaklan.";

        // Few-Shot Learning Örnekleri
        $fewShotExamples = "
Kullanıcı: En ucuz hosting planı hangisi?
Yapay Zeka: KolayHosting olarak size en uygun fiyatlı hosting planlarını bulmanızda yardımcı olabilirim. Bütçe dostu planlar genellikle paylaşımlı hosting kategorisinde yer alır ve temel web sitesi ihtiyaçları için idealdir. Daha detaylı bilgi için 'Planları Keşfet' sayfamızı ziyaret edebilirsiniz.

Kullanıcı: WordPress hosting nedir?
Yapay Zeka: WordPress hosting, WordPress tabanlı web siteleri için özel olarak optimize edilmiş bir hosting türüdür. Genellikle daha yüksek performans, güvenlik ve kolay kurulum imkanları sunar. KolayHosting'de farklı sağlayıcıların WordPress hosting planlarını karşılaştırabilirsiniz.

Kullanıcı: Hostinger ve GoDaddy planlarını karşılaştır.
Yapay Zeka: Hostinger ve GoDaddy, popüler hosting sağlayıcılarıdır. Hostinger genellikle uygun fiyatlı planları ve kullanıcı dostu arayüzü ile bilinirken, GoDaddy daha geniş bir ürün yelpazesi ve domain hizmetleri sunar. İki sağlayıcının da paylaşımlı, WordPress, VPS gibi farklı hosting türleri bulunur. Detaylı özellik ve fiyat karşılaştırması için web sitemizdeki karşılaştırma aracını kullanabilirsiniz.
";
        // Chain-of-Thought Prompting Talimatı
        $chainOfThoughtInstruction = "\n\nYanıt vermeden önce, lütfen sağlanan bağlam bilgilerini ve kullanıcının sorusunu dikkatlice analiz et. Karşılaştırma veya detaylı bilgi istendiğinde, önce ilgili verileri özetle ve ardından yanıtını oluştur. Her zaman KolayHosting'in amacına uygun, tarafsız ve bilgilendirici olmaya özen göster.";


        $fullPrompt = $systemMessage . $fewShotExamples . $chainOfThoughtInstruction; // Sistem mesajına örnekleri ve CoT talimatını ekle

        if (!empty($context)) {
            $fullPrompt .= "\n\nİşte sana yardımcı olabilecek ek bağlam bilgileri:\n" . $context;
        }

        $fullPrompt .= "\n\nKullanıcının sorusu: " . $userMessage;

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $fullPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topP' => 0.95,
                'topK' => 40,
                'maxOutputTokens' => 500,
            ],
        ];

        // --- Yapılandırılmış Yanıt İçin Schema Ekleme ---
        if ($isComparisonRequest && $comparisonPlans->isNotEmpty()) {
            $payload['generationConfig']['responseMimeType'] = "application/json";
            $payload['generationConfig']['responseSchema'] = [
                'type' => 'ARRAY',
                'items' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'plan_id' => ['type' => 'INTEGER'],
                        'plan_name' => ['type' => 'STRING'],
                        'provider_name' => ['type' => 'STRING'],
                        'category_name' => ['type' => 'STRING'],
                        'price' => ['type' => 'NUMBER'],
                        'currency' => ['type' => 'STRING'],
                        'features_summary' => ['type' => 'STRING'],
                        'link' => ['type' => 'STRING'],
                    ],
                    'required' => ['plan_id', 'plan_name', 'provider_name', 'price', 'currency'],
                    'propertyOrdering' => [
                        'plan_id',
                        'plan_name',
                        'provider_name',
                        'category_name',
                        'price',
                        'currency',
                        'features_summary',
                        'link'
                    ]
                ]
            ];
            // Prompt'u JSON formatında yanıt vermesi için güncelle
            $fullPrompt .= "\n\nLütfen bu planları karşılaştırmalı olarak, aşağıdaki JSON şemasına uygun bir dizi olarak yanıtla. Sadece JSON çıktısı ver.";
            $payload['contents'][0]['parts'][0]['text'] = $fullPrompt;
        }


        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$geminiApiKey}", $payload);

            Log::info('Gemini API Yanıtı:', ['status' => $response->status(), 'body' => $response->body()]);

            if ($response->successful()) {
                $responseData = $response->json();

                if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                    $aiResponseContent = $responseData['candidates'][0]['content']['parts'][0]['text'];

                    // Eğer karşılaştırma isteği ise, JSON'ı parse et ve yapılandırılmış yanıt olarak gönder
                    if ($isComparisonRequest && $comparisonPlans->isNotEmpty()) {
                        try {
                            $parsedJson = json_decode($aiResponseContent, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                return response()->json(['type' => 'structured', 'data' => $parsedJson]);
                            } else {
                                Log::warning('Gemini API JSON yanıtı parse edilemedi.', ['response_body' => $aiResponseContent]);
                                // JSON parse edilemezse, yine de ham metni gönder
                                return response()->json(['type' => 'text', 'reply' => $aiResponseContent]);
                            }
                        } catch (\Exception $e) {
                            Log::error('JSON yanıtını işlerken hata:', ['exception' => $e->getMessage(), 'response_body' => $aiResponseContent]);
                            return response()->json(['type' => 'text', 'reply' => $aiResponseContent]); // Hata durumunda metin olarak geri dön
                        }
                    } else {
                        // Normal metin yanıtı
                        return response()->json(['type' => 'text', 'reply' => $aiResponseContent]);
                    }
                } else {
                    Log::warning('Gemini API yanıtında beklenen metin bulunamadı.', ['response' => $responseData]);
                    return response()->json(['type' => 'text', 'reply' => 'Yapay zeka yanıtı işlenemedi.']);
                }
            } else {
                Log::error('Gemini API isteği başarısız oldu.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers(),
                ]);
                return response()->json(['error' => 'Yapay zeka servisine ulaşılamıyor.'], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Yapay zeka ile iletişim hatası:', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Sunucu tarafında bir hata oluştu.'], 500);
        }
    }
}
