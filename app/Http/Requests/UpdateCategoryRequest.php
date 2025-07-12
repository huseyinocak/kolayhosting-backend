<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Add your authorization logic here.
        // For now, allowing all users to update a category.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // The 'unique' rule needs to ignore the current category's ID.
        // We can access the category model via route binding.
        $categoryId = $this->route('category')->id ?? null;

        return [
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $categoryId,
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
