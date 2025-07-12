<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapma yetkisi olup olmadığını belirle.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Yetkilendirme mantığınızı buraya ekleyin.
        // Şimdilik herkesin sağlayıcı oluşturmasına izin veriyoruz.
        return true;
    }

    /**
     * İstek için doğrulama kurallarını al.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:providers,name',
            'logo_url' => 'nullable|url|max:255',
            'website_url' => 'required|url|max:255',
            'description' => 'nullable|string',
            'average_rating' => 'nullable|numeric|min:0|max:5',
        ];
    }

    /**
     * Özel doğrulama mesajlarını al.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Sağlayıcı adı alanı zorunludur.',
            'name.unique' => 'Bu sağlayıcı adı zaten mevcut.',
            'website_url.required' => 'Web sitesi URL\'si alanı zorunludur.',
            'website_url.url' => 'Web sitesi URL\'si geçerli bir URL olmalıdır.',
            'average_rating.numeric' => 'Ortalama derecelendirme sayısal bir değer olmalıdır.',
            'average_rating.min' => 'Ortalama derecelendirme en az :min olmalıdır.',
            'average_rating.max' => 'Ortalama derecelendirme en fazla :max olmalıdır.',
        ];
    }
}
