<?php

namespace App\Http\Requests;

use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        if ($user->role === UserRole::ADMIN) {
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
        // Güncellenen incelemenin ID'sini al
        $reviewId = $this->route('review')->id ?? null;

        // Kullanıcının rolünü al (yetkilendirme authorize() metodunda yapıldığı için burada güvenli)
        $user = Auth::user();
        $isAdmin = $user && $user->role === UserRole::ADMIN;

        return [
            // provider_id ve plan_id alanları nullable ve sometimes olabilir.
            // Ancak ikisi birden null olamaz. Bu kontrol withValidator'da yapılacak.
            'provider_id' => 'nullable|exists:providers,id',
            'plan_id' => 'nullable|exists:plans,id',
            'user_name' => [
                'nullable',
                'string',
                'max:255',
                // user_id null ise user_name zorunlu olmalı (misafir yorumları için)
                // Bu kural, user_id'nin null olabileceği senaryolar için eklenmiştir.
                // Eğer her zaman user_id bekleniyorsa bu kural kaldırılabilir.
                Rule::requiredIf(function () {
                    return is_null($this->user_id) && is_null($this->route('review')->user_id);
                }),
            ],
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'content' => 'sometimes|required|string',
            'published_at' => 'nullable|date',
            // Sadece adminler status'ü değiştirebilir
            'status' => [
                'sometimes',
                'required',
                Rule::in(ReviewStatus::values()),
                Rule::requiredIf(function () use ($isAdmin) {
                    return $isAdmin; // Sadece adminler için status alanı zorunlu olabilir
                }),
            ],
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
           $review = $this->route('review'); // Mevcut inceleme modelini al

            // İstekte provider_id varsa onu kullan, yoksa mevcut modeldeki değeri kullan
            $effectiveProviderId = $this->has('provider_id') ? $this->input('provider_id') : $review->provider_id;
            // İstekte plan_id varsa onu kullan, yoksa mevcut modeldeki değeri kullan
            $effectivePlanId = $this->has('plan_id') ? $this->input('plan_id') : $review->plan_id;

            // Eğer hem provider_id hem de plan_id null ise hata ekle
            if (is_null($effectiveProviderId) && is_null($effectivePlanId)) {
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
            'rating.min' => 'Derecelendirme en az 1 olmalıdır.',
            'rating.max' => 'Derecelendirme en fazla 5 olmalıdır.',
            'content.required' => 'İçerik alanı zorunludur.',
            'content.string' => 'İçerik metin olmalıdır.',
            'title.string' => 'Başlık metin olmalıdır.',
            'title.max' => 'Başlık en fazla 255 karakter olabilir.',
            'status.string' => 'Durum metin olmalıdır.',
            'status.required' => 'Durum alanı zorunludur.',
            'status.in' => 'Geçersiz durum tipi. Geçerli tipler: ' . implode(', ', ReviewStatus::values()) . '.',
            'user_name.required' => 'Kullanıcı adı alanı zorunludur.',
        ];
    }
}
