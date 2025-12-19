<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'master_category_id' => 'nullable|exists:master_categories,id',
            'dimension_id' => 'nullable|exists:dimensions,id',

            'product_images' => 'nullable|array',
            'product_images.*' => 'mimes:jpg,jpeg,png,webp|max:5120',

            'teak_images' => 'nullable|array',
            'teak_images.*' => 'mimes:jpg,jpeg,png,webp|max:5120',

            'cover_images' => 'nullable|array',
            'cover_images.*' => 'mimes:jpg,jpeg,png,webp|max:5120',

            'product_images_delete' => 'nullable|array',
            'product_images_delete.*' => 'integer|exists:product_images,id',

            'teak_images_delete' => 'nullable|array',
            'teak_images_delete.*' => 'integer|exists:teak_images,id',

            'cover_images_delete' => 'nullable|array',
            'cover_images_delete.*' => 'integer|exists:cover_images,id',
        ];
    }
}
