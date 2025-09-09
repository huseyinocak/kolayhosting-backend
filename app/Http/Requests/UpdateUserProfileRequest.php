<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id();
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                // E-posta benzersiz olmalı, ancak mevcut kullanıcının kendi e-postasını göz ardı etmeli
                Rule::unique('users')->ignore($userId),
            ],
            'password' => 'nullable|string|min:8|confirmed', // Şifre boş bırakılabilir, ancak verilirse onaylanmalı
            // Diğer profil alanları buraya eklenebilir (örn. 'avatar', 'phone', vb.)
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
            'name.required' => 'Ad alanı zorunludur.',
            'name.string' => 'Ad metin olmalıdır.',
            'name.max' => 'Ad en fazla :max karakter olabilir.',
            'email.required' => 'E-posta alanı zorunludur.',
            'email.string' => 'E-posta metin olmalıdır.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.max' => 'E-posta en fazla :max karakter olabilir.',
            'email.unique' => 'Bu e-posta adresi zaten kullanımda.',
            'password.min' => 'Şifre en az :min karakter olmalıdır.',
            'password.confirmed' => 'Şifre onayı uyuşmuyor.',
        ];
    }
}
