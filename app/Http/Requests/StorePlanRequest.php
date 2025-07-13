<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
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
            'provider_id' => 'required|exists:providers,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'renewal_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'features_summary' => 'nullable|string',
            'link' => 'required|url|max:255',
            'status' => 'required|in:active,inactive,deprecated',
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
