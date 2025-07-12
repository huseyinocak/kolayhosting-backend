<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FeatureController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Versiyon 1 (v1) Rotaları
// Tüm v1 rotaları '/api/v1/' öneki altında toplanır.
Route::prefix('v1')->group(function () {
    // Kategoriler için API rotaları
    // index: Tüm kategorileri listele
    // show: Belirli bir kategoriyi göster
    Route::apiResource('categories', CategoryController::class);

    // Sağlayıcılar için API rotaları
    // index: Tüm sağlayıcıları listele
    // show: Belirli bir sağlayıcıyı göster
    Route::apiResource('providers', ProviderController::class);

    // Planlar için API rotaları
    // index: Tüm planları listele
    // show: Belirli bir planı göster
    Route::apiResource('plans', PlanController::class);

    // Özellikler için API rotaları
    // index: Tüm özellikleri listele
    // show: Belirli bir özelliği göster
    Route::apiResource('features', FeatureController::class);

    // İncelemeler için API rotaları
    // index: Tüm incelemeleri listele
    // show: Belirli bir incelemeyi göster
    Route::apiResource('reviews', ReviewController::class);

    // İsteğe bağlı: Belirli bir sağlayıcıya ait planları listeleme
    Route::get('providers/{provider}/plans', [ProviderController::class, 'getPlansByProvider']);

    // İsteğe bağlı: Belirli bir kategoriye ait planları listeleme
    Route::get('categories/{category}/plans', [CategoryController::class, 'getPlansByCategory']);

    // İsteğe bağlı: Belirli bir plana ait özellikleri listeleme
    Route::get('plans/{plan}/features', [PlanController::class, 'getFeaturesByPlan']);

    // İsteğe bağlı: Belirli bir sağlayıcıya ait incelemeleri listeleme
    Route::get('providers/{provider}/reviews', [ProviderController::class, 'getReviewsByProvider']);

    // İsteğe bağlı: Belirli bir plana ait incelemeleri listeleme
    Route::get('plans/{plan}/reviews', [PlanController::class, 'getReviewsByPlan']);

});
