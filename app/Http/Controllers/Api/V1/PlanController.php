<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeatureResource;
use App\Http\Resources\PlanResource;
use App\Http\Resources\ReviewResource;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PlanController extends Controller
{
    /**
     * Tüm planları listele.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
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
        return new PlanResource($plan->load(['category', 'provider', 'features', 'reviews']));
    }

    /**
     * Yeni bir plan oluştur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\PlanResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'provider_id' => 'required|exists:providers,id',
                'category_id' => 'required|exists:categories,id',
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'currency' => 'required|string|max:3',
                'renewal_price' => 'nullable|numeric|min:0',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
                'features_summary' => 'nullable|string',
                'link' => 'required|url|max:255',
                'status' => 'required|in:active,inactive,deprecated',
            ]);

            $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name'] . '-' . $validatedData['provider_id']);

            $plan = Plan::create($validatedData);

            return new PlanResource($plan);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Plan oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir planı güncelle.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Plan  $plan
     * @return \App\Http\Resources\PlanResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Plan $plan)
    {
        try {
            $validatedData = $request->validate([
                'provider_id' => 'sometimes|required|exists:providers,id',
                'category_id' => 'sometimes|required|exists:categories,id',
                'name' => 'sometimes|required|string|max:255',
                'price' => 'sometimes|required|numeric|min:0',
                'currency' => 'sometimes|required|string|max:3',
                'renewal_price' => 'nullable|numeric|min:0',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
                'features_summary' => 'nullable|string',
                'link' => 'sometimes|required|url|max:255',
                'status' => 'sometimes|required|in:active,inactive,deprecated',
            ]);

            if (isset($validatedData['name']) || isset($validatedData['provider_id'])) {
                $name = $validatedData['name'] ?? $plan->name;
                $providerId = $validatedData['provider_id'] ?? $plan->provider_id;
                $validatedData['slug'] = \Illuminate\Support\Str::slug($name . '-' . $providerId);
            }

            $plan->update($validatedData);

            return new PlanResource($plan);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
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
        return ReviewResource::collection($plan->reviews);
    }
}
