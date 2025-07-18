<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReviewStatus;
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
    use AuthorizesRequests; // Bu trait, authorize() metodunu kullanmak için gereklidir.

    /**
     * Tüm incelemeleri listele (Pagination, Filtering, Sorting destekli).
     * Bu rota herkese açıktır.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        // Önemli: Bu metod herkese açık olduğu için $this->authorize('viewAny', Review::class); çağrısı KALDIRILDI.
        // Yetkilendirme, rota seviyesinde (routes/api.php) kontrol edilir veya hiç yapılmaz.

        // Temel sorguyu başlat
        $query = Review::with(['provider', 'plan', 'user']);

        // 1. Filtreleme (Filtering)
        // Örnek filtreler: provider_id, plan_id, rating, is_approved, title, content
        if ($request->has('provider_id')) {
            $query->where('provider_id', $request->input('provider_id'));
        }

        if ($request->has('plan_id')) {
            $query->where('plan_id', $request->input('plan_id'));
        }

        if ($request->has('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }

        if ($request->has('content')) {
            $query->where('content', 'like', '%' . $request->input('content') . '%');
        }

        // 2. Sıralama (Sorting)
        // Varsayılan sıralama: created_at azalan
        $sortBy = $request->input('sort_by', 'created_at'); // 'published_at' yerine 'created_at' daha genel olabilir
        $sortOrder = $request->input('sort_order', 'desc'); // Varsayılan azalan

        // Güvenlik için izin verilen sıralama sütunları
        $allowedSorts = ['id', 'rating', 'title', 'created_at', 'updated_at', 'published_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at'; // Geçersiz sütun ise varsayılana dön
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc'; // Geçersiz sıralama düzeni ise varsayılana dön
        }

        $query->orderBy($sortBy, $sortOrder);

        // 3. Sayfalama (Pagination)
        $perPage = $request->input('per_page', 15); // Her sayfada varsayılan 15 öğe
        $reviews = $query->paginate($perPage);

        // ReviewResource ile dönüştürerek döndür
        return ReviewResource::collection($reviews);
    }

    /**
     * Tüm incelemeleri listele (Kimliği doğrulanmış kullanıcılar için, adminler dahil).
     * Bu rota kimlik doğrulaması gerektirir ve adminler için tüm incelemeleri gösterir.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function indexAuthenticated(Request $request)
    {
        // Temel sorguyu başlat - burada is_approved filtresi uygulanmaz
        $query = Review::with(['provider', 'plan', 'user']);

        // 1. Filtreleme (Filtering)
        if ($request->has('provider_id')) {
            $query->where('provider_id', $request->input('provider_id'));
        }
        if ($request->has('plan_id')) {
            $query->where('plan_id', $request->input('plan_id'));
        }
        if ($request->has('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }
        // 'is_approved' yerine 'status' filtresi
        if ($request->has('status')) {
            $status = $request->input('status');
            // Gelen status değerinin ReviewStatus enum'ında geçerli olup olmadığını kontrol et
            if (in_array($status, ReviewStatus::values())) {
                $query->where('status', ReviewStatus::from($status));
            }
        }
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }
        if ($request->has('content')) {
            $query->where('content', 'like', '%' . $request->input('content') . '%');
        }

        // 2. Sıralama (Sorting)
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSorts = ['id', 'rating', 'title', 'created_at', 'updated_at', 'published_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }
        $query->orderBy($sortBy, $sortOrder);

        // 3. Sayfalama (Pagination)
        $perPage = $request->input('per_page', 15);
        $reviews = $query->paginate($perPage);

        // ReviewResource ile dönüştürerek döndür
        return ReviewResource::collection($reviews);
    }

    /**
     * Belirli bir incelemeyi göster.
     * Bu rota herkese açıktır.
     *
     * @param  \App\Models\Review  $review
     * @return \App\Http\Resources\ReviewResource
     */
    public function show(Review $review)
    {
        // Önemli: Bu metod herkese açık olduğu için $this->authorize('view', $review); çağrısı KALDIRILDI.
        // Yetkilendirme, rota seviyesinde (routes/api.php) kontrol edilir veya hiç yapılmaz.
        return new ReviewResource($review->load(['provider', 'plan', 'user']));
    }

    /**
     * Yeni bir inceleme oluştur.
     * Yetkilendirme StoreReviewRequest tarafından yapılır.
     *
     * @param  \App\Http\Requests\StoreReviewRequest  $request
     * @return \App\Http\Resources\ReviewResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreReviewRequest $request)
    {
        // Yetkilendirme StoreReviewRequest'in authorize() metodu tarafından halledildiği için burada authorize() çağrısı yok.
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
     * Yetkilendirme UpdateReviewRequest tarafından yapılır.
     *
     * @param  \App\Http\Requests\UpdateReviewRequest  $request
     * @param  \App\Models\Review  $review
     * @return \App\Http\Resources\ReviewResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        // Yetkilendirme UpdateReviewRequest'in authorize() metodu tarafından halledildiği için burada authorize() çağrısı yok.
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
     * Yetkilendirme bu metodun içinde yapılır.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Review $review)
    {
        // Bu metod için yetkilendirme kontrolü burada kalmaya devam ediyor.
        // ReviewPolicy'deki 'delete' metodunu çağırır.
        $this->authorize('delete', $review);
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
