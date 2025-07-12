<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * Tüm incelemeleri listele.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        // Tüm incelemeleri getir ve ReviewResource ile dönüştürerek döndür.
        // İlişkili provider ve plan verilerini de yükleyebiliriz.
        return ReviewResource::collection(Review::with(['provider', 'plan'])->get());
    }

    /**
     * Belirli bir incelemeyi göster.
     *
     * @param  \App\Models\Review  $review
     * @return \App\Http\Resources\ReviewResource
     */
    public function show(Review $review)
    {
        // Belirli bir incelemeyi getir ve ReviewResource ile dönüştürerek döndür.
        // İlişkili provider ve plan verilerini de yükleyebiliriz.
        return new ReviewResource($review->load(['provider', 'plan']));
    }

    /**
     * Yeni bir inceleme oluştur.
     *
     * @param  \App\Http\Requests\StoreReviewRequest  $request // Form Request kullanıldı
     * @return \App\Http\Resources\ReviewResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreReviewRequest $request)
    {
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            $review = Review::create($validatedData);

            return new ReviewResource($review);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'İnceleme oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir incelemeyi güncelle.
     *
     * @param  \App\Http\Requests\UpdateReviewRequest  $request // Form Request kullanıldı
     * @param  \App\Models\Review  $review
     * @return \App\Http\Resources\ReviewResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            $review->update($validatedData);

            return new ReviewResource($review);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'İnceleme güncellenirken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir incelemeyi sil.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Review $review)
    {
        try {
            $review->delete();

            return response()->json(['message' => 'İnceleme başarıyla silindi.'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'İnceleme silinirken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
