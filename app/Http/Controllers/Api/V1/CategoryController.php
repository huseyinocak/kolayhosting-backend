<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PlanResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
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
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\CategoryResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string',
            ]);

            $validatedData['slug'] = Str::slug($validatedData['name']);

            $category = Category::create($validatedData);

            return new CategoryResource($category);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Kategori oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir kategoriyi güncelle.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \App\Http\Resources\CategoryResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Category $category)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $category->id,
                'description' => 'nullable|string',
            ]);

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }

            $category->update($validatedData);

            return new CategoryResource($category);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
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
