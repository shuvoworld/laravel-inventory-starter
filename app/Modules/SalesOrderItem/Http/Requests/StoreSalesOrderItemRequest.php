<?php

namespace App\Modules\SalesOrderItem\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sales-order-item.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}

class UpdateSalesOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sales-order-item.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
