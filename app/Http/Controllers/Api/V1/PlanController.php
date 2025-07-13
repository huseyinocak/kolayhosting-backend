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

class PlanController extends Controller
{
    use AuthorizesRequests;
    /**
     * Tüm planları listele.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $this->authorize('viewAny', Plan::class);
        return PlanResource::collection(Plan::with(['category', 'provider'])->get());
    }

    /**
     * Belirli bir planı göster.
     *
     * @param  \App\Models\Plan  $plan
     * @return \App\Http\Resources\PlanResource
     */
    public function show(Plan $plan)
    {
        $this->authorize('view', $plan);
        return new PlanResource($plan->load(['category', 'provider', 'features', 'reviews']));
    }

    /**
     * Yeni bir plan oluştur.
     *
     * @param  \App\Http\Requests\StorePlanRequest  $request // Form Request kullanıldı
     * @return \App\Http\Resources\PlanResource|\Illuminate\Http\JsonResponse
     */
    public function store(StorePlanRequest $request)
    {
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            // Slug'ı otomatik oluştur
            $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name'] . '-' . $validatedData['provider_id']);

            $plan = Plan::create($validatedData);

            return new PlanResource($plan);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Plan oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
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
    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            // Eğer isim veya sağlayıcı ID'si değişirse slug'ı güncelle
            if (isset($validatedData['name']) || isset($validatedData['provider_id'])) {
                $name = $validatedData['name'] ?? $plan->name;
                $providerId = $validatedData['provider_id'] ?? $plan->provider_id;
                $validatedData['slug'] = \Illuminate\Support\Str::slug($name . '-' . $providerId);
            }

            $plan->update($validatedData);

            return new PlanResource($plan);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Plan güncellenirken bir hata oluştu.',
                'error' => $e->getMessage(),
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
        $this->authorize('delete', $plan);
        try {
            $plan->delete();

            return response()->json(['message' => 'Plan başarıyla silindi.'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Plan silinirken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir plana ait özellikleri listele.
     *
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getFeaturesByPlan(Plan $plan)
    {
        $this->authorize('view', $plan); // Planı görüntüleme yetkisi olanlar özelliklerini de görebilir.
        // Plana ait özellikleri getir ve FeatureResource ile dönüştürerek döndür.
        // pivot tablosundaki 'value' değerini de alabilmek için withPivot kullanıyoruz.
        return FeatureResource::collection($plan->features);
    }

    /**
     * Belirli bir plana ait incelemeleri listele.
     *
     * @param  \App\Models\Plan  $plan
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getReviewsByPlan(Plan $plan)
    {
        $this->authorize('view', $plan); // Planı görüntüleme yetkisi olanlar incelemelerini de görebilir.
        return ReviewResource::collection($plan->reviews);
    }
}
