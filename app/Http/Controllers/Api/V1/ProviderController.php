<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Http\Resources\ProviderResource;
use App\Http\Resources\ReviewResource;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProviderController extends Controller
{
    /**
     * Tüm sağlayıcıları listele.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
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
        return new ProviderResource($provider);
    }

    /**
     * Yeni bir sağlayıcı oluştur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\ProviderResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:providers,name',
                'logo_url' => 'nullable|url|max:255',
                'website_url' => 'required|url|max:255',
                'description' => 'nullable|string',
                'average_rating' => 'nullable|numeric|min:0|max:5',
            ]);

            $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name']);

            $provider = Provider::create($validatedData);

            return new ProviderResource($provider);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sağlayıcı oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir sağlayıcıyı güncelle.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Provider  $provider
     * @return \App\Http\Resources\ProviderResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Provider $provider)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:providers,name,' . $provider->id,
                'logo_url' => 'nullable|url|max:255',
                'website_url' => 'sometimes|required|url|max:255',
                'description' => 'nullable|string',
                'average_rating' => 'nullable|numeric|min:0|max:5',
            ]);

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name']);
            }

            $provider->update($validatedData);

            return new ProviderResource($provider);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
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
        return ReviewResource::collection($provider->reviews);
    }
}
