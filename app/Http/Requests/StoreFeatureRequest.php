<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreFeatureRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapma yetkisi olup olmadığını belirle.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Yetkilendirme mantığınızı buraya ekleyin.
        // Şimdilik herkesin özellik oluşturmasına izin veriyoruz.
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * İstek için doğrulama kurallarını al.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:features,name',
            'unit' => 'nullable|string|max:255',
            'type' => 'required|in:boolean,numeric,text',
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
            'name.required' => 'Özellik adı alanı zorunludur.',
            'name.unique' => 'Bu özellik adı zaten mevcut.',
            'type.required' => 'Özellik tipi alanı zorunludur.',
            'type.in' => 'Geçersiz özellik tipi. (boolean, numeric, text olmalı)',
        ];
    }
}
