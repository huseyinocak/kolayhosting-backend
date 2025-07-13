<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FeatureController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Rotaları
|--------------------------------------------------------------------------
|
| Bu dosya, API'nizin rotalarını kaydetmek için kullanılır. Bu rotalar
| RouteServiceProvider tarafından bir "api" middleware grubu kullanılarak
| yüklenir. API'nizi oluşturmaya başlayın!
|
*/

// API Versiyon 1 (v1) Rotaları
Route::prefix('v1')->group(function () {


    // Kimlik Doğrulama Rotaları (Auth Rate Limiter ile korunur)
    // Bu rotalar için 'auth' adını verdiğimiz rate limiter'ı kullanıyoruz.
    Route::middleware('throttle:auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    // Kimliği doğrulanmış kullanıcılar için korumalı rotalar
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']); // Kimliği doğrulanmış kullanıcı bilgilerini getir

        // Kategoriler için API rotaları
        Route::apiResource('categories', CategoryController::class);
        Route::get('categories/{category}/plans', [CategoryController::class, 'getPlansByCategory']);

        // Sağlayıcılar için API rotaları
        Route::apiResource('providers', ProviderController::class);
        Route::get('providers/{provider}/plans', [ProviderController::class, 'getPlansByProvider']);
        Route::get('providers/{provider}/reviews', [ProviderController::class, 'getReviewsByProvider']);

        // Planlar için API rotaları
        Route::apiResource('plans', PlanController::class);
        Route::get('plans/{plan}/features', [PlanController::class, 'getFeaturesByPlan']);
        Route::get('plans/{plan}/reviews', [PlanController::class, 'getReviewsByPlan']);

        // Özellikler için API rotaları
        Route::apiResource('features', FeatureController::class);

        // İncelemeler için API rotaları
        Route::apiResource('reviews', ReviewController::class);
    });

    // İsteğe bağlı: Public olarak erişilebilir sadece okuma rotaları (eğer varsa)
    // Örneğin, kullanıcılar giriş yapmadan da kategori veya plan listelerini görebilir.
    // Ancak CRUD işlemleri için kimlik doğrulama gerekecektir.
    // Route::get('categories', [CategoryController::class, 'index']);
    // Route::get('categories/{category}', [CategoryController::class, 'show']);
    // ... diğer public rotalar ...

});

// Gelecekteki versiyonlar için örnek yapı:
/*
Route::prefix('v2')->group(function () {
    // V2'ye özel rotalar buraya gelecek
    // Örneğin, yeni bir model veya mevcut modeller için farklı bir yapı
    Route::apiResource('new-resource', NewResourceController::class);
});
*/
