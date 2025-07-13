<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateFeatureRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapma yetkisi olup olmadığını belirle.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
        ;
    }

    /**
     * İstek için doğrulama kurallarını al.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 'unique' kuralının mevcut özelliğin ID'sini göz ardı etmesi gerekiyor.
        // Özellik modeline route binding ile erişebiliriz.
        $featureId = $this->route('feature')->id ?? null;

        return [
            'name' => 'sometimes|required|string|max:255|unique:features,name,' . $featureId,
            'unit' => 'nullable|string|max:255',
            'type' => 'sometimes|required|in:boolean,numeric,text',
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
