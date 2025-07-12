<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Tüm incelemeleri listele.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
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
        return new ReviewResource($review->load(['provider', 'plan']));
    }

    /**
     * Yeni bir inceleme oluştur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\ReviewResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'provider_id' => 'nullable|exists:providers,id',
                'plan_id' => 'nullable|exists:plans,id',
                'user_name' => 'nullable|string|max:255',
                'rating' => 'required|integer|min:1|max:5',
                'title' => 'nullable|string|max:255',
                'content' => 'required|string',
                'published_at' => 'nullable|date',
                'is_approved' => 'boolean',
            ]);

            // provider_id veya plan_id'den en az birinin dolu olması kontrolü
            if (empty($validatedData['provider_id']) && empty($validatedData['plan_id'])) {
                throw ValidationException::withMessages([
                    'provider_id' => 'Ya sağlayıcı ID\'si ya da plan ID\'si belirtilmelidir.',
                    'plan_id' => 'Ya sağlayıcı ID\'si ya da plan ID\'si belirtilmelidir.',
                ]);
            }

            $review = Review::create($validatedData);

            return new ReviewResource($review);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'İnceleme oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir incelemeyi güncelle.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \App\Http\Resources\ReviewResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Review $review)
    {
        try {
            $validatedData = $request->validate([
                'provider_id' => 'nullable|exists:providers,id',
                'plan_id' => 'nullable|exists:plans,id',
                'user_name' => 'nullable|string|max:255',
                'rating' => 'sometimes|required|integer|min:1|max:5',
                'title' => 'nullable|string|max:255',
                'content' => 'sometimes|required|string',
                'published_at' => 'nullable|date',
                'is_approved' => 'boolean',
            ]);

            // provider_id veya plan_id'den en az birinin dolu olması kontrolü
            // Eğer her ikisi de boşsa ve zaten boş değillerse hata ver
            if (isset($validatedData['provider_id']) && isset($validatedData['plan_id'])) {
                if (empty($validatedData['provider_id']) && empty($validatedData['plan_id'])) {
                    throw ValidationException::withMessages([
                        'provider_id' => 'Ya sağlayıcı ID\'si ya da plan ID\'si belirtilmelidir.',
                        'plan_id' => 'Ya sağlayıcı ID\'si ya da plan ID\'si belirtilmelidir.',
                    ]);
                }
            } else if (!isset($validatedData['provider_id']) && !isset($validatedData['plan_id'])) {
                // Eğer hiçbiri gönderilmediyse mevcut değerleri kontrol et
                if (empty($review->provider_id) && empty($review->plan_id)) {
                    throw ValidationException::withMessages([
                        'provider_id' => 'Ya sağlayıcı ID\'si ya da plan ID\'si belirtilmelidir.',
                        'plan_id' => 'Ya sağlayıcı ID\'si ya da plan ID\'si belirtilmelidir.',
                    ]);
                }
            }


            $review->update($validatedData);

            return new ReviewResource($review);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
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
