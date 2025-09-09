<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\Api\V1\AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FeatureController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\SitemapController;
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
    // E-posta doğrulama rotaları (public erişimli olmalı, çünkü linke tıklayan kullanıcı henüz kimliği doğrulanmamış olabilir)
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed']) // URL'nin imzalı olmasını gerektirir
        ->name('verification.verify');
    // Kimlik Doğrulama Rotaları (Auth Rate Limiter ile korunur)
    // Bu rotalar için 'auth' adını verdiğimiz rate limiter'ı kullanıyoruz.
    Route::middleware('throttle:auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email'); // Şifre sıfırlama linki gönderme
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update'); // Şifreyi sıfırlama
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
        Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
        // Planlar için Public API rotaları
        Route::get('plans', [PlanController::class, 'index']);
        Route::get('plans/{plan}', [PlanController::class, 'show']);
        Route::get('plans/{plan}/features', [PlanController::class, 'getFeaturesByPlan']);
        Route::get('plans/{plan}/reviews', [PlanController::class, 'getReviewsByPlan']);

        // Özellikler için Public API rotaları
        Route::get('features', [FeatureController::class, 'index']);
        Route::get('features/{feature}', [FeatureController::class, 'show']);

        // İncelemeler için Public API rotaları
        Route::get('reviews', [ReviewController::class, 'index']);
        Route::get('reviews/{review}', [ReviewController::class, 'show']);
        Route::post('reviews', [ReviewController::class, 'store']); // Misafir yorumlarına izin verildiği için burada kalabilir


        // E-posta doğrulama linki tıklama rotası api/v1/email/verify/resend
        Route::post('email/verify/resend', [AuthController::class, 'resendVerificationLink'])
            ->middleware(['auth:sanctum', 'throttle:6,1']) // Kimliği doğrulanmış kullanıcı ve rate limit
            ->name('verification.send');
    });


    // Kimliği doğrulanmış kullanıcılar için korumalı rotalar
    // Bu rotalar için 'api' adını verdiğimiz genel rate limiter'ı kullanıyoruz.
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']); // Kimliği doğrulanmış kullanıcı bilgilerini getir
        Route::put('user/profile', [AuthController::class, 'updateProfile']); // Kullanıcı profilini güncelleme rotası - EKLENDİ

        Route::middleware('can:admin')->group(function () {
            // Kullanıcı Yönetimi Rotları
            Route::post('admin/users', [AdminUserController::class, 'store']);
            Route::get('admin/users', [AdminUserController::class, 'index']);
            Route::get('admin/users/{user}', [AdminUserController::class, 'show']);
            Route::put('admin/users/{user}', [AdminUserController::class, 'update']);
            Route::delete('admin/users/{user}', [AdminUserController::class, 'destroy']);
            Route::get('admin/dashboard/stats', [AdminUserController::class, 'getStats']);
        });
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

        // Plan Özellik Yönetimi Rotaları - EKLENDİ
        Route::post('plans/{plan}/features/attach', [PlanController::class, 'attachFeature']);
        Route::post('plans/{plan}/features/detach', [PlanController::class, 'detachFeature']);
        Route::put('plans/{plan}/features/sync', [PlanController::class, 'syncFeatures']);

        // Özellikler için API rotaları (CRUD)
        Route::post('features', [FeatureController::class, 'store']);
        Route::put('features/{feature}', [FeatureController::class, 'update']);
        Route::delete('features/{feature}', [FeatureController::class, 'destroy']);

        // İncelemeler için API rotaları (Oluşturma, Güncelleme, Silme)
        Route::put('reviews/{review}', [ReviewController::class, 'update']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
        Route::put('reviews/{review}/status', [ReviewController::class, 'changeStatus']); // İnceleme durumu değiştirme rotası
        Route::get('user/reviews', [ReviewController::class, 'getUserReviews']); // Kullanıcının kendi incelemelerini listeleme rotası - EKLENDİ
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
