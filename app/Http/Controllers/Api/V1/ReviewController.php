<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    use AuthorizesRequests; // Bu trait, show ve index metodlarında Policy kontrolü için hala gerekli.

    /**
     * Tüm incelemeleri listele (Pagination, Filtering, Sorting destekli).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Review::class); // Policy kontrolü hala burada

        $query = Review::with(['provider', 'plan', 'user']);

        // Filtreleme: rating, is_approved, provider_id, plan_id ile filtreleme
        if ($request->has('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }
        if ($request->has('is_approved')) {
            // is_approved için boolean değerini doğru şekilde al
            $isApproved = filter_var($request->input('is_approved'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isApproved !== null) {
                $query->where('is_approved', $isApproved);
            }
        }
        if ($request->has('provider_id')) {
            $query->where('provider_id', (int) $request->input('provider_id'));
        }
        if ($request->has('plan_id')) {
            $query->where('plan_id', (int) $request->input('plan_id'));
        }

        // Sıralama: rating, published_at, created_at, updated_at sütunlarına göre sıralama
        $sortBy = $request->input('sort_by', 'published_at'); // Varsayılan: published_at
        $sortOrder = $request->input('sort_order', 'desc'); // Varsayılan: azalan

        if (in_array($sortBy, ['rating', 'published_at', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('published_at', 'desc');
        }

        // Sayfalama
        $perPage = $request->input('per_page', 10); // Varsayılan: 10 öğe
        $reviews = $query->paginate($perPage);

        return ReviewResource::collection($reviews);
    }

    /**
     * Belirli bir incelemeyi göster.
     *
     * @param  \App\Models\Review  $review
     * @return \App\Http\Resources\ReviewResource|\Illuminate\Http\JsonResponse
     */
    public function show(Review $review)
    {
        $this->authorize('view', $review); // Policy kontrolü hala burada
        return new ReviewResource($review->load(['provider', 'plan', 'user']));
    }

    /**
     * Yeni bir inceleme oluştur.
     *
     * @param  \App\Http\Requests\StoreReviewRequest  $request // Form Request kullanıldı
     * @return \App\Http\Resources\ReviewResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreReviewRequest $request) // Form Request yetkilendirmeyi halleder
    {
        // $this->authorize('create', Review::class); // Form Request'e taşındığı için kaldırıldı
        try {
            $validatedData = $request->validated();
            $validatedData['user_id'] = Auth::id(); // İncelemeyi oluşturan kullanıcının ID'sini otomatik olarak ata

            $review = Review::create($validatedData);

            return (new ReviewResource($review))
                ->additional(['message' => 'İnceleme başarıyla oluşturuldu.', 'status' => 201]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'İnceleme oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
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
    public function update(UpdateReviewRequest $request, Review $review) // Form Request yetkilendirmeyi halleder
    {
        // $this->authorize('update', $review); // Form Request'e taşındığı için kaldırıldı
        try {
            $validatedData = $request->validated();
            $review->update($validatedData);
            return (new ReviewResource($review))
                ->additional(['message' => 'İnceleme başarıyla güncellendi.', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'İnceleme güncellenirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
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
        $this->authorize('delete', $review); // Policy kontrolü hala burada
        try {
            $review->delete();
            return response()->json(['message' => 'İnceleme başarıyla silindi.', 'status' => 204], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'İnceleme silinirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
