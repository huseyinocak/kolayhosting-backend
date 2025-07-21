<?php

use App\Http\Controllers\AiChatController;
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

    // Herkesin erişebileceği Public Okuma Rotaları
    // Bu rotalar için 'api' adını verdiğimiz genel rate limiter'ı kullanıyoruz.
    Route::middleware('throttle:api')->group(function () {
        // Kategoriler için Public API rotaları
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('categories/{category}', [CategoryController::class, 'show']);
        Route::get('categories/{category}/plans', [CategoryController::class, 'getPlansByCategory']);

        // Sağlayıcılar için Public API rotaları
        Route::get('providers', [ProviderController::class, 'index']);
        Route::get('providers/{provider}', [ProviderController::class, 'show']);
        Route::get('providers/{provider}/plans', [ProviderController::class, 'getPlansByProvider']);
        Route::get('providers/{provider}/reviews', [ProviderController::class, 'getReviewsByProvider']);
        // AI Chatbot Route
        Route::post('/ai/chat', [AiChatController::class, 'chat']); // Yeni AI Chatbot rotası
        // Planlar için Public API rotaları
        Route::get('plans', [PlanController::class, 'index']);
        Route::get('plans/{plan}', [PlanController::class, 'show']);
        Route::get('plans/{plan}/features', [PlanController::class, 'getFeaturesByPlan']);
        Route::get('plans/{plan}/reviews', [PlanController::class, 'getReviewsByPlan']);

        // Özellikler için Public API rotaları
        Route::get('features', [FeatureController::class, 'index']);
        Route::get('features/{feature}', [FeatureController::class, 'show']);

        // İncelemeler için Public API rotaları
        Route::get('reviews/{review}', [ReviewController::class, 'show']);
    });


    // Kimliği doğrulanmış kullanıcılar için korumalı rotalar
    // Bu rotalar için 'api' adını verdiğimiz genel rate limiter'ı kullanıyoruz.
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']); // Kimliği doğrulanmış kullanıcı bilgilerini getir
        Route::put('/user/isonboarded', [AuthController::class, 'updateIsOnboarded']);

        // Kategoriler için API rotaları (CRUD)
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        // Sağlayıcılar için API rotaları (CRUD)
        Route::post('providers', [ProviderController::class, 'store']);
        Route::put('providers/{provider}', [ProviderController::class, 'update']);
        Route::delete('providers/{provider}', [ProviderController::class, 'destroy']);

        // Planlar için API rotaları (CRUD)
        Route::post('plans', [PlanController::class, 'store']);
        Route::put('plans/{plan}', [PlanController::class, 'update']);
        Route::delete('plans/{plan}', [PlanController::class, 'destroy']);

        // Özellikler için API rotaları (CRUD)
        Route::post('features', [FeatureController::class, 'store']);
        Route::put('features/{feature}', [FeatureController::class, 'update']);
        Route::delete('features/{feature}', [FeatureController::class, 'destroy']);

        // İncelemeler için API rotaları (Oluşturma, Güncelleme, Silme)
        // Politika (Policy) ile yetkilendirme yönetilecektir.
        // Route::get('reviews/all', [ReviewController::class, 'indexAuthenticated']);
        Route::get('reviews', [ReviewController::class, 'index']);
        Route::post('reviews', [ReviewController::class, 'store']);
        Route::put('reviews/{review}', [ReviewController::class, 'update']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
    });
});

// Gelecekteki versiyonlar için örnek yapı:
/*
Route::prefix('v2')->group(function () {
    // V2'ye özel rotalar buraya gelecek
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::apiResource('new-resource', NewResourceController::class);
    });
});
*/
