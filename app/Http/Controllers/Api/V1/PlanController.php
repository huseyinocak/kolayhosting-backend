<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Resources\FeatureResource;
use App\Http\Resources\PlanResource;
use App\Http\Resources\ReviewResource;
use App\Models\Plan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

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

        // Sıralama: name, price, renewal_price, created_at, updated_at sütunlarına göre sıralama
        $sortBy = $request->input('sort_by', 'name'); // Varsayılan: name
        $sortOrder = $request->input('sort_order', 'asc'); // Varsayılan: artan

        if (in_array($sortBy, ['name', 'price', 'renewal_price', 'created_at', 'updated_at'])) {
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
        // $this->authorize('create', Plan::class); // Form Request'e taşındığı için kaldırıldı
        try {
            $validatedData = $request->validated();
            $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name'] . '-' . $validatedData['provider_id']);
            $plan = Plan::create($validatedData);
            return (new PlanResource($plan))
                ->additional(['message' => 'Plan başarıyla oluşturuldu.', 'status' => 201]);
        } catch (\Exception $e) {
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
            $validatedData = $request->validated();
            if (isset($validatedData['name']) || isset($validatedData['provider_id'])) {
                $name = $validatedData['name'] ?? $plan->name;
                $providerId = $validatedData['provider_id'] ?? $plan->provider_id;
                $validatedData['slug'] = \Illuminate\Support\Str::slug($name . '-' . $providerId);
            }
            $plan->update($validatedData);
            return (new PlanResource($plan))
                ->additional(['message' => 'Plan başarıyla güncellendi.', 'status' => 200]);
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
    public function getFeaturesByPlan(Plan $plan, Request $request)
    {
        $this->authorize('view', $plan); // Policy kontrolü hala burada

        $query = $plan->features()->getQuery();

        // Filtreleme: Özellik adına göre filtreleme
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Sıralama: name veya type sütununa göre sıralama
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');

        if (in_array($sortBy, ['name', 'type', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = $request->input('per_page', 10);
        $features = $query->paginate($perPage);

        return FeatureResource::collection($features);
    }

    /**
     * Belirli bir plana ait incelemeleri listele (Pagination, Filtering, Sorting destekli).
     *
     * @param  \App\Models\Plan  $plan
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function getReviewsByPlan(Plan $plan, Request $request)
    {
        $this->authorize('view', $plan); // Policy kontrolü hala burada

        $query = $plan->reviews()->getQuery();

        // Filtreleme: Rating veya onay durumuna göre filtreleme
        if ($request->has('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }
        if ($request->has('is_approved')) {
            $isApproved = filter_var($request->input('is_approved'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isApproved !== null) {
                $query->where('is_approved', $isApproved);
            }
        }

        // Sıralama: Rating veya yayınlanma tarihine göre sıralama
        $sortBy = $request->input('sort_by', 'published_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if (in_array($sortBy, ['rating', 'published_at', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('published_at', 'desc');
        }

        $perPage = $request->input('per_page', 10);
        $reviews = $query->paginate($perPage);

        return ReviewResource::collection($reviews);
    }
}
