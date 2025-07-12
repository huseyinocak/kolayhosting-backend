<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapma yetkisi olup olmadığını belirle.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Yetkilendirme mantığınızı buraya ekleyin.
        // Şimdilik herkesin plan güncellemesine izin veriyoruz.
        return true;
    }

    /**
     * İstek için doğrulama kurallarını al.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Plan ID'sine göre benzersizlik kontrolü gerekebilir, ancak slug'ı otomatik oluşturduğumuz için
        // burada doğrudan unique kuralına gerek yok, çünkü name ve provider_id kombinasyonu slug'ı oluşturuyor.
        // Eğer name tek başına unique olsaydı, planId'yi kullanırdık.
        // $planId = $this->route('plan')->id ?? null;

        return [
            'provider_id' => 'sometimes|required|exists:providers,id',
            'category_id' => 'sometimes|required|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'currency' => 'sometimes|required|string|max:3',
            'renewal_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'features_summary' => 'nullable|string',
            'link' => 'sometimes|required|url|max:255',
            'status' => 'sometimes|required|in:active,inactive,deprecated',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'provider_id.required' => 'Sağlayıcı ID alanı zorunludur.',
            'provider_id.exists' => 'Belirtilen sağlayıcı ID\'si geçersiz.',
            'category_id.required' => 'Kategori ID alanı zorunludur.',
            'category_id.exists' => 'Belirtilen kategori ID\'si geçersiz.',
            'name.required' => 'Plan adı alanı zorunludur.',
            'price.required' => 'Fiyat alanı zorunludur.',
            'price.numeric' => 'Fiyat sayısal bir değer olmalıdır.',
            'currency.required' => 'Para birimi alanı zorunludur.',
            'link.required' => 'Bağlantı alanı zorunludur.',
            'link.url' => 'Bağlantı geçerli bir URL olmalıdır.',
            'status.required' => 'Durum alanı zorunludur.',
            'status.in' => 'Geçersiz durum değeri. (active, inactive, deprecated olmalı)',
        ];
    }
}
