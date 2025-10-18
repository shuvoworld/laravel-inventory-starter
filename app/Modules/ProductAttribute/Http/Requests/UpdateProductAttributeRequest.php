<?php

namespace App\Modules\ProductAttribute\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('product-attribute.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}

class UpdateProductAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('product-attribute.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'attribute_set_id' => ['nullable', 'exists:attribute_sets,id'],
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
