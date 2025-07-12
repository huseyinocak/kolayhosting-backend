<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FeatureController extends Controller
{
    /**
     * Tüm özellikleri listele.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
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
        return new FeatureResource($feature);
    }

    /**
     * Yeni bir özellik oluştur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\FeatureResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:features,name',
                'unit' => 'nullable|string|max:255',
                'type' => 'required|in:boolean,numeric,text',
            ]);

            $feature = Feature::create($validatedData);

            return new FeatureResource($feature);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Özellik oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Belirli bir özelliği güncelle.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Feature  $feature
     * @return \App\Http\Resources\FeatureResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Feature $feature)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:features,name,' . $feature->id,
                'unit' => 'nullable|string|max:255',
                'type' => 'sometimes|required|in:boolean,numeric,text',
            ]);

            $feature->update($validatedData);

            return new FeatureResource($feature);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Doğrulama hatası',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
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
