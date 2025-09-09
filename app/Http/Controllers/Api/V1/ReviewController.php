<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
        // Varsayılan sıralama: published_at azalan, sonra created_at azalan
        $sortBy = $request->input('sort_by', 'published_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if (in_array($sortBy, ['rating', 'published_at', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('published_at', 'desc'); // Geçersiz sort_by durumunda varsayılan
        }

        // 3. Sayfalama (Pagination)
        $perPage = $request->input('per_page', 10);
        $reviews = $query->paginate($perPage);

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

            // Eğer user_id boşsa ve kullanıcı oturum açmışsa, user_id'yi ata
            if (empty($validatedData['user_id']) && Auth::check()) {
                $validatedData['user_id'] = Auth::id();
            }

            // Eğer user_id hala boşsa (misafir yorumu) ve user_name de boşsa hata ver
            if (empty($validatedData['user_id']) && empty($validatedData['user_name'])) {
                throw ValidationException::withMessages([
                    'user_name' => 'Misafir yorumları için kullanıcı adı zorunludur.',
                ]);
            }

            $review = Review::create($validatedData);

            return (new ReviewResource($review))
                ->additional(['message' => 'İnceleme başarıyla oluşturuldu. Onay bekliyor.', 'status' => 201]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası.',
                'errors' => $e->errors(),
                'status' => 422,
            ], 422);
        } catch (Exception $e) {
            Log::error('İnceleme oluşturulurken bir hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
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
        } catch (Exception $e) {
            Log::error('İnceleme güncellenirken bir hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
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
        } catch (Exception $e) {
            Log::error('İnceleme silinirken bir hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'İnceleme silinirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Bir incelemenin durumunu günceller (Admin yetkisi gerektirir).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request, Review $review): JsonResponse
    {
        // Sadece adminlerin bu işlemi yapmasına izin ver
        $this->authorize('admin'); // AuthServiceProvider'daki 'admin' gate'ini kullan

        try {
            $request->validate([
                'status' => ['required', 'string', \Illuminate\Validation\Rule::in(ReviewStatus::values())],
            ]);

            $newStatus = ReviewStatus::from($request->status);

            // Eğer statü değişiyorsa ve yeni statü APPROVED ise published_at'ı güncelle
            if ($review->status !== $newStatus && $newStatus === ReviewStatus::APPROVED) {
                $review->published_at = now();
            } elseif ($newStatus !== ReviewStatus::APPROVED) {
                // Eğer statü APPROVED değilse, published_at'ı null yapabiliriz (isteğe bağlı, iş mantığına göre)
                $review->published_at = null;
            }

            $review->status = $newStatus;
            $review->save();

            return response()->json([
                'message' => 'İnceleme durumu başarıyla güncellendi.',
                'review' => new ReviewResource($review),
                'status' => 200,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası.',
                'errors' => $e->errors(),
                'status' => 422,
            ], 422);
        } catch (Exception $e) {
            Log::error('İnceleme durumu güncellenirken hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'İnceleme durumu güncellenirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Kimliği doğrulanmış kullanıcının kendi incelemelerini listeler.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function getUserReviews(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['message' => 'Kimliği doğrulanmış kullanıcı bulunamadı.'], 401);
            }

            if (!$user instanceof User) {
                Log::error('ReviewController@getUserReviews: Auth::user() beklenmeyen bir tip veya null döndürdü.', [
                    'user_id' => Auth::id(), // Mümkünse kullanıcı ID'sini al
                    'user_type' => is_object($user) ? get_class($user) : gettype($user), // Gerçek sınıfı veya tipi al
                ]);
                return response()->json(['message' => 'Kullanıcının incelemelerinde beklenmeyen bir hata oluştu: Kullanıcı modeli bulunamadı.'], 500);
            }

            $query = $user->reviews()->with(['provider', 'plan']);

            // Filtreleme: provider_id, plan_id, rating, status, title, content
            if ($request->has('provider_id')) {
                $query->where('provider_id', $request->input('provider_id'));
            }
            if ($request->has('plan_id')) {
                $query->where('plan_id', $request->input('plan_id'));
            }
            if ($request->has('rating')) {
                $query->where('rating', (int) $request->input('rating'));
            }
            if ($request->has('status')) {
                $status = $request->input('status');
                if (in_array($status, ReviewStatus::values())) {
                    $query->where('status', $status);
                }
            }
            if ($request->has('title')) {
                $query->where('title', 'like', '%' . $request->input('title') . '%');
            }
            if ($request->has('content')) {
                $query->where('content', 'like', '%' . $request->input('content') . '%');
            }

            // Sıralama
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            if (in_array($sortBy, ['rating', 'created_at', 'updated_at', 'published_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Sayfalama
            $perPage = $request->input('per_page', 10);
            $perPage = min(100, max(1, (int) $perPage));

            $reviews = $query->paginate($perPage);

            return ReviewResource::collection($reviews);

        } catch (Exception $e) {
            Log::error('Kullanıcının incelemeleri alınırken hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'İncelemeler alınırken bir hata oluştu.'], 500);
        }
    }
}
