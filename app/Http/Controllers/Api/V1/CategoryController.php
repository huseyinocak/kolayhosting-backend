<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PlanResource;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * /**
     * Tüm kategorileri listele.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return CategoryResource::collection(Category::all());
    }

    /**
     * Belirli bir kategoriyi göster.
     *
     * @param  \App\Models\Category  $category
     * @return \App\Http\Resources\CategoryResource
     */
    public function show(Category $category)
    {
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

            return new CategoryResource($category);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Kategori oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
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

            return new CategoryResource($category);
        } catch (\Exception $e) {
            // Sadece beklenmeyen genel hataları yakala, doğrulama hataları FormRequest tarafından otomatik yönetilir.
            return response()->json([
                'message' => 'Kategori güncellenirken bir hata oluştu.',
                'error' => $e->getMessage(),
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
        try {
            $category->delete();

            return response()->json(['message' => 'Kategori başarıyla silindi.'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Kategori silinirken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir kategoriye ait planları listele.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getPlansByCategory(Category $category)
    {
        return PlanResource::collection($category->plans);
    }
}
