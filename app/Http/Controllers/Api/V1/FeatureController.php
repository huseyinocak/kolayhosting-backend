<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeatureRequest;
use App\Http\Requests\UpdateFeatureRequest;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FeatureController extends Controller
{
    use AuthorizesRequests;
    /**
     * Tüm özellikleri listele.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $this->authorize('viewAny', Feature::class);
        return FeatureResource::collection(Feature::all());
    }

    /**
     * Belirli bir özelliği göster.
     *
     * @param  \App\Models\Feature  $feature
     * @return \App\Http\Resources\FeatureResource
     */
    public function show(Feature $feature)
    {
        $this->authorize('view', $feature);
        return new FeatureResource($feature);
    }

    /**
     * Yeni bir özellik oluştur.
     *
     * @param  \App\Http\Requests\StoreFeatureRequest  $request // Form Request kullanıldı
     * @return \App\Http\Resources\FeatureResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreFeatureRequest $request)
    {
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            $feature = Feature::create($validatedData);

            return new FeatureResource($feature);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Özellik oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
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
    public function update(UpdateFeatureRequest $request, Feature $feature)
    {
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            $feature->update($validatedData);

            return new FeatureResource($feature);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Özellik güncellenirken bir hata oluştu.',
                'error' => $e->getMessage(),
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
        $this->authorize('delete', $feature);
        try {
            $feature->delete();

            return response()->json(['message' => 'Özellik başarıyla silindi.'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Özellik silinirken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
