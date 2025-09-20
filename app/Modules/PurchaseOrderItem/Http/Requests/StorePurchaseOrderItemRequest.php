<?php

namespace App\Modules\PurchaseOrderItem\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('purchase-order-item.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}

class UpdatePurchaseOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('purchase-order-item.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
