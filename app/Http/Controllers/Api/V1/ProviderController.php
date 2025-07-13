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
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    use AuthorizesRequests; // Bu trait, show, index, getPlansByProvider, getReviewsByProvider gibi metodlarda Policy kontrolü için hala gerekli.

    /**
     * Tüm sağlayıcıları listele (Pagination, Filtering, Sorting destekli).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Provider::class); // Policy kontrolü hala burada
        // viewAny metodu için yetkilendirme kontrolü

        $query = Provider::query();

        // Filtreleme: İsim ile filtreleme
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Sıralama: name veya average_rating sütununa göre sıralama
        $sortBy = $request->input('sort_by', 'name'); // Varsayılan: name
        $sortOrder = $request->input('sort_order', 'asc'); // Varsayılan: artan

        if (in_array($sortBy, ['name', 'average_rating', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Geçersiz sıralama sütunu durumunda varsayılan sıralama
            $query->orderBy('name', 'asc');
        }

        // Sayfalama
        $perPage = $request->input('per_page', 10); // Varsayılan: 10 öğe
        $providers = $query->paginate($perPage);

        return ProviderResource::collection($providers);
    }

    /**
     * Belirli bir sağlayıcıyı göster.
     *
     * @param  \App\Models\Provider  $provider
     * @return \App\Http\Resources\ProviderResource|\Illuminate\Http\JsonResponse
     */
    public function show(Provider $provider)
    {
        $this->authorize('view', $provider); // Policy kontrolü hala burada
        return new ProviderResource($provider);
    }

    /**
     * Yeni bir sağlayıcı oluştur.
     *
     * @param  \App\Http\Requests\StoreProviderRequest  $request // Form Request kullanıldı
     * @return \App\Http\Resources\ProviderResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreProviderRequest $request) // Form Request yetkilendirmeyi halleder
    {
        // $this->authorize('create', Provider::class); // Form Request'e taşındığı için kaldırıldı
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name']);

            $provider = Provider::create($validatedData);

            return (new ProviderResource($provider))
                ->additional(['message' => 'Sağlayıcı başarıyla oluşturuldu.', 'status' => 201]);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Sağlayıcı oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
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
    public function update(UpdateProviderRequest $request, Provider $provider) // Form Request yetkilendirmeyi halleder
    {
        // $this->authorize('update', $provider); // Form Request'e taşındığı için kaldırıldı
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name']);
            }

            $provider->update($validatedData);

            return (new ProviderResource($provider))
                ->additional(['message' => 'Sağlayıcı başarıyla güncellendi.', 'status' => 200]);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Sağlayıcı güncellenirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
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
        $this->authorize('delete', $provider); // Policy kontrolü hala burada
        try {
            $provider->delete();
            return response()->json(['message' => 'Sağlayıcı başarıyla silindi.', 'status' => 204], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sağlayıcı silinirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Belirli bir sağlayıcıya ait planları listele (Pagination, Filtering, Sorting destekli).
     *
     * @param  \App\Models\Provider  $provider
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function getPlansByProvider(Provider $provider, Request $request)
    {
        $this->authorize('view', $provider); // Policy kontrolü hala burada

        $query = $provider->plans()->getQuery();

        // Filtreleme: Plan adına göre filtreleme
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Sıralama: Plan adına veya fiyata göre sıralama
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');

        if (in_array($sortBy, ['name', 'price', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = $request->input('per_page', 10);
        $plans = $query->paginate($perPage);

        return PlanResource::collection($plans);
    }

    /**
     * Belirli bir sağlayıcıya ait incelemeleri listele (Pagination, Filtering, Sorting destekli).
     *
     * @param  \App\Models\Provider  $provider
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function getReviewsByProvider(Provider $provider, Request $request)
    {
        $this->authorize('view', $provider); // Policy kontrolü hala burada

        $query = $provider->reviews()->getQuery();

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
