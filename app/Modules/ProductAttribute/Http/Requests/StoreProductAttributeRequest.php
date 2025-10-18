<?php

namespace App\Modules\ProductAttribute\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('product-attributes.create') ?? false;
    }

    public function rules(): array
    {
        $storeId = $this->user()?->currentStoreId();

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('product_attributes', 'name')->where(fn ($q) => $q->where('store_id', $storeId)),
            ],
            'attribute_set_id' => ['nullable', 'exists:attribute_sets,id'],
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}

class UpdateProductAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('product-attributes.edit') ?? false;
    }

    public function rules(): array
    {
        $storeId = $this->user()?->currentStoreId();
        $id = (int) $this->route('id') ?? (int) $this->route('product_attribute');

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('product_attributes', 'name')
                    ->where(fn ($q) => $q->where('store_id', $storeId))
                    ->ignore($id),
            ],
            'attribute_set_id' => ['nullable', 'exists:attribute_sets,id'],
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
