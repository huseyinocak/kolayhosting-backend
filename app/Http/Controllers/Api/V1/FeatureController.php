<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeatureRequest;
use App\Http\Requests\UpdateFeatureRequest;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    use AuthorizesRequests; // Bu trait, show ve index metodlarında Policy kontrolü için hala gerekli.

    /**
     * Tüm özellikleri listele (Pagination, Filtering, Sorting destekli).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Feature::class); // Policy kontrolü hala burada

        $query = Feature::query();

        // Filtreleme: İsim ile filtreleme
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        // Filtreleme: Type ile filtreleme
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        // Sıralama: name veya type sütununa göre sıralama
        $sortBy = $request->input('sort_by', 'name'); // Varsayılan: name
        $sortOrder = $request->input('sort_order', 'asc'); // Varsayılan: artan

        if (in_array($sortBy, ['name', 'type', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Sayfalama
        $perPage = $request->input('per_page', 10); // Varsayılan: 10 öğe
        $features = $query->paginate($perPage);

        return FeatureResource::collection($features);
    }

    /**
     * Belirli bir özelliği göster.
     *
     * @param  \App\Models\Feature  $feature
     * @return \App\Http\Resources\FeatureResource|\Illuminate\Http\JsonResponse
     */
    public function show(Feature $feature)
    {
        $this->authorize('view', $feature); // Policy kontrolü hala burada
        return new FeatureResource($feature);
    }

    /**
     * Yeni bir özellik oluştur.
     *
     * @param  \App\Http\Requests\StoreFeatureRequest  $request // Form Request kullanıldı
     * @return \App\Http\Resources\FeatureResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreFeatureRequest $request) // Form Request yetkilendirmeyi halleder
    {
        // $this->authorize('create', Feature::class); // Form Request'e taşındığı için kaldırıldı
        try {
            $validatedData = $request->validated();
            $feature = Feature::create($validatedData);
            return (new FeatureResource($feature))
                ->additional(['message' => 'Özellik başarıyla oluşturuldu.', 'status' => 201]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Özellik oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Belirli bir özelliği güncelle.
     *
     * @param  \App\Http\Requests\UpdateFeatureRequest  $request // Form Request kullanıldı
     * @param  \App\Models\Feature  $feature
     * @return \App\Http\Resources\FeatureResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateFeatureRequest $request, Feature $feature) // Form Request yetkilendirmeyi halleder
    {
        // $this->authorize('update', $feature); // Form Request'e taşındığı için kaldırıldı
        try {
            $validatedData = $request->validated();
            $feature->update($validatedData);
            return (new FeatureResource($feature))
                ->additional(['message' => 'Özellik başarıyla güncellendi.', 'status' => 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Özellik güncellenirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Belirli bir özelliği sil.
     *
     * @param  \App\Models\Feature  $feature
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Feature $feature)
    {
        $this->authorize('delete', $feature); // Policy kontrolü hala burada
        try {
            $feature->delete();
            return response()->json(['message' => 'Özellik başarıyla silindi.', 'status' => 204], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Özellik silinirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }
}
