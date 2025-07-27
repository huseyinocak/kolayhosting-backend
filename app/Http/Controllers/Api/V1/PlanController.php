<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Resources\FeatureResource;
use App\Http\Resources\PlanResource;
use App\Http\Resources\ReviewResource;
use App\Models\Plan;
use Exception;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PlanController extends Controller
{
    use AuthorizesRequests; // Bu trait, show, index, getFeaturesByPlan, getReviewsByPlan gibi metodlarda Policy kontrolü için hala gerekli.

    /**
     * Tüm planları listele (Pagination, Filtering, Sorting destekli).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Plan::class); // Policy kontrolü hala burada

        $query = Plan::with(['category', 'provider']);

        // Filtreleme: name, price, status, provider_id, category_id ile filtreleme
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->has('price_min')) {
            $query->where('price', '>=', (float) $request->input('price_min'));
        }
        if ($request->has('price_max')) {
            $query->where('price', '<=', (float) $request->input('price_max'));
        }
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('provider_id')) {
            $query->where('provider_id', (int) $request->input('provider_id'));
        }
        if ($request->has('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        // Sıralama: name, price, created_at, updated_at, average_rating sütunlarına göre sıralama
        $sortBy = $request->input('sort_by', 'name'); // Varsayılan: name
        $sortOrder = $request->input('sort_order', 'asc'); // Varsayılan: artan

        if (in_array($sortBy, ['name', 'price', 'created_at', 'updated_at', 'average_rating'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Sayfalama
        $perPage = $request->input('per_page', 10); // Varsayılan: 10 öğe
        $plans = $query->paginate($perPage);

        return PlanResource::collection($plans);
    }

    /**
     * Belirli bir planı göster.
     *
     * @param  \App\Models\Plan  $plan
     * @return \App\Http\Resources\PlanResource|\Illuminate\Http\JsonResponse
     */
    public function show(Plan $plan)
    {
        $this->authorize('view', $plan); // Policy kontrolü hala burada
        return new PlanResource($plan->load(['category', 'provider', 'features', 'reviews']));
    }

    /**
     * Yeni bir plan oluştur.
     *
     * @param  \App\Http\Requests\StorePlanRequest  $request // Form Request kullanıldı
     * @return \App\Http\Resources\PlanResource|\Illuminate\Http\JsonResponse
     */
    public function store(StorePlanRequest $request) // Form Request yetkilendirmeyi halleder
    {
        // Yetkilendirme StorePlanRequest'in authorize() metodu tarafından halledildiği için burada authorize() çağrısı yok.
        try {
            $plan = Plan::create($request->validated());
            return (new PlanResource($plan))
                ->additional(['message' => 'Plan başarıyla oluşturuldu.', 'status' => 201]);
        } catch (Exception $e) {
            Log::error('Plan oluşturulurken bir hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Plan oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Belirli bir planı güncelle.
     *
     * @param  \App\Http\Requests\UpdatePlanRequest  $request // Form Request kullanıldı
     * @param  \App\Models\Plan  $plan
     * @return \App\Http\Resources\PlanResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdatePlanRequest $request, Plan $plan) // Form Request yetkilendirmeyi halleder
    {
        // $this->authorize('update', $plan); // Form Request'e taşındığı için kaldırıldı
        try {
            $plan->update($request->validated());
            return (new PlanResource($plan))
                ->additional(['message' => 'Plan başarıyla güncellendi.', 'status' => 200]);
        } catch (Exception $e) {
            Log::error('Plan güncellenirken bir hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Plan güncellenirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Belirli bir planı sil.
     *
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Plan $plan)
    {
        $this->authorize('delete', $plan); // Policy kontrolü hala burada
        try {
            $plan->delete();
            return response()->json(['message' => 'Plan başarıyla silindi.', 'status' => 204], 204);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Plan silinirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Belirli bir plana ait özellikleri listele (Pagination, Filtering, Sorting destekli).
     *
     * @param  \App\Models\Plan  $plan
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function getFeaturesByPlan(Plan $plan)
    {
        $this->authorize('view', $plan); // Policy kontrolü hala burada

        $query = $plan->features()->get();



        return response()->json(FeatureResource::collection($query), 200);
    }

    /**
     * Belirli bir plana ait özellikleri listele (Pagination, Filtering, Sorting destekli).
     *
     * @param  \App\Models\Plan  $plan
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function getReviewsByPlan(Plan $plan)
    {
        $this->authorize('view', $plan); // Policy kontrolü hala burada

        $query = $plan->reviews()->get();



        return response()->json(ReviewResource::collection($query), 200);
    }

    /**
     * Bir plana özellik ekler.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachFeature(Request $request, Plan $plan): JsonResponse
    {
        $this->authorize('update', $plan); // Planı güncelleme yetkisi kontrolü

        $request->validate([
            'feature_id' => 'required|exists:features,id',
            'value' => 'nullable|string|max:255', // Özelliğin değeri
        ]);

        try {
            // Eğer özellik zaten ekliyse, detach yapıp tekrar eklemek yerine update edebiliriz
            // Veya direkt attach ile hata fırlatmasını bekleyebiliriz (eğer unique kısıtlaması varsa)
            $plan->features()->attach($request->feature_id, ['value' => $request->value]);

            // Planın güncel özelliklerini döndür
            return response()->json([
                'message' => 'Özellik plana başarıyla eklendi.',
                'features' => FeatureResource::collection($plan->features()->withPivot('value')->get())
            ], 200);
        } catch (Exception $e) {
            Log::error('Özellik plana eklenirken hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Özellik plana eklenirken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bir plandan özelliği çıkarır.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachFeature(Request $request, Plan $plan): JsonResponse
    {
        $this->authorize('update', $plan); // Planı güncelleme yetkisi kontrolü

        $request->validate([
            'feature_id' => 'required|exists:features,id',
        ]);

        try {
            $plan->features()->detach($request->feature_id);

            // Planın güncel özelliklerini döndür
            return response()->json([
                'message' => 'Özellik plandan başarıyla çıkarıldı.',
                'features' => FeatureResource::collection($plan->features()->withPivot('value')->get())
            ], 200);
        } catch (Exception $e) {
            Log::error('Özellik plandan çıkarılırken hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Özellik plandan çıkarılırken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bir planın özelliklerini senkronize eder (mevcutları günceller, olmayanları ekler, fazlalıkları siler).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncFeatures(Request $request, Plan $plan): JsonResponse
    {
        $this->authorize('update', $plan); // Planı güncelleme yetkisi kontrolü

        $request->validate([
            'features' => 'required|array',
            'features.*.id' => 'required|exists:features,id',
            'features.*.value' => 'nullable|string|max:255',
        ]);

        try {
            $featuresToSync = [];
            foreach ($request->features as $feature) {
                $featuresToSync[$feature['id']] = ['value' => $feature['value']];
            }

            $plan->features()->sync($featuresToSync);

            // Planın güncel özelliklerini döndür
            return response()->json([
                'message' => 'Plan özellikleri başarıyla senkronize edildi.',
                'features' => FeatureResource::collection($plan->features()->withPivot('value')->get())
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası.',
                'errors' => $e->errors(),
                'status' => 422,
            ], 422);
        } catch (Exception $e) {
            Log::error('Plan özellikleri senkronize edilirken hata oluştu: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Plan özellikleri senkronize edilirken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
