<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProviderRequest;
use App\Http\Requests\UpdateProviderRequest;
use App\Http\Resources\PlanResource;
use App\Http\Resources\ProviderResource;
use App\Http\Resources\ReviewResource;
use App\Models\Provider;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProviderController extends Controller
{
    use AuthorizesRequests;
    /**
     * Tüm sağlayıcıları listele.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $this->authorize('viewAny', Provider::class);
        return ProviderResource::collection(Provider::all());
    }

    /**
     * Belirli bir sağlayıcıyı göster.
     *
     * @param  \App\Models\Provider  $provider
     * @return \App\Http\Resources\ProviderResource
     */
    public function show(Provider $provider)
    {
        $this->authorize('view', $provider);
        return new ProviderResource($provider);
    }

    /**
     * Yeni bir sağlayıcı oluştur.
     *
     * @param  \App\Http\Requests\StoreProviderRequest  $request // Form Request kullanıldı
     * @return \App\Http\Resources\ProviderResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreProviderRequest $request)
    {
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name']);

            $provider = Provider::create($validatedData);

            return new ProviderResource($provider);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Sağlayıcı oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir sağlayıcıyı güncelle.
     *
     * @param  \App\Http\Requests\UpdateProviderRequest  $request // Form Request kullanıldı
     * @param  \App\Models\Provider  $provider
     * @return \App\Http\Resources\ProviderResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateProviderRequest $request, Provider $provider)
    {
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name']);
            }

            $provider->update($validatedData);

            return new ProviderResource($provider);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Sağlayıcı güncellenirken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir sağlayıcıyı sil.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Provider $provider)
    {
        $this->authorize('delete', $provider);
        try {
            $provider->delete();

            return response()->json(['message' => 'Sağlayıcı başarıyla silindi.'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sağlayıcı silinirken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir sağlayıcıya ait planları listele.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getPlansByProvider(Provider $provider)
    {
        $this->authorize('view', $provider); // Sağlayıcıyı görüntüleme yetkisi olanlar planlarını da görebilir.
        return PlanResource::collection($provider->plans);
    }

    /**
     * Belirli bir sağlayıcıya ait incelemeleri listele.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getReviewsByProvider(Provider $provider)
    {
        $this->authorize('view', $provider); // Sağlayıcıyı görüntüleme yetkisi olanlar incelemelerini de görebilir.
        return ReviewResource::collection($provider->reviews);
    }
}
