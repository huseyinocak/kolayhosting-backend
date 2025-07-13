<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Kategori oluşturma yetkisini burada kontrol et.
        // CategoryPolicy'deki 'create' metodunun mantığını buraya taşıyoruz.
        // Sadece admin rolüne sahip kullanıcılar kategori oluşturabilir.
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
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
            'name.required' => 'Kategori adı alanı zorunludur.',
            'name.unique' => 'Bu kategori adı zaten mevcut.',
            'name.max' => 'Kategori adı en fazla :max karakter olabilir.',
        ];
    }
}
