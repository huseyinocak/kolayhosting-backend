<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PlanResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CategoryController extends Controller
{
    use AuthorizesRequests;
    /**
     * Tüm kategorileri listele (Pagination, Filtering, Sorting destekli).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Category::class);

        $query = Category::withCount('plans');

        // Filtreleme: İsim ile filtreleme
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Sıralama: name sütununa göre sıralama
        $sortBy = $request->input('sort_by', 'name'); // Varsayılan: name
        $sortOrder = $request->input('sort_order', 'asc'); // Varsayılan: artan

        if (in_array($sortBy, ['name', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Geçersiz sıralama sütunu durumunda varsayılan sıralama
            $query->orderBy('name', 'asc');
        }
        // Sayfalama
        $perPage = $request->get('per_page', 10); // Sayfa başına varsayılan 10 kayıt
        if (!is_numeric($perPage) || $perPage <= 0) {
            $perPage = 10; // Geçersiz per_page değeri için varsayılana dön
        }

        $categories = $query->paginate($perPage);

        return CategoryResource::collection($categories);
    }

    /**
     * Belirli bir kategoriyi göster.
     *
     * @param  \App\Models\Category  $category
     * @return \App\Http\Resources\CategoryResource
     */
    public function show(Category $category)
    {
        // view metodu için yetkilendirme kontrolü
        $this->authorize('view', $category);
        $category->loadCount('plans'); // Eğer sadece show metodunda lazımsa
        return new CategoryResource($category);
    }

    /**
     * Yeni bir kategori oluştur.
     *
     * @param  \App\Http\Requests\StoreCategoryRequest  $request // Form Request kullanıldı
     * @return \App\Http\Resources\CategoryResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name']);

            $category = Category::create($validatedData);

            return (new CategoryResource($category))
                ->additional(['message' => 'Kategori başarıyla oluşturuldu.', 'status' => 201]);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Kategori oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Belirli bir kategoriyi güncelle.
     *
     * @param  \App\Http\Requests\UpdateCategoryRequest  $request // Form Request kullanıldı
     * @param  \App\Models\Category  $category
     * @return \App\Http\Resources\CategoryResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            // Doğrulama Form Request tarafından yapıldığı için burada doğrudan validated() metodunu kullanıyoruz.
            $validatedData = $request->validated();

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = \Illuminate\Support\Str::slug($validatedData['name']);
            }

            $category->update($validatedData);

            return (new CategoryResource($category))
                ->additional(['message' => 'Kategori başarıyla güncellendi.', 'status' => 200]);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Kategori güncellenirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Belirli bir kategoriyi sil.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category)
    {
        // delete metodu için yetkilendirme kontrolü
        $this->authorize('delete', $category); // Policy kontrolü hala burada
        try {
            $category->delete();
            return response()->json(['message' => 'Kategori başarıyla silindi.', 'status' => 204], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Kategori silinirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Belirli bir kategoriye ait planları listele.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getPlansByCategory(Category $category, Request $request)
    {
        // Bu metod için de yetkilendirme kontrolü ekleyebilirsiniz, örneğin viewAny veya özel bir yetki.
        // Şimdilik view yetkisini kullanabiliriz, çünkü tek bir kategoriye ait planlar gösteriliyor.
        $this->authorize('view', $category);
        $query = $category->plans()->getQuery();

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
}
