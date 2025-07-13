<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateReviewRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapma yetkisi olup olmadığını belirle.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Yetkilendirme mantığını burada kontrol et.
        // İncelemeyi güncelleyen kullanıcı ya admin olmalı
        // ya da incelemenin sahibi olmalıdır.

        // Kullanıcının kimliği doğrulanmış mı?
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        $review = $this->route('review'); // Route Model Binding ile inceleme modelini al

        // Eğer inceleme modeli mevcut değilse (örn. geçersiz ID), yetkilendirme başarısız.
        if (!$review) {
            return false;
        }

        // Admin rolüne sahipse her zaman izin ver
        if ($user->role === 'admin') {
            return true;
        }

        // İncelemenin sahibi ise izin ver
        return $user->id === $review->user_id;
    }

    /**
     * İstek için doğrulama kurallarını al.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provider_id' => 'nullable|exists:providers,id',
            'plan_id' => 'nullable|exists:plans,id',
            'user_name' => 'nullable|string|max:255',
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'content' => 'sometimes|required|string',
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
            // Eğer provider_id veya plan_id gönderildiyse ve her ikisi de boşsa hata ver.
            // Veya hiçbiri gönderilmediyse ve mevcut incelemede de her ikisi boşsa hata ver.
            $providerIdPresent = $this->has('provider_id');
            $planIdPresent = $this->has('plan_id');

            $currentProviderId = $this->route('review')->provider_id ?? null; // Route model binding ile review'i al
            $currentPlanId = $this->route('review')->plan_id ?? null;

            $newProviderId = $this->provider_id;
            $newPlanId = $this->plan_id;

            if (
                ($providerIdPresent && $planIdPresent && empty($newProviderId) && empty($newPlanId)) ||
                (!$providerIdPresent && !$planIdPresent && empty($currentProviderId) && empty($currentPlanId))
            ) {
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
