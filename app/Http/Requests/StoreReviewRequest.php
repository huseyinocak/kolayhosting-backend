<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Yetkilendirme mantığınızı buraya ekleyin.
        // Şimdilik herkesin inceleme oluşturmasına izin veriyoruz.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provider_id' => 'nullable|exists:providers,id',
            'plan_id' => 'nullable|exists:plans,id',
            'user_name' => 'nullable|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'published_at' => 'nullable|date',
            'is_approved' => 'boolean',
        ];
    }

    /**
     * Doğrulama sonrası ek kontroller ekle.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // provider_id veya plan_id'den en az birinin dolu olması kontrolü
            if (empty($this->provider_id) && empty($this->plan_id)) {
                $validator->errors()->add('provider_id', 'Ya sağlayıcı ID\'si ya da plan ID\'si belirtilmelidir.');
                $validator->errors()->add('plan_id', 'Ya sağlayıcı ID\'si ya da plan ID\'si belirtilmelidir.');
            }
        });
    }

    /**
     * Özel doğrulama mesajlarını al.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Derecelendirme alanı zorunludur.',
            'rating.integer' => 'Derecelendirme bir tam sayı olmalıdır.',
            'rating.min' => 'Derecelendirme en az :min olmalıdır.',
            'rating.max' => 'Derecelendirme en fazla :max olmalıdır.',
            'content.required' => 'İçerik alanı zorunludur.',
            'provider_id.exists' => 'Belirtilen sağlayıcı ID\'si geçersiz.',
            'plan_id.exists' => 'Belirtilen plan ID\'si geçersiz.',
        ];
    }
}
